<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Batepapo - Confirmar OTP</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100">

    <div class="min-h-screen flex items-center justify-center px-4 py-10">

        <div class="w-full max-w-md">

            <div class="text-center mb-6">
                <div class="w-20 h-20 mx-auto rounded-full bg-emerald-100 text-4xl flex items-center justify-center mb-4">
                    🔐
                </div>

                <h1 class="text-3xl font-bold text-emerald-600">
                    Confirmar Código
                </h1>

                <p class="text-sm text-slate-400 mt-1">
                    Introduz o código OTP enviado para o teu email
                </p>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">

                <div class="px-6 py-5 border-b border-slate-100">
                    <h2 class="text-xl font-bold text-slate-800">
                        Verificação OTP
                    </h2>

                    <p class="text-sm text-slate-400 mt-1">
                        Confirma o código para finalizar a criação da conta
                    </p>
                </div>

                <div class="p-6">

                    @if ($errors->any())
                        <div class="mb-4 rounded-2xl bg-red-50 border border-red-100 px-5 py-4 text-red-600 text-sm">
                            <ul class="mb-0 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>
                                        {{ $error }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="mb-4 rounded-2xl bg-emerald-50 border border-emerald-100 px-5 py-4 text-emerald-700 text-sm font-semibold">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register.verifyOtp') }}" class="space-y-4">

                        @csrf

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-slate-400 mb-2">
                                Código OTP
                            </label>

                            <input
                                type="text"
                                name="code"
                                maxlength="6"
                                inputmode="numeric"
                                class="w-full rounded-full border-0 bg-slate-100 px-5 py-3 text-center text-lg tracking-[0.35em] font-bold text-slate-700 outline-none focus:ring-2 focus:ring-emerald-400"
                                placeholder="000000"
                                required
                            >
                        </div>

                        <button class="w-full rounded-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-3 shadow-sm transition">
                            Verificar
                        </button>

                    </form>

                    <div class="text-center mt-5">
                        <a href="/register"
                           class="text-sm font-semibold text-emerald-600 hover:text-emerald-700 no-underline">
                            Voltar para criar conta
                        </a>
                    </div>

                </div>

            </div>

        </div>

    </div>

</body>
</html>