<x-app-layout>
    <div class="container-fluid p-0">

        <div class="d-flex" style="height: calc(100vh - 64px);">

            <div style="width: 90px; background:#f0f2f5; border-right:1px solid #ddd;"
                 class="d-flex flex-column align-items-center py-3 gap-4">

                <a href="{{ route('dashboard') }}" class="btn btn-light rounded-circle">
                    💬
                </a>

                <a href="{{ route('contacts.index') }}" class="btn btn-success rounded-circle">
                    👤
                </a>

                <a href="{{ route('groups.index') }}" class="btn btn-light rounded-circle">
                    👥
                </a>

            </div>

            <div style="width: 430px; background:white; border-right:1px solid #ddd; overflow-y:auto;">

                <div class="p-4 d-flex align-items-center gap-3">
                    <a href="{{ route('dashboard') }}" class="btn btn-light rounded-circle">
                        ←
                    </a>

                    <h4 class="mb-0">
                        Nova conversa
                    </h4>
                </div>

                <div class="px-4 pb-3">
                    <input
                        type="text"
                        id="contactSearch"
                        class="form-control rounded-pill"
                        placeholder="Pesquisar nome ou email"
                    >
                </div>

                <div class="px-4 pb-3">
                    <a href="{{ route('friends.search') }}" class="text-decoration-none text-dark">
                        <div class="d-flex align-items-center gap-3 py-3">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                                 style="width:50px;height:50px;">
                                +
                            </div>

                            <strong>
                                Novo contacto
                            </strong>
                        </div>
                    </a>

                    <a href="{{ route('groups.create') }}" class="text-decoration-none text-dark">
                        <div class="d-flex align-items-center gap-3 py-3">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                                 style="width:50px;height:50px;">
                                👥
                            </div>

                            <strong>
                                Novo grupo
                            </strong>
                        </div>
                    </a>
                </div>

                <div class="px-4 text-muted small mb-2">
                    Contactos
                </div>

                <div id="contactsList">

                    @forelse ($friends as $friend)

                        <a href="{{ route('chat.show', $friend) }}"
                           class="contact-item text-decoration-none text-dark d-flex align-items-center gap-3 px-4 py-3 border-bottom"
                           data-name="{{ strtolower($friend->name) }}"
                           data-email="{{ strtolower($friend->email) }}">

                            @if ($friend->profile_photo)
                                <img
                                    src="{{ asset('storage/' . $friend->profile_photo) }}"
                                    width="52"
                                    height="52"
                                    class="rounded-circle object-fit-cover"
                                >
                            @else
                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center"
                                     style="width:52px;height:52px;">
                                    {{ strtoupper(substr($friend->name, 0, 1)) }}
                                </div>
                            @endif

                            <div>
                                <strong>
                                    {{ $friend->name }}
                                </strong>

                                @if ($friend->is_online)
                                    <span class="badge bg-success ms-1">
                                        Online
                                    </span>
                                @else
                                    <span class="badge bg-secondary ms-1">
                                        Offline
                                    </span>
                                @endif

                                <br>

                                <small class="text-muted">
                                    {{ $friend->email }}
                                </small>
                            </div>

                        </a>

                    @empty

                        <div class="text-center text-muted py-5">
                            Ainda não tens contactos.
                        </div>

                    @endforelse

                </div>

            </div>

            <div class="flex-fill d-flex align-items-center justify-content-center"
                 style="background:#efeae2;">

                <div class="text-center text-muted">
                    <h3>Batepapo Web</h3>
                    <p>Seleciona um contacto para começar a conversa.</p>
                </div>

            </div>

        </div>

    </div>

    <script>
        document.getElementById('contactSearch').addEventListener('input', function () {
            const search = this.value.toLowerCase();

            document.querySelectorAll('.contact-item').forEach(item => {
                const name = item.dataset.name;
                const email = item.dataset.email;

                item.style.display = name.includes(search) || email.includes(search)
                    ? 'flex'
                    : 'none';
            });
        });
    </script>
</x-app-layout>