<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Batepapo') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">

    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"
        rel="stylesheet"
    />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">

    <div class="min-h-screen bg-gray-100">

        @include('layouts.navigation')

        @isset($header)
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main>
            {{ $slot }}
        </main>

    </div>

    @auth
        <div
            id="customMessageToast"
            style="
                display:none;
                position:fixed;
                right:20px;
                bottom:20px;
                width:320px;
                z-index:99999;
                background:#ffffff;
                border-radius:10px;
                box-shadow:0 10px 30px rgba(0,0,0,0.2);
                overflow:hidden;
                border:1px solid #e5e5e5;
            "
        >
            <div
                style="
                    padding:10px 14px;
                    border-bottom:1px solid #eee;
                    font-weight:bold;
                    display:flex;
                    justify-content:space-between;
                    align-items:center;
                "
            >
                <span id="customToastTitle">
                    Nova mensagem
                </span>

                <button
                    type="button"
                    id="customToastClose"
                    style="
                        border:none;
                        background:transparent;
                        font-size:18px;
                        cursor:pointer;
                    "
                >
                    ×
                </button>
            </div>

            <div
                id="customToastBody"
                style="
                    padding:12px 14px;
                    color:#555;
                "
            >
                Tens uma nova mensagem.
            </div>
        </div>

        <audio
            id="messageSound"
            src="{{ asset('sounds/message.wav') }}"
            preload="auto">
        </audio>

        <script>
            document.addEventListener('DOMContentLoaded', function () {

                const authId = "{{ auth()->id() }}";

                if (window.startOnlinePresence) {
                    window.startOnlinePresence();
                }

                const toastBox = document.getElementById('customMessageToast');
                const toastTitle = document.getElementById('customToastTitle');
                const toastBody = document.getElementById('customToastBody');
                const toastClose = document.getElementById('customToastClose');
                const messageSound = document.getElementById('messageSound');

                let toastTimer = null;
                let soundUnlocked = false;

                function unlockSound() {
                    if (soundUnlocked || !messageSound) {
                        return;
                    }

                    messageSound.volume = 0;
                    messageSound.play()
                        .then(function () {
                            messageSound.pause();
                            messageSound.currentTime = 0;
                            messageSound.volume = 1;
                            soundUnlocked = true;
                        })
                        .catch(function () {
                            messageSound.volume = 1;
                        });
                }

                document.addEventListener('click', unlockSound, { once: true });
                document.addEventListener('keydown', unlockSound, { once: true });

                function playSound() {
                    if (!messageSound) {
                        return;
                    }

                    const sound = messageSound.cloneNode();
                    sound.volume = 1;

                    sound.play().catch(function (error) {
                        console.log('Erro ao tocar som:', error);
                    });
                }

                function hideNotification() {
                    toastBox.style.display = 'none';

                    if (toastTimer) {
                        clearTimeout(toastTimer);
                        toastTimer = null;
                    }
                }

                function showNotification(title, body) {
                    toastTitle.innerText = title;
                    toastBody.innerText = body;

                    toastBox.style.display = 'block';

                    playSound();

                    if (toastTimer) {
                        clearTimeout(toastTimer);
                    }

                    toastTimer = setTimeout(function () {
                        hideNotification();
                    }, 15000);
                }

                toastClose.addEventListener('click', function () {
                    hideNotification();
                });

                function startGlobalNotifications() {
                    if (!window.Echo) {
                        setTimeout(startGlobalNotifications, 500);
                        return;
                    }

                    window.Echo.private(`chat.${authId}`)
                        .listen('.message.sent', function (e) {
                            if (e.message.sender_id == authId) {
                                return;
                            }

                            const text = e.message.body
                                ? e.message.body
                                : '📷 Imagem';

                            showNotification(
                                'Nova mensagem privada',
                                text
                            );
                        });
                }

                startGlobalNotifications();

            });
        </script>
    @endauth

</body>

</html>