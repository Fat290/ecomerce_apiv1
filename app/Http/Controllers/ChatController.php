<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ChatController extends Controller
{
    /**
     * List chats for the authenticated user (buyer or seller).
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $perPage = min(max((int) $request->input('per_page', 20), 1), 100);

            $chats = Chat::with([
                'buyer:id,name,avatar,email',
                'seller:id,name,avatar,email',
                'seller.shops:id,owner_id,name,logo,status',
            ])
                ->where(function ($query) use ($user) {
                    $query->where('buyer_id', $user->id)
                        ->orWhere('seller_id', $user->id);
                })
                ->orderByDesc('updated_at')
                ->paginate($perPage);

            return $this->paginatedResponse($chats, 'Chats retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve chats: ' . $e->getMessage());
        }
    }

    /**
     * Show a single chat thread.
     */
    public function show(Chat $chat): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            if ($user->id !== $chat->buyer_id && $user->id !== $chat->seller_id && $user->role !== 'admin') {
                return $this->forbiddenResponse('You do not have permission to view this chat');
            }

            $chat->load([
                'buyer:id,name,avatar,email',
                'seller:id,name,avatar,email',
                'seller.shops:id,owner_id,name,logo,status',
            ]);

            return $this->successResponse($chat, 'Chat retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve chat: ' . $e->getMessage());
        }
    }

    /**
     * Send a message to a shop (creates or reuses chat thread).
     */
    public function messageShop(Request $request, Shop $shop): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $validator = Validator::make($request->all(), [
                'message' => ['required', 'string', 'max:2000'],
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            if ($shop->status !== 'active') {
                return $this->errorResponse('You can only message shops that are open/active.', 400);
            }

            if ($shop->owner_id === $user->id) {
                return $this->errorResponse('You cannot message your own shop.', 400);
            }

            $seller = User::find($shop->owner_id);

            if (!$seller) {
                return $this->notFoundResponse('Shop owner not found.');
            }

            $chat = Chat::firstOrCreate([
                'buyer_id' => $user->id,
                'seller_id' => $seller->id,
            ]);

            $this->appendMessage($chat, $user, $request->input('message'));

            return $this->createdResponse($chat->fresh(), 'Message sent successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to send message: ' . $e->getMessage());
        }
    }

    /**
     * Reply inside an existing chat thread.
     */
    public function reply(Request $request, Chat $chat): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            if ($user->id !== $chat->buyer_id && $user->id !== $chat->seller_id) {
                return $this->forbiddenResponse('You are not a participant in this chat');
            }

            $validator = Validator::make($request->all(), [
                'message' => ['required', 'string', 'max:2000'],
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $this->appendMessage($chat, $user, $request->input('message'));

            return $this->successResponse($chat->fresh(), 'Message sent successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to send message: ' . $e->getMessage());
        }
    }

    protected function appendMessage(Chat $chat, User $sender, string $body): void
    {
        $messages = $chat->messages ?? [];
        $messages[] = [
            'sender_id' => $sender->id,
            'sender_role' => $sender->role,
            'body' => $body,
            'sent_at' => now()->toISOString(),
        ];

        $chat->messages = $messages;
        $chat->last_message = $body;
        $chat->updated_at = now();
        $chat->save();
    }
}
