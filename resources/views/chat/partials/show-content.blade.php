@vite([
    'resources/css/chat.css',
    'resources/js/chat.js'
])

<div class="chat-page w-full h-full overflow-hidden bg-[#efeae2] flex flex-col" data-auth-id="{{ auth()->id() }}"
    data-user-id="{{ $user->id }}" data-csrf-token="{{ csrf_token() }}">

    {{-- HEADER --}}
    <div class="h-[74px] bg-white border-b border-slate-200 px-5 flex items-center justify-between shrink-0 shadow-sm">
        <div class="flex items-center gap-3 min-w-0">

            <button type="button" id="mobileBackToChats"
                class="lg:hidden w-10 h-10 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center shrink-0"
                title="Voltar">
                ←
            </button>

            @if ($user->profile_photo)
                <img src="{{ asset('storage/' . $user->profile_photo) }}"
                    class="w-12 h-12 rounded-full object-cover shrink-0">
            @else
                <div
                    class="w-12 h-12 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-lg shrink-0">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif

            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <strong class="text-slate-800 truncate">
                        {{ $user->name }}
                    </strong>

                    @if ($user->is_online)
                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-semibold">
                            Online
                        </span>
                    @else
                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 font-semibold">
                            Offline
                        </span>
                    @endif
                </div>

                <small class="text-slate-400 truncate block">
                    {{ $user->email }}
                </small>
            </div>
        </div>
    </div>

    {{-- MESSAGES --}}
    <div id="messagesBox" class="flex-1 overflow-y-auto px-4 md:px-8 lg:px-14 py-5 relative"
        style="background-image: radial-gradient(circle at 25px 25px, rgba(0,0,0,.04) 2px, transparent 2px); background-size:70px 70px;">

        @forelse ($messages as $message)

            @php
                $mine = $message->sender_id === auth()->id();
            @endphp

            <div id="message-{{ $message->id }}"
                class="wa-message-row flex mb-2 {{ $mine ? 'mine justify-end' : 'other justify-start' }}">

                <div
                    class="wa-bubble {{ $mine ? 'mine bg-emerald-100 rounded-tr-sm' : 'other bg-white rounded-tl-sm' }} max-w-[75%] md:max-w-[48%] px-3 py-2 rounded-2xl shadow-sm text-[14px] leading-snug">

                    @if ($message->deleted_for_everyone)

                        <em class="text-slate-400">
                            Mensagem apagada
                        </em>

                    @else

                        @if ($message->body)
                            <div class="wa-message-body message-body whitespace-pre-wrap break-words text-slate-800">
                                {{ $message->body }}
                            </div>
                        @endif

                        @if ($message->image)
                            <img src="{{ asset('storage/' . $message->image) }}" class="rounded-xl mt-2 max-w-[220px]">
                        @endif

                        @if ($message->audio)
                            <audio controls class="mt-2" style="max-width:250px;">
                                <source src="{{ asset('storage/' . $message->audio) }}" type="audio/webm">
                                O teu navegador não suporta áudio.
                            </audio>
                        @endif

                        @if ($mine)
                            <div class="wa-actions flex justify-end gap-2 mt-2">

                                @if ($message->body)
                                    <button type="button"
                                        class="edit-message-btn text-[11px] px-2.5 py-0.5 rounded-full bg-white/70 hover:bg-white text-slate-500"
                                        data-message-id="{{ $message->id }}">
                                        Editar
                                    </button>
                                @endif

                                <button type="button"
                                    class="delete-message-btn text-[11px] px-2.5 py-0.5 rounded-full bg-white/70 hover:bg-white text-slate-500"
                                    data-message-id="{{ $message->id }}">
                                    Apagar
                                </button>

                            </div>
                        @endif

                    @endif

                    <div class="wa-message-meta text-[10px] text-slate-400 mt-1 text-right whitespace-nowrap">

                        {{ $message->created_at->format('H:i') }}

                        @if ($message->edited_at)
                            · editada
                        @endif

                        @if ($mine)
                            ·
                            <span id="message-status-{{ $message->id }}">
                                @if ($message->read_at)
                                    ✓✓ Lida
                                @elseif ($message->delivered_at)
                                    ✓✓ Entregue
                                @else
                                    ✓ Enviada
                                @endif
                            </span>
                        @endif

                    </div>

                </div>
            </div>

        @empty
            <div class="text-center text-slate-400 mt-10">
                Nenhuma mensagem ainda.
            </div>
        @endforelse

    </div>

    {{-- TYPING --}}
    <div id="typingIndicator" class="hidden bg-slate-100 px-6 py-2 text-sm text-slate-500 shrink-0">
        Está digitando...
    </div>

    {{-- INPUT --}}
  <div class="bg-white border-t border-slate-200 p-2 sm:px-4 sm:py-3 shrink-0">

    {{-- PREVIEW DA IMAGEM --}}
    <div id="imagePreviewContainer" class="image-preview-container hidden mb-2">
        <div class="image-preview-box">
            <img id="imagePreview" src="" alt="Preview da imagem">

            <button type="button" id="removeImagePreview" class="remove-image-preview">
                ✕
            </button>
        </div>
    </div>

    <form id="chatForm"
        method="POST"
        action="{{ route('chat.send', $user) }}"
        enctype="multipart/form-data"
        class="flex items-center gap-2 w-full">

        @csrf

        <label for="imageInput"
            class="w-10 h-10 shrink-0 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 text-2xl flex items-center justify-center cursor-pointer"
            title="Enviar imagem">
            +
        </label>

        <input type="file"
            name="image"
            id="imageInput"
            class="hidden"
            accept="image/*">

        <button type="button"
            class="w-10 h-10 shrink-0 rounded-full bg-slate-100 hover:bg-slate-200 text-xl flex items-center justify-center">
            🙂
        </button>

        <input
            type="text"
            name="body"
            id="messageInput"
            placeholder="Escreve uma mensagem..."
            autocomplete="off"
            class="flex-1 min-w-0 rounded-full border-0 bg-slate-100 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-emerald-400">

        <button type="button"
            id="voiceNoteButton"
            class="w-10 h-10 shrink-0 rounded-full bg-slate-100 hover:bg-slate-200 text-xl flex items-center justify-center"
            title="Gravar áudio">
            🎤
        </button>

        <button type="submit"
            id="sendButton"
            class="w-10 h-10 shrink-0 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center font-semibold">
            ➤
        </button>

        <button type="button"
            class="hidden border border-slate-200 text-slate-500 px-4 py-3 rounded-full font-semibold hover:bg-slate-100 shrink-0"
            id="cancelEditButton">
            Cancelar
        </button>

    </form>

</div>
</div>