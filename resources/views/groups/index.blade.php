<x-app-layout>
    <div class="min-h-[calc(100vh-65px)] bg-slate-100 px-4 py-8">

        <div class="max-w-4xl mx-auto">

            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-600">
                        Grupos
                    </h1>
                    <p class="text-sm text-slate-400">
                        Conversas em grupo onde participas
                    </p>
                </div>

                <a href="{{ route('groups.create') }}"
                   class="px-4 py-2 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold no-underline shadow-sm">
                    + Criar grupo
                </a>
            </div>

            @if (session('success'))
                <div class="mb-4 rounded-2xl bg-emerald-50 border border-emerald-100 px-5 py-4 text-emerald-700 font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">

                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="font-bold text-slate-800">
                        Meus grupos
                    </h2>
                    <p class="text-xs text-slate-400">
                        Abre um grupo para continuar a conversa
                    </p>
                </div>

                <div>
                    @forelse ($groups as $group)
                        <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-slate-100 hover:bg-slate-50 transition">

                            <div class="flex items-center gap-3 min-w-0">

                                @if ($group->photo)
                                    <img src="{{ asset('storage/' . $group->photo) }}"
                                         class="w-14 h-14 rounded-full object-cover shrink-0">
                                @else
                                    <div class="w-14 h-14 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-lg shrink-0">
                                        {{ strtoupper(substr($group->name, 0, 1)) }}
                                    </div>
                                @endif

                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <div class="font-semibold text-slate-800 truncate">
                                            {{ $group->name }}
                                        </div>

                                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 border">
                                            Grupo
                                        </span>
                                    </div>

                                    <div class="text-sm text-slate-500 truncate mt-1">
                                        Criado por {{ $group->creator->name }}
                                    </div>

                                    <div class="text-sm text-slate-400 truncate mt-1"
                                         id="group-last-message-{{ $group->id }}">
                                        @if ($group->last_message)
                                            @if ($group->last_message->deleted_for_everyone)
                                                Mensagem apagada
                                            @elseif ($group->last_message->body)
                                                {{ $group->last_message->sender->name }}:
                                                {{ \Illuminate\Support\Str::limit($group->last_message->body, 35) }}
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

                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <span id="group-unread-count-{{ $group->id }}"
                                      class="min-w-[22px] h-[22px] px-2 rounded-full bg-emerald-500 text-white text-xs font-bold flex items-center justify-center {{ $group->unread_count > 0 ? '' : 'hidden' }}">
                                    {{ $group->unread_count }}
                                </span>

                                <a href="{{ route('groups.show', $group) }}"
                                   class="px-4 py-2 rounded-full border border-emerald-200 text-emerald-700 hover:bg-emerald-50 text-sm font-semibold no-underline">
                                    Abrir
                                </a>
                            </div>

                        </div>
                    @empty
                        <div class="text-center text-slate-400 py-12">
                            Ainda não participas em nenhum grupo.
                        </div>
                    @endforelse
                </div>

            </div>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const authId = "{{ auth()->id() }}";

            function startGroupUnreadEcho() {
                if (!window.Echo) {
                    setTimeout(startGroupUnreadEcho, 500);
                    return;
                }

                @foreach ($groups as $group)
                    window.Echo.private('group.{{ $group->id }}')
                        .listen('.group.message.sent', function (e) {
                            if (e.message.sender_id == authId) {
                                return;
                            }

                            const badge = document.getElementById(
                                'group-unread-count-' + e.message.chat_group_id
                            );

                            const lastMessage = document.getElementById(
                                'group-last-message-' + e.message.chat_group_id
                            );

                            if (badge) {
                                let currentCount = parseInt(badge.innerText || '0');
                                currentCount++;

                                badge.innerText = currentCount;
                                badge.classList.remove('hidden');
                            }

                            if (lastMessage) {
                                if (e.message.body) {
                                    const text = e.message.sender_name + ': ' + e.message.body;

                                    lastMessage.innerText = text.length > 35
                                        ? text.substring(0, 35) + '...'
                                        : text;
                                } else if (e.message.image) {
                                    lastMessage.innerText = e.message.sender_name + ': 📷 Imagem';
                                } else if (e.message.audio) {
                                    lastMessage.innerText = e.message.sender_name + ': 🎤 Áudio';
                                }
                            }
                        });
                @endforeach
            }

            startGroupUnreadEcho();
        });
    </script>
</x-app-layout>