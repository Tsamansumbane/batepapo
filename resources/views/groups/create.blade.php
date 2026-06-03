<x-app-layout>
    <div class="container py-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Criar grupo</h3>

            <a href="{{ route('groups.index') }}" class="btn btn-outline-secondary">
                Voltar
            </a>
        </div>

        <div class="card">
            <div class="card-body">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('groups.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Nome do grupo</label>
                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="{{ old('name') }}"
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Foto do grupo opcional</label>
                        <input
                            type="file"
                            name="photo"
                            class="form-control"
                            accept="image/*"
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Adicionar amigos</label>

                        @forelse ($friends as $friend)
                            <div class="form-check border rounded p-3 mb-2">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="members[]"
                                    value="{{ $friend->id }}"
                                    id="friend{{ $friend->id }}"
                                >

                                <label class="form-check-label" for="friend{{ $friend->id }}">
                                    <strong>{{ $friend->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $friend->email }}</small>
                                </label>
                            </div>
                        @empty
                            <p class="text-muted">
                                Ainda não tens amigos para adicionar ao grupo.
                            </p>
                        @endforelse
                    </div>

                    <button class="btn btn-primary">
                        Criar grupo
                    </button>

                </form>

            </div>
        </div>

    </div>
</x-app-layout>