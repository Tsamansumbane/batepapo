<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Completar Conta - Batepapo</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100">

    <div class="min-h-screen flex items-center justify-center px-4 py-10">

        <div class="w-full max-w-lg">

            <div class="text-center mb-6">

                <div class="w-20 h-20 mx-auto rounded-full bg-emerald-100 text-4xl flex items-center justify-center mb-4">
                    👤
                </div>

                <h1 class="text-3xl font-bold text-emerald-600">
                    Completar Conta
                </h1>

                <p class="text-sm text-slate-400 mt-1">
                    Define os teus dados finais para começares a utilizar o Batepapo
                </p>

            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">

                <div class="px-6 py-5 border-b border-slate-100">
                    <h2 class="text-xl font-bold text-slate-800">
                        Dados da Conta
                    </h2>

                    <p class="text-sm text-slate-400 mt-1">
                        Completa as informações abaixo
                    </p>
                </div>

                <div class="p-6">

                    @if ($errors->any())
                        <div class="mb-5 rounded-2xl bg-red-50 border border-red-100 px-5 py-4 text-red-600 text-sm">
                            <ul class="space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST"
                          action="{{ route('register.complete') }}"
                          enctype="multipart/form-data"
                          class="space-y-5">

                        @csrf

                        {{-- FOTO --}}
                        <div class="flex flex-col items-center">

                            <div id="photoPreview"
                                 class="w-28 h-28 rounded-full bg-slate-200 flex items-center justify-center overflow-hidden text-4xl text-slate-500 mb-3">
                                👤
                            </div>

                            <label for="profile_photo"
                                   class="px-4 py-2 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold cursor-pointer transition">
                                Escolher foto
                            </label>

                            <input type="file"
                                   id="profile_photo"
                                   name="profile_photo"
                                   accept="image/*"
                                   class="hidden">
                        </div>

                        {{-- DATA NASCIMENTO --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-slate-400 mb-2">
                                Data de nascimento
                            </label>

                            <input
                                type="date"
                                name="birth_date"
                                value="{{ old('birth_date') }}"
                                class="w-full rounded-full border-0 bg-slate-100 px-5 py-3 text-sm outline-none focus:ring-2 focus:ring-emerald-400"
                                required
                            >
                        </div>

                        {{-- GENERO --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-slate-400 mb-2">
                                Género
                            </label>

                            <select
                                name="gender"
                                class="w-full rounded-full border-0 bg-slate-100 px-5 py-3 text-sm outline-none focus:ring-2 focus:ring-emerald-400"
                                required>

                                <option value="">
                                    Selecionar
                                </option>

                                <option value="Masculino" {{ old('gender') == 'Masculino' ? 'selected' : '' }}>
                                    Masculino
                                </option>

                                <option value="Feminino" {{ old('gender') == 'Feminino' ? 'selected' : '' }}>
                                    Feminino
                                </option>

                                <option value="Outro" {{ old('gender') == 'Outro' ? 'selected' : '' }}>
                                    Outro
                                </option>

                            </select>
                        </div>

                        {{-- PASSWORD --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-slate-400 mb-2">
                                Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                class="w-full rounded-full border-0 bg-slate-100 px-5 py-3 text-sm outline-none focus:ring-2 focus:ring-emerald-400"
                                required
                            >
                        </div>

                        {{-- CONFIRMAR PASSWORD --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wide text-slate-400 mb-2">
                                Confirmar password
                            </label>

                            <input
                                type="password"
                                name="password_confirmation"
                                class="w-full rounded-full border-0 bg-slate-100 px-5 py-3 text-sm outline-none focus:ring-2 focus:ring-emerald-400"
                                required
                            >
                        </div>

                        <button
                            class="w-full rounded-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-3 shadow-sm transition">
                            Criar Conta
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

    <script>
        const profilePhotoInput = document.getElementById('profile_photo');
        const photoPreview = document.getElementById('photoPreview');

        profilePhotoInput.addEventListener('change', function () {

            const file = this.files[0];

            if (!file) {
                return;
            }

            const reader = new FileReader();

            reader.onload = function (e) {

                photoPreview.innerHTML = `
                    <img
                        src="${e.target.result}"
                        class="w-full h-full object-cover"
                    >
                `;

            };

            reader.readAsDataURL(file);
        });
    </script>

</body>
</html>