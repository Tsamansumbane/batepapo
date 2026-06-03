<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\GroupMember;

Broadcast::channel('group.{groupId}', function ($user, $groupId) {
    return GroupMember::where('chat_group_id', $groupId)
        ->where('user_id', $user->id)
        ->exists();
});

Broadcast::channel('chat.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('online-users', function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
});
