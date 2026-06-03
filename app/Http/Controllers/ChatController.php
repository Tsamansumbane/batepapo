<?php

namespace App\Http\Controllers;

use App\Events\MessageDeleted;
use App\Events\MessageSent;
use App\Events\MessageEdited;
use App\Models\Message;
use App\Events\MessageRead;
use App\Models\User;
use App\Events\UserTyping;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function show(User $user)
    {
        $messages = Message::where(function ($query) use ($user) {
            $query->where('sender_id', auth()->id())
                ->where('receiver_id', $user->id);
        })
            ->orWhere(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->where('receiver_id', auth()->id());
            })
            ->orderBy('created_at')
            ->get();

        $unreadMessages = Message::where('sender_id', $user->id)
            ->where('receiver_id', auth()->id())
            ->whereNull('read_at')
            ->get();

        foreach ($unreadMessages as $unreadMessage) {
            $unreadMessage->update([
                'read_at' => now(),
            ]);

            broadcast(new MessageRead($unreadMessage));
        }

        return view('chat.show', compact('user', 'messages'));
    }

    public function send(Request $request, User $user)
    {
        $request->validate([
            'body' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'audio' => 'nullable|file|mimes:webm,wav,ogg,mp3|max:10240',
        ]);

        if (
            !$request->body &&
            !$request->hasFile('image') &&
            !$request->hasFile('audio')
        ) {
            return response()->json([
                'message' => 'Escreve uma mensagem, envia uma imagem ou grava um áudio.',
            ], 422);
        }

        $imagePath = null;
        $audioPath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')
                ->store('chat-images', 'public');
        }

        if ($request->hasFile('audio')) {
            $audioPath = $request->file('audio')
                ->store('chat-audios', 'public');
        }

        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $user->id,
            'body' => $request->body,
            'image' => $imagePath,
            'audio' => $audioPath,
        ]);

        broadcast(new MessageSent($message));

        $data = [
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'receiver_id' => $message->receiver_id,
            'body' => $message->body,
            'image' => $message->image,
            'audio' => $message->audio,
            'created_at' => $message->created_at->format('H:i'),
        ];

        if ($request->expectsJson()) {
            return response()->json($data);
        }

        return back();
    }

    public function deleteForEveryone(Message $message)
    {
        if ($message->sender_id !== auth()->id()) {
            abort(403);
        }

        $message->update([
            'body' => null,
            'image' => null,
            'audio' => null,
            'deleted_for_everyone' => true,
        ]);

        broadcast(new MessageDeleted($message));

        if (request()->expectsJson()) {
            return response()->json([
                'id' => $message->id,
                'sender_id' => $message->sender_id,
                'receiver_id' => $message->receiver_id,
                'deleted_for_everyone' => true,
            ]);
        }

        return back();
    }

    public function updateMessage(Request $request, Message $message)
    {
        if ($message->sender_id !== auth()->id()) {
            abort(403);
        }

        if ($message->deleted_for_everyone) {
            return response()->json([
                'message' => 'Não podes editar uma mensagem apagada.',
            ], 422);
        }

        if ($message->image && !$message->body) {
            return response()->json([
                'message' => 'Mensagens apenas com imagem não podem ser editadas.',
            ], 422);
        }

        if ($message->audio && !$message->body) {
            return response()->json([
                'message' => 'Mensagens apenas com áudio não podem ser editadas.',
            ], 422);
        }

        $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $message->update([
            'body' => $request->body,
            'edited_at' => now(),
        ]);

        broadcast(new MessageEdited($message));

        return response()->json([
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'receiver_id' => $message->receiver_id,
            'body' => $message->body,
            'edited_at' => $message->edited_at->format('H:i'),
        ]);
    }

    public function typing(User $user)
    {
        broadcast(new UserTyping(
            auth()->id(),
            $user->id,
            auth()->user()->name
        ))->toOthers();

        return response()->json([
            'success' => true
        ]);
    }

    public function markDelivered(Message $message)
    {
        if ($message->receiver_id !== auth()->id()) {
            abort(403);
        }

        if (!$message->delivered_at) {
            $message->update([
                'delivered_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
        ]);
    }

    public function partial(User $user)
    {
        $messages = Message::where(function ($query) use ($user) {
            $query->where('sender_id', auth()->id())
                ->where('receiver_id', $user->id);
        })
            ->orWhere(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->where('receiver_id', auth()->id());
            })
            ->orderBy('created_at')
            ->get();

        $unreadMessages = Message::where('sender_id', $user->id)
            ->where('receiver_id', auth()->id())
            ->whereNull('read_at')
            ->get();

        foreach ($unreadMessages as $unreadMessage) {
            $unreadMessage->update([
                'read_at' => now(),
            ]);

            broadcast(new MessageRead($unreadMessage));
        }

        return view('chat.partials.show-content', compact('user', 'messages'));
    }
}