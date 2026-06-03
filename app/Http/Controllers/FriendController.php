<?php

namespace App\Http\Controllers;

use App\Models\FriendRequest;
use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;

class FriendController extends Controller
{
    public function index()
    {
        $pendingRequests = FriendRequest::where('receiver_id', auth()->id())
            ->where('status', 'pending')
            ->with('sender')
            ->get();

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

        return view('friends.index', compact('pendingRequests', 'friends'));
    }

    public function search(Request $request)
    {
        $users = collect();

        if ($request->filled('q')) {
            $users = User::where('id', '!=', auth()->id())
                ->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->q . '%')
                        ->orWhere('email', 'like', '%' . $request->q . '%');
                })
                ->limit(10)
                ->get();
        }

        return view('friends.search', compact('users'));
    }

    public function sendRequest(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors([
                'error' => 'Não podes adicionar a ti mesmo.',
            ]);
        }

        $exists = FriendRequest::where(function ($query) use ($user) {
            $query->where('sender_id', auth()->id())
                ->where('receiver_id', $user->id);
        })
            ->orWhere(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->where('receiver_id', auth()->id());
            })
            ->first();

        if ($exists) {
            return back()->withErrors([
                'error' => 'Já existe um pedido ou amizade com este utilizador.',
            ]);
        }

        FriendRequest::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $user->id,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Pedido de amizade enviado.');
    }

    public function accept(FriendRequest $friendRequest)
    {
        if ($friendRequest->receiver_id !== auth()->id()) {
            abort(403);
        }

        $friendRequest->update([
            'status' => 'accepted',
        ]);

        return back()->with('success', 'Pedido aceite.');
    }

    public function reject(FriendRequest $friendRequest)
    {
        if ($friendRequest->receiver_id !== auth()->id()) {
            abort(403);
        }

        $friendRequest->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', 'Pedido recusado.');
    }

    public function contacts()
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
            ->orderBy('name')
            ->get();

        return view('friends.contacts', compact('friends'));
    }
}