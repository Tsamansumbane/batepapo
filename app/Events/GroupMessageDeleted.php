<?php

namespace App\Events;

use App\Models\GroupMessage;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GroupMessageDeleted implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(public GroupMessage $message) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('group.' . $this->message->chat_group_id);
    }

    public function broadcastAs(): string
    {
        return 'group.message.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'chat_group_id' => $this->message->chat_group_id,
                'sender_id' => $this->message->sender_id,
            ],
        ];
    }
}