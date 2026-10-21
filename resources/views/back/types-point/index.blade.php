@extends('back.layouts.back')

@section('title', 'Types de points')

@section('content')
    <x-page-header title="Types de points" :subtitle="'Liste des types (' . $typesPoint->total() . ')'">
        @slot('actions')
            <a href="{{ route('admin.types-point.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Nouveau type
            </a>
        @endslot
    </x-page-header>

    {{-- Recherche --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.types-point.index') }}" class="d-flex flex-wrap gap-2 align-items-end" novalidate>
                <div class="app-search-bar">
                    <label class="form-label" for="search">Rechercher</label>
                    <input
                        type="text"
                        class="form-control"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Nom ou description..."
                    >
                </div>
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bx bx-search me-1"></i> Rechercher
                </button>
                @if ($search)
                    <a href="{{ route('admin.types-point.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Liste des types de points --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Description</th>
                            <th>Icône</th>
                            <th>Points</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($typesPoint as $typePoint)
                            <tr>
                                <td>{{ $typePoint->id }}</td>
                                <td>
                                    <a href="{{ route('admin.types-point.show', $typePoint) }}" class="fw-semibold">
                                        {{ $typePoint->nom }}
                                    </a>
                                </td>
                                <td>{{ \Illuminate\Support\Str::limit($typePoint->description, 60) }}</td>
                                <td><code>{{ $typePoint->icone }}</code></td>
                                <td>
                                    <span class="badge app-badge-user">{{ $typePoint->points_count }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('admin.types-point.show', $typePoint) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Voir">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('admin.types-point.edit', $typePoint) }}"
                                           class="btn btn-sm btn-outline-primary" title="Modifier">
                                            <i class="bx bx-edit"></i>
                                        </a>

                                        {{-- La suppression est refusée par le contrôleur
                                             si le type a encore des points. --}}
                                        <form method="POST"
                                              action="{{ route('admin.types-point.destroy', $typePoint) }}"
                                              onsubmit="return confirm('Confirmer la suppression de ce type de point ?');" novalidate>
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Aucun type de point ne correspond à votre recherche.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <x-pagination :paginator="$typesPoint" variant="bootstrap-5" />
@endsection
