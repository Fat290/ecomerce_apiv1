<?php

namespace App\Jobs;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;
    public string $type;
    public string $title;
    public string $message;
    public ?array $data;
    public ?string $actionUrl;

    /**
     * Create a new job instance.
     */
    public function __construct(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?array $data = null,
        ?string $actionUrl = null
    ) {
        $this->userId = $userId;
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
        $this->data = $data;
        $this->actionUrl = $actionUrl;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Notification::create([
                'user_id' => $this->userId,
                'type' => $this->type,
                'title' => $this->title,
                'message' => $this->message,
                'data' => $this->data,
                'action_url' => $this->actionUrl,
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send notification: ' . $e->getMessage(), [
                'user_id' => $this->userId,
                'type' => $this->type,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
