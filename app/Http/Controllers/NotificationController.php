<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Build a normalized unread breakdown for the three notification types.
     */
    protected function unreadBreakdown(int $userId): array
    {
        $rawCounts = Notification::selectRaw('type, COUNT(*) as total')
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        return [
            'order' => (int) ($rawCounts['order'] ?? 0),
            'promotion' => (int) ($rawCounts['promotion'] ?? 0),
            'information' => (int) ($rawCounts['information'] ?? 0),
        ];
    }

    /**
     * Get all notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $query = Notification::where('user_id', $user->id);

            // Filter by type
            if ($request->filled('type')) {
                $query->where('type', $request->input('type'));
            }

            // Filter by read status
            if ($request->filled('is_read')) {
                $isRead = filter_var($request->input('is_read'), FILTER_VALIDATE_BOOLEAN);
                $query->where('is_read', $isRead);
            }

            // Sorting
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy('created_at', $sortOrder);

            // Pagination
            $perPage = $request->input('per_page', 15);
            $perPage = min(max(1, (int)$perPage), 100);

            $notifications = $query->paginate($perPage);

            // Get unread summary
            $unreadCount = Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->count();

            return $this->paginatedResponse($notifications, 'Notifications retrieved successfully', [
                'unread_count' => $unreadCount,
                'unread_breakdown' => $this->unreadBreakdown($user->id),
            ]);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve notifications: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific notification.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $notification = Notification::where('user_id', $user->id)
                ->find($id);

            if (!$notification) {
                return $this->notFoundResponse('Notification not found');
            }

            return $this->successResponse($notification, 'Notification retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve notification: ' . $e->getMessage());
        }
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(string $id): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $notification = Notification::where('user_id', $user->id)
                ->find($id);

            if (!$notification) {
                return $this->notFoundResponse('Notification not found');
            }

            $this->notificationService->markAsRead($notification);
            $notification->refresh();

            return $this->successResponse($notification, 'Notification marked as read');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to mark notification as read: ' . $e->getMessage());
        }
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $count = $this->notificationService->markAllAsRead($user);

            return $this->successResponse([
                'marked_count' => $count,
            ], "Marked {$count} notifications as read");
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to mark all notifications as read: ' . $e->getMessage());
        }
    }

    /**
     * Delete a notification.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $notification = Notification::where('user_id', $user->id)
                ->find($id);

            if (!$notification) {
                return $this->notFoundResponse('Notification not found');
            }

            $notification->delete();

            return $this->successResponse(null, 'Notification deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete notification: ' . $e->getMessage());
        }
    }

    /**
     * Get unread notification count.
     */
    public function unreadCount(): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $count = Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->count();

            return $this->successResponse([
                'unread_count' => $count,
                'unread_breakdown' => $this->unreadBreakdown($user->id),
            ], 'Unread count retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to get unread count: ' . $e->getMessage());
        }
    }

    /**
     * Test endpoint for sending notifications.
     * This is for testing purposes only.
     */
    public function test(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $request->validate([
                'user_id' => ['required', 'exists:users,id'],
                'type' => ['required', 'string', 'in:order,promotion,information'],
                'title' => ['required', 'string', 'max:255'],
                'message' => ['required', 'string'],
                'data' => ['nullable', 'array'],
                'action_url' => ['nullable', 'string', 'max:500'],
            ]);

            $targetUser = \App\Models\User::find($request->user_id);

            if (!$targetUser) {
                return $this->notFoundResponse('Target user not found');
            }

            $notification = $this->notificationService->send(
                $targetUser,
                $request->type,
                $request->title,
                $request->message,
                $request->data,
                $request->action_url
            );

            return $this->createdResponse($notification, 'Test notification sent successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to send test notification: ' . $e->getMessage());
        }
    }
}
