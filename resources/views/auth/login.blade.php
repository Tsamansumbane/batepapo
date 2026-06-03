<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-white px-6">

        <div class="w-full max-w-sm">

            <div class="flex justify-center mb-8">
                <img 
                    src="{{ asset('images/batepapo-logo.png') }}" 
                    alt="Batepapo"
                    class="w-40"
                >
            </div>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <x-input-label for="email" :value="__('Email')" />

                    <x-text-input 
                        id="email"
                        class="block mt-1 w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                        type="email"
                        name="email"
                        :value="old('email')"
                        required
                        autofocus
                        autocomplete="username"
                    />

                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password" :value="__('Password')" />

                    <x-text-input 
                        id="password"
                        class="block mt-1 w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                    />

                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between">
                    <label for="remember_me" class="inline-flex items-center">
                        <input 
                            id="remember_me"
                            type="checkbox"
                            name="remember"
                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                        >

                        <span class="ms-2 text-sm text-gray-600">
                            {{ __('Remember me') }}
                        </span>
                    </label>

                    @if (Route::has('password.request'))
                        <a 
                            href="{{ route('password.request') }}"
                            class="text-sm text-purple-600 hover:text-purple-700"
                        >
                            {{ __('Forgot your password?') }}
                        </a>
                    @endif
                </div>

                <button 
                    type="submit"
                    class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-3 rounded-lg transition"
                >
                    {{ __('Log in') }}
                </button>
            </form>

        </div>

    </div>
</x-guest-layout>