<?php

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Notification $notification)
    {
        $this->notification->refresh();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $userId = $this->notification->user_id;
        $type = $this->notification->type;

        return [
            new PrivateChannel("notifications.{$userId}"),
            new PrivateChannel("notifications.{$userId}.{$type}"),
        ];
    }

    /**
     * Customize the broadcast event name.
     */
    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    /**
     * The data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'user_id' => $this->notification->user_id,
            'type' => $this->notification->type,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            'data' => $this->notification->data,
            'action_url' => $this->notification->action_url,
            'is_read' => $this->notification->is_read,
            'read_at' => optional($this->notification->read_at)?->toISOString(),
            'created_at' => optional($this->notification->created_at)?->toISOString(),
        ];
    }
}
