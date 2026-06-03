@vite([
    'resources/css/group-chat.css',
    'resources/js/group-chat.js'
])

<div class="wa-chat-page"
    data-auth-id="{{ auth()->id() }}"
    data-group-id="{{ $group->id }}"
    data-csrf-token="{{ csrf_token() }}">

    <div class="wa-chat-header">
        <div class="wa-user-info">

            <button type="button"
                    id="mobileBackToChats"
                    class="lg:hidden w-10 h-10 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center shrink-0"
                    title="Voltar">
                ←
            </button>

            @if ($group->photo)
                <img src="{{ asset('storage/' . $group->photo) }}" class="wa-avatar">
            @else
                <div class="wa-avatar-placeholder">
                    {{ strtoupper(substr($group->name, 0, 1)) }}
                </div>
            @endif

            <div>
                <strong>{{ $group->name }}</strong>
                <br>
                <small>{{ $members->count() }} membros</small>
            </div>

        </div>

        @if ($isGroupAdmin)
            <button type="button"
                    id="openGroupDetailsModal"
                    class="wa-icon-btn"
                    title="Detalhes do grupo">
                ⋮
            </button>
        @endif
    </div>

    <div id="groupMessagesBox" class="wa-chat-body">

        @forelse ($messages as $message)

            @php
                $mine = $message->sender_id === auth()->id();
            @endphp

            <div id="group-message-{{ $message->id }}" class="wa-message-row {{ $mine ? 'mine' : 'other' }}">

                <div class="wa-bubble {{ $mine ? 'mine' : 'other' }}">

                    @if (!$mine)
                        <div class="wa-sender-name">
                            {{ $message->sender->name }}
                        </div>
                    @endif

                    @if ($message->deleted_for_everyone)

                        <em class="text-muted">Mensagem apagada</em>

                    @else

                        @if ($message->body)
                            <div class="wa-message-body group-message-body">
                                {{ $message->body }}
                            </div>
                        @endif

                        @if ($message->image)
                            <img src="{{ asset('storage/' . $message->image) }}"
                                class="group-message-image">
                        @endif

                        @if ($message->audio)
                            <audio controls class="mt-2" style="max-width:250px;">
                                <source src="{{ asset('storage/' . $message->audio) }}" type="audio/webm">
                                O teu navegador não suporta áudio.
                            </audio>
                        @endif

                        @if ($mine)
                            <div class="wa-actions">

                                @if ($message->body)
                                    <button type="button"
                                            class="edit-group-message-btn"
                                            data-message-id="{{ $message->id }}">
                                        Editar
                                    </button>
                                @endif

                                <button type="button"
                                        class="delete-group-message-btn"
                                        data-message-id="{{ $message->id }}">
                                    Apagar
                                </button>

                            </div>
                        @endif

                    @endif

                    <div class="wa-message-meta">
                        {{ $message->created_at->format('H:i') }}

                        @if ($message->edited_at)
                            · editada
                        @endif
                    </div>

                </div>
            </div>

        @empty

            <div class="text-center text-muted mt-5">
                Nenhuma mensagem ainda.
            </div>

        @endforelse

    </div>

    <div class="wa-input-area bg-white border-t border-slate-200 p-2 sm:px-4 sm:py-3 shrink-0">

    {{-- PREVIEW DA IMAGEM --}}
    <div id="groupImagePreviewContainer" class="group-image-preview-container hidden mb-2">
        <div class="group-image-preview-box">
            <img id="groupImagePreview" src="" alt="Preview da imagem">

            <button type="button"
                    id="removeGroupImagePreview"
                    class="remove-group-image-preview">
                ✕
            </button>
        </div>
    </div>

    <form id="groupChatForm"
          method="POST"
          action="{{ route('groups.messages.send', $group) }}"
          enctype="multipart/form-data"
          class="wa-input-form flex items-center gap-2 w-full">

        @csrf

        <label for="groupImageInput"
               class="w-10 h-10 shrink-0 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 text-2xl flex items-center justify-center cursor-pointer"
               title="Enviar imagem">
            +
        </label>

        <input type="file"
               name="image"
               id="groupImageInput"
               accept="image/*"
               class="hidden">

        <button type="button"
                id="emojiButton"
                class="w-10 h-10 shrink-0 rounded-full bg-slate-100 hover:bg-slate-200 text-xl flex items-center justify-center"
                title="Emojis">
            🙂
        </button>

        <input type="text"
               name="body"
               id="groupMessageInput"
               class="flex-1 min-w-0 rounded-full border-0 bg-slate-100 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-emerald-400"
               placeholder="Escreve uma mensagem..."
               autocomplete="off">

        <div id="emojiPicker" class="emoji-picker hidden"></div>

        <button type="button"
                id="voiceNoteButton"
                class="w-10 h-10 shrink-0 rounded-full bg-slate-100 hover:bg-slate-200 text-xl flex items-center justify-center"
                title="Gravar áudio">
            🎤
        </button>

        <button type="submit"
                id="groupSendButton"
                class="w-10 h-10 shrink-0 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center font-semibold">
            ➤
        </button>

    </form>

