<x-app-layout>
    <div class="min-h-[calc(100vh-65px)] bg-slate-100 px-4 py-8">

        <div class="max-w-4xl mx-auto">

            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-600">
                        Amigos
                    </h1>
                    <p class="text-sm text-slate-400">
                        Gere os teus contactos e pedidos de amizade
                    </p>
                </div>

                <a href="{{ route('friends.search') }}"
                   class="px-4 py-2 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold no-underline shadow-sm">
                    + Adicionar amigo
                </a>
            </div>

            @if (session('success'))
                <div class="mb-4 rounded-2xl bg-emerald-50 border border-emerald-100 px-5 py-4 text-emerald-700 font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-2xl bg-red-50 border border-red-100 px-5 py-4 text-red-600 font-semibold">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-6">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-slate-800">
                            Pedidos recebidos
                        </h2>
                        <p class="text-xs text-slate-400">
                            Pessoas que querem conectar contigo
                        </p>
                    </div>
                </div>

                <div>
                    @forelse ($pendingRequests as $request)
                        <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-slate-100 hover:bg-slate-50 transition">

                            <div class="flex items-center gap-3 min-w-0">
                                @if ($request->sender->profile_photo)
                                    <img src="{{ asset('storage/' . $request->sender->profile_photo) }}"
                                         class="w-12 h-12 rounded-full object-cover shrink-0">
                                @else
                                    <div class="w-12 h-12 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold shrink-0">
                                        {{ strtoupper(substr($request->sender->name, 0, 1)) }}
                                    </div>
                                @endif

                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <div class="font-semibold text-slate-800 truncate">
                                            {{ $request->sender->name }}
                                        </div>

                                        <span id="friend-status-{{ $request->sender->id }}"
                                              class="text-[11px] px-2 py-0.5 rounded-full font-semibold {{ $request->sender->is_online ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $request->sender->is_online ? 'Online' : 'Offline' }}
                                        </span>
                                    </div>

                                    <div class="text-sm text-slate-500 truncate mt-1">
                                        {{ $request->sender->email }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <form method="POST" action="{{ route('friends.accept', $request) }}">
                                    @csrf

                                    <button class="px-4 py-2 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold">
                                        Aceitar
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('friends.reject', $request) }}">
                                    @csrf

                                    <button class="px-4 py-2 rounded-full bg-red-50 hover:bg-red-100 text-red-600 text-sm font-semibold">
                                        Recusar
                                    </button>
                                </form>
                            </div>

                        </div>
                    @empty
                        <div class="text-center text-slate-400 py-10">
                            Nenhum pedido pendente.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-slate-800">
                            Meus amigos
                        </h2>
                        <p class="text-xs text-slate-400">
                            Lista dos teus contactos
                        </p>
                    </div>
                </div>

                <div>
                    @forelse ($friends as $friend)
                        <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-slate-100 hover:bg-slate-50 transition">

                            <div class="flex items-center gap-3 min-w-0">
                                @if ($friend->profile_photo)
                                    <img src="{{ asset('storage/' . $friend->profile_photo) }}"
                                         class="w-12 h-12 rounded-full object-cover shrink-0">
                                @else
                                    <div class="w-12 h-12 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold shrink-0">
                                        {{ strtoupper(substr($friend->name, 0, 1)) }}
                                    </div>
                                @endif

                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <div class="font-semibold text-slate-800 truncate">
                                            {{ $friend->name }}
                                        </div>

                                        <span id="friend-status-{{ $friend->id }}"
                                              class="text-[11px] px-2 py-0.5 rounded-full font-semibold {{ $friend->is_online ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $friend->is_online ? 'Online' : 'Offline' }}
                                        </span>
                                    </div>

                                    <div class="text-sm text-slate-500 truncate mt-1">
                                        {{ $friend->email }}
                                    </div>

                                    <div class="text-sm text-slate-400 truncate mt-1" id="last-message-{{ $friend->id }}">
                                        @if ($friend->last_message)
                                            @if ($friend->last_message->deleted_for_everyone)
                                                Mensagem apagada
                                            @elseif ($friend->last_message->body)
                                                {{ \Illuminate\Support\Str::limit($friend->last_message->body, 35) }}
                                            @elseif ($friend->last_message->image)
                                                📷 Imagem
                                            @elseif ($friend->last_message->audio)
                                                🎤 Áudio
                                            @endif
                                        @else
                                            Nenhuma mensagem ainda.
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <span id="unread-count-{{ $friend->id }}"
                                      class="min-w-[22px] h-[22px] px-2 rounded-full bg-emerald-500 text-white text-xs font-bold flex items-center justify-center {{ $friend->unread_count > 0 ? '' : 'hidden' }}">
                                    {{ $friend->unread_count }}
                                </span>

                                <a href="{{ route('chat.show', $friend) }}"
                                   class="px-4 py-2 rounded-full border border-emerald-200 text-emerald-700 hover:bg-emerald-50 text-sm font-semibold no-underline">
                                    Conversar
                                </a>
                            </div>

                        </div>
                    @empty
                        <div class="text-center text-slate-400 py-12">
                            Ainda não tens amigos adicionados.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const authId = "{{ auth()->id() }}";

            function startOnlineEcho() {
                if (!window.Echo) {
                    setTimeout(startOnlineEcho, 500);
                    return;
                }

                window.Echo.join('online-users')
                    .here((users) => {
                        users.forEach(user => {
                            setUserOnline(user.id);
                        });
                    })
                    .joining((user) => {
                        setUserOnline(user.id);
                    })
                    .leaving((user) => {
                        setUserOffline(user.id);
                    });
            }

            function startUnreadEcho() {
                if (!window.Echo) {
                    setTimeout(startUnreadEcho, 500);
                    return;
                }

                window.Echo.private(`chat.${authId}`)
                    .listen('.message.sent', function (e) {
                        const senderId = e.message.sender_id;

                        const badge = document.getElementById(`unread-count-${senderId}`);
                        const lastMessage = document.getElementById(`last-message-${senderId}`);

                        if (badge) {
                            let currentCount = parseInt(badge.innerText || '0');

                            currentCount++;

                            badge.innerText = currentCount;
                            badge.classList.remove('hidden');
                        }

                        if (lastMessage) {
                            if (e.message.body) {
                                lastMessage.innerText = e.message.body.length > 35
                                    ? e.message.body.substring(0, 35) + '...'
                                    : e.message.body;
                            } else if (e.message.image) {
                                lastMessage.innerText = '📷 Imagem';
                            } else if (e.message.audio) {
                                lastMessage.innerText = '🎤 Áudio';
                            }
                        }
                    });
            }

            function setUserOnline(userId) {
                const badge = document.querySelector(`#friend-status-${userId}`);

                if (!badge) {
                    return;
                }

                badge.className = 'text-[11px] px-2 py-0.5 rounded-full font-semibold bg-emerald-100 text-emerald-700';
                badge.innerText = 'Online';
            }

            function setUserOffline(userId) {
                const badge = document.querySelector(`#friend-status-${userId}`);

                if (!badge) {
                    return;
                }

                badge.className = 'text-[11px] px-2 py-0.5 rounded-full font-semibold bg-slate-100 text-slate-500';
                badge.innerText = 'Offline';
            }

            startOnlineEcho();
            startUnreadEcho();
        });
    </script>
</x-app-layout>