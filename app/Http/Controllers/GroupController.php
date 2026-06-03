<?php

namespace App\Http\Controllers;

use App\Events\GroupMessageDeleted;
use App\Models\ChatGroup;
use App\Models\GroupMember;
use App\Models\GroupMessage;
use App\Events\GroupMessageSent;
use App\Events\GroupMessageEdited;
use App\Models\GroupMessageRead;
use App\Models\User;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index()
    {
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

            $group->unread_count = GroupMessage::where('chat_group_id', $group->id)
                ->where('sender_id', '!=', auth()->id())
                ->whereDoesntHave('reads', function ($query) {
                    $query->where('user_id', auth()->id());
                })
                ->count();

            return $group;
        });

        return view('groups.index', compact('groups'));
    }

    public function create()
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

        return view('groups.create', compact('friends'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'members' => 'required|array',
            'members.*' => 'exists:users,id',
        ]);

        $photoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('group-photos', 'public');
        }

        $group = ChatGroup::create([
            'created_by' => auth()->id(),
            'name' => $request->name,
            'photo' => $photoPath,
        ]);

        GroupMember::create([
            'chat_group_id' => $group->id,
            'user_id' => auth()->id(),
            'is_admin' => true,
        ]);

        foreach ($request->members as $memberId) {
            GroupMember::firstOrCreate([
                'chat_group_id' => $group->id,
                'user_id' => $memberId,
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Grupo criado com sucesso.',
                'group' => [
                    'id' => $group->id,
                    'name' => $group->name,
                    'photo' => $group->photo,
                    'show_url' => route('groups.show', $group),
                    'partial_url' => route('groups.partial', $group),
                ],
            ]);
        }

        return redirect()
            ->route('groups.index')
            ->with('success', 'Grupo criado com sucesso.');
    }

    public function show(ChatGroup $group)
    {
        $isMember = GroupMember::where('chat_group_id', $group->id)
            ->where('user_id', auth()->id())
            ->exists();

        if (!$isMember) {
            abort(403);
        }

        $messages = GroupMessage::where('chat_group_id', $group->id)
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        foreach ($messages as $message) {
            if ($message->sender_id !== auth()->id()) {
                GroupMessageRead::firstOrCreate([
                    'group_message_id' => $message->id,
                    'user_id' => auth()->id(),
                ], [
                    'read_at' => now(),
                ]);
            }
        }

        $members = GroupMember::where('chat_group_id', $group->id)
            ->with('user')
            ->get();

        $memberIds = $members->pluck('user_id')->toArray();

        $availableFriends = User::whereNotIn('id', $memberIds)
            ->where(function ($query) {
                $query->whereIn('id', function ($q) {
                    $q->select('sender_id')
                        ->from('friend_requests')
                        ->where('receiver_id', auth()->id())
                        ->where('status', 'accepted');
                })
                    ->orWhereIn('id', function ($q) {
                        $q->select('receiver_id')
                            ->from('friend_requests')
                            ->where('sender_id', auth()->id())
                            ->where('status', 'accepted');
                    });
            })
            ->get();

        $myMembership = GroupMember::where('chat_group_id', $group->id)
            ->where('user_id', auth()->id())
            ->first();

        $isGroupAdmin = $myMembership && $myMembership->is_admin;

        return view('groups.show', compact(
            'group',
            'messages',
            'members',
            'availableFriends',
            'isGroupAdmin'
        ));
    }

    public function sendMessage(Request $request, ChatGroup $group)
    {
        $isMember = GroupMember::where('chat_group_id', $group->id)
            ->where('user_id', auth()->id())
            ->exists();

        if (!$isMember) {
            abort(403);
        }

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
                ->store('group-chat-images', 'public');
        }

        if ($request->hasFile('audio')) {
            $audioPath = $request->file('audio')
                ->store('group-chat-audios', 'public');
        }

        $message = GroupMessage::create([
            'chat_group_id' => $group->id,
            'sender_id' => auth()->id(),
            'body' => $request->body,
            'image' => $imagePath,
            'audio' => $audioPath,
        ]);

        $message->load('sender');
        broadcast(new GroupMessageSent($message));

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $message->id,
                'chat_group_id' => $message->chat_group_id,
                'sender_id' => $message->sender_id,
                'sender_name' => $message->sender->name,
                'body' => $message->body,
                'image' => $message->image,
                'audio' => $message->audio,
                'created_at' => $message->created_at->format('H:i'),
            ]);
        }

        return back();
    }

    public function deleteMessage(GroupMessage $message)
    {
        $isMember = GroupMember::where('chat_group_id', $message->chat_group_id)
            ->where('user_id', auth()->id())
            ->exists();

        if (!$isMember) {
            abort(403);
        }

        if ($message->sender_id !== auth()->id()) {
            abort(403);
        }

        $message->update([
            'body' => null,
            'image' => null,
            'audio' => null,
            'deleted_for_everyone' => true,
        ]);

        broadcast(new GroupMessageDeleted($message));

        if (request()->expectsJson()) {
            return response()->json([
                'id' => $message->id,
                'chat_group_id' => $message->chat_group_id,
                'sender_id' => $message->sender_id,
                'deleted_for_everyone' => true,
            ]);
        }

        return back();
    }

    public function addMembers(Request $request, ChatGroup $group)
    {
        $this->ensureGroupAdmin($group);

        $request->validate([
            'members' => 'required|array',
            'members.*' => 'exists:users,id',
        ]);

        foreach ($request->members as $memberId) {
            GroupMember::firstOrCreate([
                'chat_group_id' => $group->id,
                'user_id' => $memberId,
            ]);
        }

        return back()->with('success', 'Membros adicionados com sucesso.');
    }

    private function ensureGroupAdmin(ChatGroup $group)
    {
        $isAdmin = GroupMember::where('chat_group_id', $group->id)
            ->where('user_id', auth()->id())
            ->where('is_admin', true)
            ->exists();

        if (!$isAdmin) {
            abort(403);
        }
    }

    public function removeMember(ChatGroup $group, User $user)
    {
        $this->ensureGroupAdmin($group);

        if ($group->created_by === $user->id) {
            return back()->withErrors([
                'error' => 'Não podes remover o criador do grupo.',
            ]);
        }

        if ($user->id === auth()->id()) {
            return back()->withErrors([
                'error' => 'Usa a opção sair do grupo.',
            ]);
        }

        GroupMember::where('chat_group_id', $group->id)
            ->where('user_id', $user->id)
            ->delete();

        return back()->with('success', 'Membro removido com sucesso.');
    }

    public function makeAdmin(ChatGroup $group, User $user)
    {
        $this->ensureGroupAdmin($group);

        $member = GroupMember::where('chat_group_id', $group->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $member->update([
            'is_admin' => true,
        ]);

        return back()->with('success', 'Membro tornou-se admin.');
    }

    public function leaveGroup(ChatGroup $group)
    {
        $member = GroupMember::where('chat_group_id', $group->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($group->created_by === auth()->id()) {
            return back()->withErrors([
                'error' => 'O criador do grupo não pode sair por enquanto.',
            ]);
        }

        $member->delete();

        return redirect()
            ->route('groups.index')
            ->with('success', 'Saíste do grupo.');
    }

    public function update(Request $request, ChatGroup $group)
    {
        $this->ensureGroupAdmin($group);

        $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $photoPath = $group->photo;

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')
                ->store('group-photos', 'public');
        }

        $group->update([
            'name' => $request->name,
            'photo' => $photoPath,
        ]);

        return back()->with('success', 'Grupo atualizado com sucesso.');
    }

    public function updateMessage(Request $request, GroupMessage $message)
    {
        $isMember = GroupMember::where('chat_group_id', $message->chat_group_id)
            ->where('user_id', auth()->id())
            ->exists();

        if (!$isMember) {
            abort(403);
        }

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

        broadcast(new GroupMessageEdited($message));

        return response()->json([
            'id' => $message->id,
            'chat_group_id' => $message->chat_group_id,
            'sender_id' => $message->sender_id,
            'body' => $message->body,
            'edited_at' => $message->edited_at->format('H:i'),
        ]);
    }

    public function partial(ChatGroup $group)
    {
        $isMember = GroupMember::where('chat_group_id', $group->id)
            ->where('user_id', auth()->id())
            ->exists();

        if (!$isMember) {
            abort(403);
        }

        $messages = GroupMessage::where('chat_group_id', $group->id)
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        foreach ($messages as $message) {
            if ($message->sender_id !== auth()->id()) {
                GroupMessageRead::firstOrCreate([
                    'group_message_id' => $message->id,
                    'user_id' => auth()->id(),
                ], [
                    'read_at' => now(),
                ]);
            }
        }

        $members = GroupMember::where('chat_group_id', $group->id)
            ->with('user')
            ->get();

        $memberIds = $members->pluck('user_id')->toArray();

        $availableFriends = User::whereNotIn('id', $memberIds)
            ->where(function ($query) {
                $query->whereIn('id', function ($q) {
                    $q->select('sender_id')
                        ->from('friend_requests')
                        ->where('receiver_id', auth()->id())
                        ->where('status', 'accepted');
                })
                    ->orWhereIn('id', function ($q) {
                        $q->select('receiver_id')
                            ->from('friend_requests')
                            ->where('sender_id', auth()->id())
                            ->where('status', 'accepted');
                    });
            })
            ->get();

        $myMembership = GroupMember::where('chat_group_id', $group->id)
            ->where('user_id', auth()->id())
            ->first();

        $isGroupAdmin = $myMembership && $myMembership->is_admin;

        return view('groups.partials.show-content', compact(
            'group',
            'messages',
            'members',
            'availableFriends',
            'isGroupAdmin'
        ));
    }
}