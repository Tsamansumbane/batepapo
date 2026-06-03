<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Models\GroupMessage;

class DashboardController extends Controller
{
    public function index()
    {
        $friends = User::whereIn('id', function ($query) {
            $query->select('sender_id')
                ->from('friend_requests')
                ->where('receiver_id', auth()->id())
                ->where('status', 'accepted');
        })
        ->orWhereIn('id', function ($query) {
            $query->select('receiver_id')
                ->from('friend_requests')
                ->where('sender_id', auth()->id())
                ->where('status', 'accepted');
        })
        ->get();

        $friends = $friends->map(function ($friend) {
            $friend->last_message = Message::where(function ($query) use ($friend) {
                $query->where('sender_id', auth()->id())
                    ->where('receiver_id', $friend->id);
            })
            ->orWhere(function ($query) use ($friend) {
                $query->where('sender_id', $friend->id)
                    ->where('receiver_id', auth()->id());
            })
            ->latest()
            ->first();

            $friend->unread_count = Message::where('sender_id', $friend->id)
                ->where('receiver_id', auth()->id())
                ->whereNull('read_at')
                ->count();

            return $friend;
        });

        $groups = auth()->user()
            ->groups()
            ->with('creator')
            ->latest()
            ->get();

        $groups = $groups->map(function ($group) {
            $group->last_message = GroupMessage::where('chat_group_id', $group->id)
                ->with('sender')
                ->latest()
                ->first();

            return $group;
        });

        return view('dashboard', compact('friends', 'groups'));
    }
}