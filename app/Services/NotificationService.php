<?php

namespace App\Services;

use App\Jobs\SendNotificationJob;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send a notification to a user (queued).
     *
     * @param int|User $user
     * @param string $type One of: order, promotion, information
     * @param string $title
     * @param string $message
     * @param array|null $data
     * @param string|null $actionUrl
     * @return Notification
     */
    public function send($user, string $type, string $title, string $message, ?array $data = null, ?string $actionUrl = null): Notification
    {
        $userId = $user instanceof User ? $user->id : $user;

        // Dispatch job to queue for async processing
        SendNotificationJob::dispatch($userId, $type, $title, $message, $data, $actionUrl);

        // Return a placeholder notification (actual one will be created in the job)
        return new Notification([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
        ]);
    }

    /**
     * Send notification immediately (synchronous).
     *
     * @param int|User $user
     * @param string $type Notification category: order, promotion, information
     * @param string $title
     * @param string $message
     * @param array|null $data
     * @param string|null $actionUrl
     * @return Notification
     */
    public function sendNow($user, string $type, string $title, string $message, ?array $data = null, ?string $actionUrl = null): Notification
    {
        $userId = $user instanceof User ? $user->id : $user;

        $notification = Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'action_url' => $actionUrl,
            'is_read' => false,
        ]);

        return $notification;
    }

    /**
     * Send notification for order update.
     */
    public function sendOrderUpdate(User $user, int $orderId, string $status, string $message): Notification
    {
        return $this->send(
            $user,
            'order',
            'Order Update',
            $message,
            ['order_id' => $orderId, 'status' => $status],
            "/orders/{$orderId}"
        );
    }

    /**
     * Send notification when a new order is placed (for shop owner).
     */
    public function sendOrderPlaced(User $shopOwner, int $orderId, string $buyerName, float $totalAmount): Notification
    {
        return $this->send(
            $shopOwner,
            'order',
            'New Order Received',
            "You have received a new order from {$buyerName} for $" . number_format($totalAmount, 2),
            ['order_id' => $orderId, 'buyer_name' => $buyerName, 'total_amount' => $totalAmount],
            "/orders/{$orderId}"
        );
    }

    /**
     * Send notification for chat message.
     */
    public function sendChatMessage(User $user, int $chatId, string $senderName, string $message): Notification
    {
        return $this->send(
            $user,
            'information',
            'New Message',
            "{$senderName}: " . substr($message, 0, 100),
            ['chat_id' => $chatId, 'sender_name' => $senderName],
            "/chats/{$chatId}"
        );
    }

    /**
     * Send promotion notification.
     */
    public function sendPromotion(User $user, string $title, string $message, ?string $promoCode = null, ?string $actionUrl = null): Notification
    {
        return $this->send(
            $user,
            'promotion',
            $title,
            $message,
            ['promo_code' => $promoCode],
            $actionUrl ?? '/promotions'
        );
    }

    /**
     * Send notification for product review.
     */
    public function sendProductReview(User $shopOwner, int $productId, string $productName, string $reviewerName, int $rating): Notification
    {
        return $this->send(
            $shopOwner,
            'information',
            'New Product Review',
            "{$reviewerName} left a {$rating}-star review on {$productName}",
            ['product_id' => $productId, 'reviewer_name' => $reviewerName, 'rating' => $rating],
            "/products/{$productId}"
        );
    }

    /**
     * Send notification for shop review.
     */
    public function sendShopReview(User $shopOwner, int $shopId, string $shopName, string $reviewerName, int $rating): Notification
    {
        return $this->send(
            $shopOwner,
            'information',
            'New Shop Review',
            "{$reviewerName} left a {$rating}-star review on your shop",
            ['shop_id' => $shopId, 'reviewer_name' => $reviewerName, 'rating' => $rating],
            "/shops/{$shopId}"
        );
    }

    /**
     * Send high-level information/system notification.
     */
    public function sendInformation(User $user, string $title, string $message, ?array $data = null, ?string $actionUrl = null): Notification
    {
        return $this->send(
            $user,
            'information',
            $title,
            $message,
            $data,
            $actionUrl
        );
    }

    /**
     * @deprecated Use sendInformation instead.
     */
    public function sendSystem(User $user, string $title, string $message, ?array $data = null, ?string $actionUrl = null): Notification
    {
        return $this->sendInformation($user, $title, $message, $data, $actionUrl);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Notification $notification): bool
    {
        if ($notification->is_read) {
            return true;
        }

        return $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsRead(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
}
