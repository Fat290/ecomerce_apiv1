<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Access token TTL in minutes (short-lived for security)
     */
    private const ACCESS_TOKEN_TTL = 15; // 15 minutes

    /**
     * Refresh token TTL in days (longer-lived)
     */
    private const REFRESH_TOKEN_TTL_DAYS = 30; // 30 days

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => $request->role ?? 'buyer',
            'status' => 'active', // Users are active by default when registering
        ]);

        $tokens = $this->generateTokens($user, $request);

        return $this->createdResponse([
            'user' => $user,
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'token_type' => 'bearer',
            'expires_in' => self::ACCESS_TOKEN_TTL * 60, // Convert minutes to seconds
        ], 'User registered successfully');
    }

    /**
     * Login user and create tokens.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::customClaims(['exp' => now()->addMinutes(self::ACCESS_TOKEN_TTL)->timestamp])->attempt($credentials)) {
            return $this->errorResponse('Invalid email or password', 401);
        }

        $user = Auth::user();

        // Check if user is banned
        if ($user->status === 'banned') {
            return $this->errorResponse('Your account has been banned', 403);
        }

        $tokens = $this->generateTokens($user, $request);

        return $this->successResponse([
            'user' => $user,
            'access_token' => $tokens['access_token'],
            'refresh_token' => $tokens['refresh_token'],
            'token_type' => 'bearer',
            'expires_in' => self::ACCESS_TOKEN_TTL * 60, // Convert minutes to seconds
        ], 'Login successful');
    }

    /**
     * Get authenticated user.
     */
    public function me(): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not found');
            }

            return $this->successResponse($user, 'User retrieved successfully');
        } catch (\Exception $e) {
            return $this->unauthorizedResponse('Invalid or expired token');
        }
    }

    /**
     * Logout user (Invalidate tokens).
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Invalidate access token
            $token = JWTAuth::getToken();
            if ($token) {
                JWTAuth::invalidate($token);
            }

            // Revoke refresh token if provided
            $refreshToken = $request->input('refresh_token');
            if ($refreshToken) {
                $this->revokeRefreshToken($refreshToken);
            }

            return $this->successResponse(null, 'Successfully logged out');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to logout, please try again', 500);
        }
    }

    /**
     * Refresh access token using refresh token.
     * Implements token rotation: generates new refresh token and revokes old one.
     * Uses JWT for refresh tokens (consistent with access tokens).
     */
    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        try {
            $refreshTokenValue = $request->input('refresh_token');

            // First, validate the JWT refresh token
            try {
                $payload = JWTAuth::setToken($refreshTokenValue)->getPayload();

                // Check if this is actually a refresh token (not an access token)
                if ($payload->get('type') !== 'refresh') {
                    return $this->unauthorizedResponse('Invalid token type. Expected refresh token.');
                }

                // Get user from JWT
                $userId = $payload->get('sub');
                $user = User::find($userId);

                if (!$user) {
                    return $this->unauthorizedResponse('User not found');
                }

                // Check if user is banned
                if ($user->status === 'banned') {
                    $this->revokeRefreshToken($refreshTokenValue);
                    return $this->errorResponse('Your account has been banned', 403);
                }
            } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
                return $this->unauthorizedResponse('Refresh token has expired');
            } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
                return $this->unauthorizedResponse('Invalid refresh token');
            } catch (\Exception $e) {
                return $this->unauthorizedResponse('Unable to validate refresh token');
            }

            // Now check database for revocation and reuse detection
            // Use hash for faster lookup
            $refreshToken = RefreshToken::where('token_hash', hash('sha256', $refreshTokenValue))->first();

            // If token exists in database, check for revocation/reuse
            if ($refreshToken) {
                // Check if token has been reused (already revoked and replaced)
                if ($refreshToken->hasBeenReused()) {
                    // Token reuse detected - security breach! Revoke all tokens for this user
                    RefreshToken::where('user_id', $refreshToken->user_id)
                        ->where('is_revoked', false)
                        ->update(['is_revoked' => true]);
                    return $this->unauthorizedResponse('Token has been reused. All sessions revoked. Please login again.');
                }

                // Check if token is already revoked
                if ($refreshToken->is_revoked) {
                    return $this->unauthorizedResponse('Token has been revoked');
                }
            }

            // Generate new access token
            $accessToken = JWTAuth::customClaims([
                'exp' => now()->addMinutes(self::ACCESS_TOKEN_TTL)->timestamp,
                'type' => 'access',
            ])->fromUser($user);

            // Token rotation: Generate new JWT refresh token
            $newRefreshTokenValue = JWTAuth::customClaims([
                'exp' => now()->addDays(self::REFRESH_TOKEN_TTL_DAYS)->timestamp,
                'type' => 'refresh',
                'jti' => uniqid('rt_', true),
            ])->fromUser($user);

            // Create new refresh token record in database
            $newRefreshToken = RefreshToken::create([
                'user_id' => $user->id,
                'token' => $newRefreshTokenValue,
                'token_hash' => hash('sha256', $newRefreshTokenValue),
                'device_id' => $refreshToken->device_id ?? $request->header('X-Device-ID'),
                'device_name' => $refreshToken->device_name ?? $request->header('X-Device-Name'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'expires_at' => now()->addDays(self::REFRESH_TOKEN_TTL_DAYS),
            ]);

            // Revoke old token and mark which token replaced it
            if ($refreshToken) {
                $refreshToken->update([
                    'is_revoked' => true,
                    'replaced_by' => $newRefreshToken->id,
                    'last_used_at' => now(),
                ]);
            }

            return $this->successResponse([
                'user' => $user,
                'access_token' => $accessToken,
                'refresh_token' => $newRefreshTokenValue, // Return new JWT refresh token
                'token_type' => 'bearer',
                'expires_in' => self::ACCESS_TOKEN_TTL * 60, // Convert minutes to seconds
            ], 'Token refreshed successfully');
        } catch (\Exception $e) {
            return $this->unauthorizedResponse('Unable to refresh token: ' . $e->getMessage());
        }
    }

    /**
     * Generate both access and refresh tokens.
     * Both tokens are JWT tokens for consistency.
     */
    private function generateTokens(User $user, Request $request): array
    {
        // Generate access token (short-lived JWT)
        $accessToken = JWTAuth::customClaims([
            'exp' => now()->addMinutes(self::ACCESS_TOKEN_TTL)->timestamp,
            'type' => 'access',
        ])->fromUser($user);

        // Generate refresh token (longer-lived JWT)
        $refreshTokenValue = JWTAuth::customClaims([
            'exp' => now()->addDays(self::REFRESH_TOKEN_TTL_DAYS)->timestamp,
            'type' => 'refresh',
            'jti' => uniqid('rt_', true), // JWT ID for tracking
        ])->fromUser($user);

        // Store refresh token in database for revocation tracking and session management
        RefreshToken::create([
            'user_id' => $user->id,
            'token' => $refreshTokenValue, // Store the full JWT token
            'token_hash' => hash('sha256', $refreshTokenValue), // Hash for fast unique lookups
            'device_id' => $request->header('X-Device-ID'),
            'device_name' => $request->header('X-Device-Name'),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'expires_at' => now()->addDays(self::REFRESH_TOKEN_TTL_DAYS),
        ]);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshTokenValue,
        ];
    }

    /**
     * Revoke a refresh token (mark as revoked in database).
     * Note: JWT tokens themselves cannot be invalidated, but we track revocation in DB.
     */
    private function revokeRefreshToken(string $token): void
    {
        RefreshToken::where('token_hash', hash('sha256', $token))
            ->update(['is_revoked' => true]);
    }

    /**
     * Revoke all refresh tokens for a user (useful for logout all devices).
     */
    public function logoutAll(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not found');
            }

            // Revoke all refresh tokens for this user
            RefreshToken::where('user_id', $user->id)
                ->where('is_revoked', false)
                ->update(['is_revoked' => true]);

            // Invalidate current access token
            $token = JWTAuth::getToken();
            if ($token) {
                JWTAuth::invalidate($token);
            }

            return $this->successResponse(null, 'Logged out from all devices successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to logout from all devices', 500);
        }
    }
}
