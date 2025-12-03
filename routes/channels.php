<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::routes([
    'middleware' => ['api', 'auth:api'],
    'prefix' => 'api',
]);

Broadcast::channel('notifications.{userId}', function ($user, int $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('notifications.{userId}.{type}', function ($user, int $userId, string $type) {
    return (int) $user->id === (int) $userId;
});