</div>

</div>

@if ($isGroupAdmin)
    <div id="groupDetailsModal" class="fixed inset-0 z-50 hidden">

        <div class="absolute inset-0 bg-black/40" id="groupDetailsModalOverlay"></div>

        <div class="relative z-10 min-h-screen flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden">

                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                    <div>
                        <h5 class="text-lg font-bold text-slate-800">
                            Detalhes do grupo
                        </h5>
                        <p class="text-xs text-slate-400">
                            Gerir membros de {{ $group->name }}
                        </p>
                    </div>

                    <button type="button"
                            id="closeGroupDetailsModal"
                            class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600">
                        ✕
                    </button>
                </div>

                <div class="max-h-[75vh] overflow-y-auto">

                    <div class="px-5 py-2 text-xs font-bold uppercase tracking-wide text-slate-400 bg-slate-50">
                        Adicionar membros
                    </div>

                    <form method="POST"
                        action="{{ route('groups.members.add', $group) }}">

                        @csrf

                        <div class="p-4 border-b border-slate-100">
                            <input type="text"
                                id="groupMembersSearch"
                                class="w-full rounded-full border-0 bg-slate-100 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-emerald-400"
                                placeholder="Pesquisar amigo">
                        </div>

                        <div class="max-h-[260px] overflow-y-auto">

                            @forelse ($availableFriends as $friend)
                                <label class="group-member-option flex items-center gap-3 px-5 py-4 border-b border-slate-100 hover:bg-slate-50 cursor-pointer"
                                    data-name="{{ strtolower($friend->name) }}"
                                    data-email="{{ strtolower($friend->email) }}">

                                    <input type="checkbox"
                                        name="members[]"
                                        value="{{ $friend->id }}"
                                        class="w-5 h-5 rounded border-slate-300 text-emerald-500 focus:ring-emerald-400">

                                    @if ($friend->profile_photo)
                                        <img src="{{ asset('storage/' . $friend->profile_photo) }}"
                                            class="w-12 h-12 rounded-full object-cover">
                                    @else
                                        <div class="w-12 h-12 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold">
                                            {{ strtoupper(substr($friend->name, 0, 1)) }}
                                        </div>
                                    @endif

                                    <div class="min-w-0">
                                        <div class="font-semibold truncate text-slate-800">
                                            {{ $friend->name }}
                                        </div>

                                        <div class="text-sm text-slate-500 truncate">
                                            {{ $friend->email }}
                                        </div>
                                    </div>
                                </label>
                            @empty
                                <div class="text-center text-slate-400 py-10">
                                    Todos os teus amigos já fazem parte deste grupo.
                                </div>
                            @endforelse

                        </div>

                        @if ($availableFriends->count())
                            <div class="flex items-center justify-end gap-2 px-5 py-4 border-b border-slate-100">
                                <button type="submit"
                                        class="px-4 py-2 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold">
                                    Adicionar selecionados
                                </button>
                            </div>
                        @endif

                    </form>

                    <div class="px-5 py-2 text-xs font-bold uppercase tracking-wide text-slate-400 bg-slate-50">
                        Membros atuais
                    </div>

                    <div class="max-h-[320px] overflow-y-auto">

                        @foreach ($members as $member)
                            <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-slate-100">

                                <div class="flex items-center gap-3 min-w-0">

                                    @if ($member->user->profile_photo)
                                        <img src="{{ asset('storage/' . $member->user->profile_photo) }}"
                                            class="w-12 h-12 rounded-full object-cover">
                                    @else
                                        <div class="w-12 h-12 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold">
                                            {{ strtoupper(substr($member->user->name, 0, 1)) }}
                                        </div>
                                    @endif

                                    <div class="min-w-0">
                                        <div class="font-semibold truncate text-slate-800">
                                            {{ $member->user->name }}

                                            @if ($member->is_admin)
                                                <span class="ml-1 text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">
                                                    Admin
                                                </span>
                                            @endif

                                            @if ($group->created_by === $member->user_id)
                                                <span class="ml-1 text-[10px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">
                                                    Criador
                                                </span>
                                            @endif
                                        </div>

                                        <div class="text-sm text-slate-500 truncate">
                                            {{ $member->user->email }}
                                        </div>
                                    </div>

                                </div>

                                @if ($group->created_by !== $member->user_id && $member->user_id !== auth()->id())
                                    <form method="POST"
                                        action="{{ route('groups.members.remove', [$group, $member->user]) }}">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                onclick="return confirm('Remover este membro do grupo?')"
                                                class="px-3 py-1.5 rounded-full bg-red-50 hover:bg-red-100 text-red-600 text-sm font-semibold">
                                            Remover
                                        </button>
                                    </form>
                                @endif

                            </div>
                        @endforeach

                    </div>

                </div>

                <div class="flex items-center justify-end gap-2 px-5 py-4 border-t border-slate-100">
                    <button type="button"
                            id="cancelGroupDetailsModal"
                            class="px-4 py-2 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold">
                        Fechar
                    </button>
                </div>

            </div>
        </div>
    </div>
@endif