<x-app-layout>
    <style>
        .create-group-photo-picker {
            width: 96px;
            height: 96px;
            border-radius: 999px;
            background: #faf5ff;
            border: 3px solid #7e22ce;
            color: #6b21a8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: 700;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 22px rgba(126, 34, 206, 0.18);
        }

        .create-group-photo-picker img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 999px;
        }

        .create-group-photo-overlay {
            position: absolute;
            inset: auto 0 0 0;
            background: rgba(15, 23, 42, 0.72);
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            text-align: center;
            padding: 6px 0;
            opacity: 0;
            transition: 0.2s;
        }

        .create-group-photo-picker:hover .create-group-photo-overlay {
            opacity: 1;
        }
    </style>

    <div class="h-[calc(100vh-65px)] overflow-hidden bg-slate-100 flex">

        <aside id="chatSidebar" class="w-full lg:w-[430px] bg-white border-r border-slate-200 flex flex-col">

            <div class="px-5 py-4 border-b border-slate-100 bg-white">


                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-violet-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>

                    <input type="text" id="chatSearch"
                        class="w-full rounded-full border-0 bg-slate-100 pl-11 pr-4 py-3 text-sm outline-none focus:ring-2 focus:ring-emerald-400"
                        placeholder="Pesquisar conversa">
                </div>
            </div>

            <div class="flex-1 overflow-y-auto">

                @forelse ($friends as $friend)
                    <a id="chat-item-{{ $friend->id }}" href="{{ route('chat.show', $friend) }}"
                        data-chat-url="{{ route('chat.partial', $friend) }}"
                        class="js-open-chat chat-item group flex items-center gap-3 px-4 py-3 border-b border-slate-100 hover:bg-slate-50 transition text-slate-900 no-underline"
                        data-name="{{ strtolower($friend->name) }}" data-email="{{ strtolower($friend->email) }}">
                        <div class="relative shrink-0">
                            @if ($friend->profile_photo)
                                <img src="{{ asset('storage/' . $friend->profile_photo) }}"
                                    class="w-14 h-14 rounded-full object-cover">
                            @else
                                <div
                                    class="w-14 h-14 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-lg">
                                    {{ strtoupper(substr($friend->name, 0, 1)) }}
                                </div>
                            @endif

                            @if ($friend->is_online)
                                <span
                                    class="absolute bottom-0 right-0 w-3.5 h-3.5 rounded-full bg-emerald-500 border-2 border-white"></span>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-3">
                                <div class="font-semibold truncate">
                                    {{ $friend->name }}
                                </div>

                                <div class="chat-time text-xs text-slate-400 shrink-0">
                                    @if ($friend->last_message)
                                        {{ $friend->last_message->created_at->format('H:i') }}
                                    @endif
                                </div>
                            </div>

                            <div class="mt-1 flex items-center justify-between gap-3">
                                <div class="chat-last-message text-sm text-slate-500 truncate">
                                    @if ($friend->last_message)
                                        @if ($friend->last_message->deleted_for_everyone)
                                            Mensagem apagada
                                        @elseif ($friend->last_message->body)
                                            {{ \Illuminate\Support\Str::limit($friend->last_message->body, 45) }}
                                        @elseif ($friend->last_message->image)
                                            📷 Imagem
                                        @elseif ($friend->last_message->audio)
                                            🎤 Áudio
                                        @endif
                                    @else
                                        Nenhuma mensagem ainda.
                                    @endif
                                </div>

                                @if ($friend->unread_count > 0)
                                    <div
                                        class="wa-unread min-w-[22px] h-[22px] px-2 rounded-full bg-emerald-500 text-white text-xs font-bold flex items-center justify-center">
                                        {{ $friend->unread_count }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                @endforelse

                @if ($groups->isNotEmpty())
                    <div
                        class="px-5 py-2 text-xs font-bold uppercase tracking-wide text-slate-400 bg-slate-50 border-b border-slate-100">
                        Grupos
                    </div>
                @endif

                @forelse ($groups as $group)
                    <a href="{{ route('groups.show', $group) }}" data-chat-url="{{ route('groups.partial', $group) }}"
                        class="js-open-chat chat-item group flex items-center gap-3 px-4 py-3 border-b border-slate-100 hover:bg-slate-50 transition text-slate-900 no-underline"
                        data-name="{{ strtolower($group->name) }}" data-email="">
                        <div class="shrink-0">
                            @if ($group->photo)
                                <img src="{{ asset('storage/' . $group->photo) }}" class="w-14 h-14 rounded-full object-cover">
                            @else
                                <div
                                    class="w-14 h-14 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-lg">
                                    {{ strtoupper(substr($group->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-3">
                                <div class="font-semibold truncate">
                                    {{ $group->name }}
                                    <span
                                        class="ml-1 text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 border">
                                        Grupo
                                    </span>
                                </div>

                                <div class="text-xs text-slate-400 shrink-0">
                                    @if ($group->last_message)
                                        {{ $group->last_message->created_at->format('H:i') }}
                                    @endif
                                </div>
                            </div>

                            <div class="mt-1 text-sm text-slate-500 truncate">
                                @if ($group->last_message)
                                    @if ($group->last_message->deleted_for_everyone)
                                        Mensagem apagada
                                    @elseif ($group->last_message->body)
                                        {{ $group->last_message->sender->name }}:
                                        {{ \Illuminate\Support\Str::limit($group->last_message->body, 45) }}
                                    @elseif ($group->last_message->image)
                                        {{ $group->last_message->sender->name }}: 📷 Imagem
                                    @elseif ($group->last_message->audio)
                                        {{ $group->last_message->sender->name }}: 🎤 Áudio
                                    @endif
                                @else
                                    Nenhuma mensagem ainda.
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                @endforelse

                @if ($friends->isEmpty() && $groups->isEmpty())
                    <div class="p-8 text-center text-slate-400">
                        Ainda não tens conversas.
                    </div>
                @endif

            </div>



        </aside>

        <main id="chatContainer" class="hidden lg:flex flex-1 bg-[#efeae2] relative overflow-hidden w-full">

            <div class="w-full h-full flex items-center justify-center relative">

                <div class="absolute inset-0 opacity-50"
                    style="background-image: radial-gradient(circle at 25px 25px, rgba(0,0,0,.04) 2px, transparent 2px); background-size:70px 70px;">
                </div>

                <div class="relative z-10 text-center max-w-md px-6">
                    <div
                        class="w-32 h-32 mx-auto rounded-full bg-emerald-100 text-5xl flex items-center justify-center mb-6">
                        💬
                    </div>

                    <h2 class="text-3xl font-bold text-slate-700">
                        Batepapo Web
                    </h2>

                    <p class="mt-3 text-slate-500">
                        Seleciona uma conversa à esquerda para começar a conversar.
                    </p>
                </div>

            </div>

        </main>
    </div>

    <div id="contactsModal" class="fixed inset-0 z-50 hidden">

        <div class="absolute inset-0 bg-black/40" id="contactsModalOverlay"></div>

        <div class="relative z-10 min-h-screen flex items-center justify-center p-4">
            <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden">

                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                    <div>
                        <h5 class="text-lg font-bold text-slate-800">
                            Iniciar conversa
                        </h5>
                        <p class="text-xs text-slate-400">
                            Escolhe um contacto
                        </p>
                    </div>

                    <button type="button" id="closeContactsModal"
                        class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600">
                        ✕
                    </button>
                </div>

                <div class="p-4 border-b border-slate-100">
                    <input type="text" id="contactSearch"
                        class="w-full rounded-full border-0 bg-slate-100 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-emerald-400"
                        placeholder="Pesquisar contacto">
                </div>

                <a href="{{ route('friends.search') }}"
                    class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 hover:bg-slate-50 text-slate-900 no-underline">
                    <div
                        class="w-12 h-12 rounded-full bg-emerald-500 text-white flex items-center justify-center text-2xl">
                        +
                    </div>

                    <div>
                        <div class="font-semibold">Adicionar novo contacto</div>
                        <div class="text-sm text-slate-500">Procurar utilizador por nome ou email</div>
                    </div>
                </a>

                <div class="px-5 py-2 text-xs font-bold uppercase tracking-wide text-slate-400 bg-slate-50">
                    Contactos existentes
                </div>

                <div id="contactsList" class="max-h-[420px] overflow-y-auto">
                    @forelse ($friends as $friend)
                        <a href="{{ route('chat.show', $friend) }}" data-chat-url="{{ route('chat.partial', $friend) }}"
                            class="js-open-chat contact-item flex items-center gap-3 px-5 py-4 border-b border-slate-100 hover:bg-slate-50 text-slate-900 no-underline"
                            data-name="{{ strtolower($friend->name) }}" data-email="{{ strtolower($friend->email) }}">
                            @if ($friend->profile_photo)
                                <img src="{{ asset('storage/' . $friend->profile_photo) }}"
                                    class="w-12 h-12 rounded-full object-cover">
                            @else
                                <div
                                    class="w-12 h-12 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold">
                                    {{ strtoupper(substr($friend->name, 0, 1)) }}
                                </div>
                            @endif

                            <div class="min-w-0">
                                <div class="font-semibold truncate">
                                    {{ $friend->name }}

                                    @if ($friend->is_online)
                                        <span class="ml-1 text-xs text-emerald-600">Online</span>
                                    @else
                                        <span class="ml-1 text-xs text-slate-400">Offline</span>
                                    @endif
                                </div>

                                <div class="text-sm text-slate-500 truncate">
                                    {{ $friend->email }}
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center text-slate-400 py-10">
                            Ainda não tens contactos.
                        </div>
                    @endforelse
                </div>

            </div>
        </div>
    </div>


    <div id="createGroupModal" class="fixed inset-0 z-50 hidden">

        <div class="absolute inset-0 bg-black/40" id="createGroupModalOverlay"></div>

        <div class="relative z-10 min-h-screen flex items-center justify-center p-4 overflow-y-auto">
            <form id="createGroupForm" method="POST" action="{{ route('groups.store') }}" enctype="multipart/form-data"
                class="w-full max-w-md max-h-[90vh] bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col">

                @csrf

                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                    <div>
                        <h5 class="text-lg font-bold text-slate-800">
                            Criar grupo
                        </h5>
                        <p class="text-xs text-slate-400">
                            Define o nome, foto e membros do grupo
                        </p>
                    </div>

                    <button type="button" id="closeCreateGroupModal"
                        class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600">
                        ✕
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <div class="p-5 space-y-5 border-b border-slate-100">

                        <div class="flex flex-col items-center gap-3">
                            <label for="createGroupPhotoInput" class="create-group-photo-picker">
                                <img id="createGroupPhotoPreview" src="" alt="Foto do grupo" class="hidden">

                                <span id="createGroupPhotoInitial">
                                    📷
                                </span>

                                <div class="create-group-photo-overlay">
                                    Trocar
                                </div>
                            </label>

                            <input type="file" name="photo" id="createGroupPhotoInput" accept="image/*" class="hidden">

                            <p class="text-xs text-slate-400">
                                Toca na bolinha para escolher a foto do grupo
                            </p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-slate-400 mb-2">
                                Nome do grupo
                            </label>

                            <input type="text" name="name"
                                class="w-full rounded-full border-0 bg-slate-100 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-emerald-400"
                                placeholder="Ex: Família, Trabalho, Amigos..." required>
                        </div>
                    </div>

                    <div class="p-4 border-b border-slate-100">
                        <input type="text" id="createGroupSearch"
                            class="w-full rounded-full border-0 bg-slate-100 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-emerald-400"
                            placeholder="Pesquisar contacto">
                    </div>

                    <div class="px-5 py-2 text-xs font-bold uppercase tracking-wide text-slate-400 bg-slate-50">
                        Adicionar membros
                    </div>

                    <div class="min-h-[180px] max-h-[360px] overflow-y-auto">

                        @forelse ($friends as $friend)
                            <label
                                class="create-group-member flex items-center gap-3 px-5 py-4 border-b border-slate-100 hover:bg-slate-50 cursor-pointer"
                                data-name="{{ strtolower($friend->name) }}" data-email="{{ strtolower($friend->email) }}">

                                <input type="checkbox" name="members[]" value="{{ $friend->id }}"
                                    class="w-5 h-5 rounded border-slate-300 text-emerald-500 focus:ring-emerald-400">

                                @if ($friend->profile_photo)
                                    <img src="{{ asset('storage/' . $friend->profile_photo) }}"
                                        class="w-12 h-12 rounded-full object-cover">
                                @else
                                    <div
                                        class="w-12 h-12 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold">
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
                                Ainda não tens contactos para adicionar.
                            </div>
                        @endforelse

                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 px-5 py-4 border-t border-slate-100 bg-white shrink-0">
                    <button type="button" id="cancelCreateGroupModal"
                        class="px-4 py-2 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold">
                        Cancelar
                    </button>

                    <button type="submit"
                        class="px-4 py-2 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold">
                        Criar grupo
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- FLOATING ACTION BUTTON MOBILE --}}
    <button type="button" id="floatingNewChatButton"
        class="open-contacts-modal lg:hidden fixed right-5 bottom-5 z-[60] w-14 h-14 rounded-full bg-purple-700 hover:bg-purple-800 text-white shadow-2xl flex items-center justify-center transition"
        title="Iniciar conversa">
        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none">
            <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" />
        </svg>
    </button>

    <script>
        const authId = {{ auth()->id() }};

        function formatTime(dateString) {
            const date = new Date(dateString);

            return date.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function resetCreateGroupPhotoPreview() {
            const input = document.getElementById('createGroupPhotoInput');
            const preview = document.getElementById('createGroupPhotoPreview');
            const initial = document.getElementById('createGroupPhotoInitial');

            if (input) {
                input.value = '';
            }

            if (preview) {
                preview.src = '';
                preview.classList.add('hidden');
            }

            if (initial) {
                initial.classList.remove('hidden');
            }
        }

        function bindCreateGroupPhotoPreview() {
            const input = document.getElementById('createGroupPhotoInput');
            const preview = document.getElementById('createGroupPhotoPreview');
            const initial = document.getElementById('createGroupPhotoInitial');

            if (!input || !preview || !initial) {
                return;
            }

            input.addEventListener('change', function () {
                const file = this.files[0];

                if (!file) {
                    resetCreateGroupPhotoPreview();
                    return;
                }

                if (!file.type.startsWith('image/')) {
                    alert('Por favor seleciona apenas uma imagem.');
                    resetCreateGroupPhotoPreview();
                    return;
                }

                const reader = new FileReader();

                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    initial.classList.add('hidden');
                };

                reader.readAsDataURL(file);
            });
        }

        function moveChatToTop(userId, message) {
            const chatItem = document.getElementById(`chat-item-${userId}`);

            if (!chatItem) {
                return;
            }

            const lastMessage = chatItem.querySelector('.chat-last-message');

            if (lastMessage) {
                if (message.deleted_for_everyone) {
                    lastMessage.textContent = 'Mensagem apagada';
                } else if (message.body) {
                    lastMessage.textContent = message.body;
                } else if (message.image) {
                    lastMessage.textContent = '📷 Imagem';
                } else if (message.audio) {
                    lastMessage.textContent = '🎤 Áudio';
                }
            }

            const chatTime = chatItem.querySelector('.chat-time');

            if (chatTime) {
                chatTime.textContent = formatTime(message.created_at);
            }

            const unread = chatItem.querySelector('.wa-unread');

            if (unread) {
                unread.textContent = parseInt(unread.textContent || '0') + 1;
            } else {
                const bottom = chatItem.querySelector('.chat-last-message')?.parentNode;

                if (bottom) {
                    const badge = document.createElement('div');
                    badge.className = 'wa-unread min-w-[22px] h-[22px] px-2 rounded-full bg-emerald-500 text-white text-xs font-bold flex items-center justify-center';
                    badge.textContent = '1';
                    bottom.appendChild(badge);
                }
            }

            const parent = chatItem.parentNode;

            if (parent) {
                parent.prepend(chatItem);
            }
        }

        function startEchoChatList() {
            if (!window.Echo) {
                setTimeout(startEchoChatList, 500);
                return;
            }

            window.Echo.private(`chat.${authId}`)
                .listen('.message.sent', function (e) {
                    moveChatToTop(e.message.sender_id, e.message);
                });
        }

        function bindOpenChatLinks() {
            const chatContainer = document.getElementById('chatContainer');
            const contactsModal = document.getElementById('contactsModal');

            document.querySelectorAll('.js-open-chat').forEach(link => {
                link.onclick = function (e) {
                    e.preventDefault();

                    const url = this.dataset.chatUrl;

                    if (!url || !chatContainer) {
                        window.location.href = this.href;
                        return false;
                    }

                    if (contactsModal) {
                        contactsModal.classList.add('hidden');
                    }

                    chatContainer.innerHTML = `
                        <div class="w-full h-full flex items-center justify-center text-slate-500">
                            A carregar conversa...
                        </div>
                    `;

                    fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html',
                        }
                    })
                        .then(response => response.text())
                        .then(html => {
                            chatContainer.innerHTML = html;

                            const chatSidebar = document.getElementById('chatSidebar');

                            if (window.innerWidth < 1024) {
                                if (chatSidebar) {
                                    chatSidebar.classList.add('hidden');
                                }

                                chatContainer.classList.remove('hidden');
                                chatContainer.classList.remove('lg:flex');
                                chatContainer.classList.add('flex', 'w-full');

                                if (floatingNewChatButton) {
                                    floatingNewChatButton.classList.add('hidden');
                                }
                            } else {
                                chatContainer.classList.remove('hidden');
                                chatContainer.classList.add('flex');
                            }

                            chatContainer.querySelectorAll('script').forEach(oldScript => {
                                const newScript = document.createElement('script');

                                if (oldScript.src) {
                                    newScript.src = oldScript.src;
                                } else {
                                    newScript.textContent = oldScript.textContent;
                                }

                                document.body.appendChild(newScript);
                                oldScript.remove();
                            });
                        })
                        .catch(() => {
                            chatContainer.innerHTML = `
                            <div class="w-full h-full flex items-center justify-center text-red-500">
                                Erro ao carregar conversa.
                            </div>
                        `;
                        });

                    return false;
                };
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const contactsModal = document.getElementById('contactsModal');
            const floatingNewChatButton = document.getElementById('floatingNewChatButton');
            const closeContactsModal = document.getElementById('closeContactsModal');
            const contactsModalOverlay = document.getElementById('contactsModalOverlay');

            document.querySelectorAll('.open-contacts-modal').forEach(button => {
                button.addEventListener('click', function () {
                    if (contactsModal) {
                        contactsModal.classList.remove('hidden');
                    }
                });
            });

            if (closeContactsModal) {
                closeContactsModal.addEventListener('click', function () {
                    contactsModal.classList.add('hidden');
                });
            }

            if (contactsModalOverlay) {
                contactsModalOverlay.addEventListener('click', function () {
                    contactsModal.classList.add('hidden');
                });
            }

            const chatSearch = document.getElementById('chatSearch');

            if (chatSearch) {
                chatSearch.addEventListener('input', function () {
                    const search = this.value.toLowerCase();

                    document.querySelectorAll('.chat-item').forEach(item => {
                        const name = item.dataset.name || '';
                        const email = item.dataset.email || '';

                        item.style.display =
                            name.includes(search) || email.includes(search)
                                ? 'flex'
                                : 'none';
                    });
                });
            }

            const contactSearch = document.getElementById('contactSearch');

            if (contactSearch) {
                contactSearch.addEventListener('input', function () {
                    const search = this.value.toLowerCase();

                    document.querySelectorAll('.contact-item').forEach(item => {
                        const name = item.dataset.name || '';
                        const email = item.dataset.email || '';

                        item.style.display =
                            name.includes(search) || email.includes(search)
                                ? 'flex'
                                : 'none';
                    });
                });
            }

            const createGroupModal = document.getElementById('createGroupModal');
            const closeCreateGroupModal = document.getElementById('closeCreateGroupModal');
            const cancelCreateGroupModal = document.getElementById('cancelCreateGroupModal');
            const createGroupModalOverlay = document.getElementById('createGroupModalOverlay');

            function closeCreateGroup() {
                if (createGroupModal) {
                    createGroupModal.classList.add('hidden');
                }

                resetCreateGroupPhotoPreview();
            }

            document.querySelectorAll('.open-create-group-modal').forEach(button => {
                button.addEventListener('click', function () {
                    if (createGroupModal) {
                        createGroupModal.classList.remove('hidden');
                    }
                });
            });

            if (closeCreateGroupModal) {
                closeCreateGroupModal.addEventListener('click', closeCreateGroup);
            }

            if (cancelCreateGroupModal) {
                cancelCreateGroupModal.addEventListener('click', closeCreateGroup);
            }

            if (createGroupModalOverlay) {
                createGroupModalOverlay.addEventListener('click', closeCreateGroup);
            }

            bindCreateGroupPhotoPreview();

            const createGroupSearch = document.getElementById('createGroupSearch');

            if (createGroupSearch) {
                createGroupSearch.addEventListener('input', function () {
                    const search = this.value.toLowerCase();

                    document.querySelectorAll('.create-group-member').forEach(item => {
                        const name = item.dataset.name || '';
                        const email = item.dataset.email || '';

                        item.style.display =
                            name.includes(search) || email.includes(search)
                                ? 'flex'
                                : 'none';
                    });
                });
            }

            const createGroupForm = document.getElementById('createGroupForm');

            if (createGroupForm) {
                createGroupForm.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const formData = new FormData(createGroupForm);

                    fetch(createGroupForm.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData
                    })
                        .then(async response => {
                            const data = await response.json();

                            if (!response.ok) {
                                throw data;
                            }

                            return data;
                        })
                        .then(() => {
                            window.location.reload();
                        })
                        .catch(error => {
                            if (error.errors) {
                                const firstError = Object.values(error.errors)[0][0];
                                alert(firstError);
                                return;
                            }

                            alert(error.message || 'Erro ao criar grupo.');
                        });
                });
            }

            document.addEventListener('click', function (e) {
                if (e.target.closest('#mobileBackToChats')) {
                    const chatSidebar = document.getElementById('chatSidebar');
                    const chatContainer = document.getElementById('chatContainer');

                    if (chatSidebar) {
                        chatSidebar.classList.remove('hidden');
                    }

                    if (chatContainer) {
                        chatContainer.classList.add('hidden');
                        chatContainer.classList.remove('flex', 'w-full');

                        if (floatingNewChatButton) {
                            floatingNewChatButton.classList.remove('hidden');
                        }
                    }
                }
            });

            bindOpenChatLinks();
            startEchoChatList();
        });
    </script>
</x-app-layout>