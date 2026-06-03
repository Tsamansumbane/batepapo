<x-app-layout>
    <div class="min-h-[calc(100vh-65px)] bg-slate-100 px-4 py-8">

        <div class="max-w-3xl mx-auto">

            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-emerald-600">
                        Adicionar amigo
                    </h1>
                    <p class="text-sm text-slate-400">
                        Procura utilizadores por nome ou email
                    </p>
                </div>

                <a href="{{ route('friends.index') }}"
                   class="px-4 py-2 rounded-full bg-white border border-slate-200 text-slate-600 font-semibold hover:bg-slate-50 no-underline">
                    Voltar
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

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden mb-5">
                <div class="p-5 border-b border-slate-100">
                    <form method="GET" action="{{ route('friends.search') }}">
                        <div class="flex gap-3">
                            <div class="relative flex-1">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                    🔍
                                </span>

                                <input
                                    type="text"
                                    name="q"
                                    class="w-full rounded-full border-0 bg-slate-100 pl-11 pr-4 py-3 text-sm outline-none focus:ring-2 focus:ring-emerald-400"
                                    placeholder="Pesquisar por nome ou email"
                                    value="{{ request('q') }}"
                                >
                            </div>

                            <button class="px-5 py-3 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold shadow-sm">
                                Pesquisar
                            </button>
                        </div>
                    </form>
                </div>

                <div class="px-5 py-3 bg-slate-50 border-b border-slate-100">
                    <span class="text-xs font-bold uppercase tracking-wide text-slate-400">
                        Resultados
                    </span>
                </div>

                <div>
                    @forelse ($users as $user)
                        <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-slate-100 hover:bg-slate-50 transition">

                            <div class="flex items-center gap-3 min-w-0">
                                @if ($user->profile_photo)
                                    <img src="{{ asset('storage/' . $user->profile_photo) }}"
                                         class="w-12 h-12 rounded-full object-cover shrink-0">
                                @else
                                    <div class="w-12 h-12 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif

                                <div class="min-w-0">
                                    <div class="font-semibold text-slate-800 truncate">
                                        {{ $user->name }}
                                    </div>

                                    <div class="text-sm text-slate-500 truncate">
                                        {{ $user->email }}
                                    </div>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('friends.request', $user) }}">
                                @csrf

                                <button class="px-4 py-2 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold">
                                    Adicionar
                                </button>
                            </form>

                        </div>
                    @empty
                        <div class="text-center text-slate-400 py-12">
                            Nenhum utilizador encontrado.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
</x-app-layout>