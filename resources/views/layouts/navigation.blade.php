<nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
    <div class="px-3 sm:px-5 h-16 flex items-center justify-between gap-2">

        <div class="flex items-center gap-2 sm:gap-4 min-w-0">

            {{-- LOGO --}}
            <a href="{{ route('dashboard') }}" class="flex items-center shrink-0">
                <img
                    src="{{ asset('images/batepapo-logo-chat.png') }}"
                    alt="Batepapo"
                    class="h-20 sm:h-15 w-auto"
                >
            </a>

            {{-- ICONS DO MENU --}}
            <div class="flex items-center gap-1 sm:gap-2 shrink-0">

                {{-- CHATS --}}
                <a href="{{ route('dashboard') }}"
                   title="Chats"
                   class="w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center no-underline transition
                   {{ request()->routeIs('dashboard') ? 'text-emerald-600 bg-emerald-50' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-100' }}">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="none">
                        <path d="M7.5 9.5H16.5M7.5 13H13.5M21 11.5C21 15.6421 16.9706 19 12 19C10.9768 19 9.99342 18.8578 9.07634 18.5953L4 21L5.32782 16.789C3.88197 15.4016 3 13.5558 3 11.5C3 7.35786 7.02944 4 12 4C16.9706 4 21 7.35786 21 11.5Z"
                              stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>

                {{-- GRUPOS --}}
                <a href="{{ route('groups.index') }}"
                   title="Grupos"
                   class="w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center no-underline transition
                   {{ request()->routeIs('groups.*') ? 'text-emerald-600 bg-emerald-50' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-100' }}">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="none">
                        <path d="M16 11C17.6569 11 19 9.65685 19 8C19 6.34315 17.6569 5 16 5M8 11C9.65685 11 11 9.65685 11 8C11 6.34315 9.65685 5 8 5C6.34315 5 5 6.34315 5 8C5 9.65685 6.34315 11 8 11ZM8 14C5.23858 14 3 15.7909 3 18V19H13V18C13 15.7909 10.7614 14 8 14ZM16 14C15.392 14 14.8106 14.0827 14.2742 14.2334C15.3293 15.1226 16 16.4382 16 18V19H21V18C21 15.7909 18.7614 14 16 14Z"
                              stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>

                {{-- AMIGOS --}}
                <a href="{{ route('friends.index') }}"
                   title="Amigos"
                   class="w-9 h-9 sm:w-10 sm:h-10 rounded-full flex items-center justify-center no-underline transition
                   {{ request()->routeIs('friends.*') ? 'text-emerald-600 bg-emerald-50' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-100' }}">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" viewBox="0 0 24 24" fill="none">
                        <path d="M12 12C14.2091 12 16 10.2091 16 8C16 5.79086 14.2091 4 12 4C9.79086 4 8 5.79086 8 8C8 10.2091 9.79086 12 12 12ZM4 20C4 16.6863 7.58172 14 12 14C16.4183 14 20 16.6863 20 20"
                              stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>

            </div>

        </div>

        {{-- PERFIL --}}
        <x-dropdown align="right" width="48">

            <x-slot name="trigger">
                <button type="button"
                    class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center overflow-hidden shadow-sm transition">

                    @if(Auth::user()->profile_photo)
                        <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}"
                             class="w-full h-full object-cover">
                    @else
                        <span class="font-bold text-slate-600">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </span>
                    @endif

                </button>
            </x-slot>

            <x-slot name="content">

                <div class="px-4 py-3 border-b border-slate-100">
                    <div class="font-semibold text-slate-800">
                        {{ Auth::user()->name }}
                    </div>

                    <div class="text-xs text-slate-500 truncate">
                        {{ Auth::user()->email }}
                    </div>
                </div>

                <x-dropdown-link :href="route('profile.edit')">
                    ⚙️ Perfil
                </x-dropdown-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-dropdown-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        ⎋ Sair
                    </x-dropdown-link>
                </form>

            </x-slot>

        </x-dropdown>

    </div>
</nav>