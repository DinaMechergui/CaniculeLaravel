@extends('back.layouts.back')

@section('title', 'Types de signalements')

@section('content')
    <x-page-header title="Types de signalements" :subtitle="'Liste des types (' . $typesSignalement->total() . ')'">
        @slot('actions')
            <a href="{{ route('admin.types-signalement.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Nouveau type
            </a>
        @endslot
    </x-page-header>

    {{-- Recherche --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.types-signalement.index') }}" class="d-flex flex-wrap gap-2 align-items-end">
                <div class="app-search-bar">
                    <label class="form-label" for="search">Rechercher</label>
                    <input
                        type="text"
                        class="form-control"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Libellé..."
                    >
                </div>
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bx bx-search me-1"></i> Rechercher
                </button>
                @if ($search)
                    <a href="{{ route('admin.types-signalement.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Liste des types de signalements --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Libellé</th>
                            <th>Signalements</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($typesSignalement as $typeSignalement)
                            <tr>
                                <td>{{ $typeSignalement->id }}</td>
                                <td>
                                    <a href="{{ route('admin.types-signalement.show', $typeSignalement) }}" class="fw-semibold">
                                        {{ $typeSignalement->libelle }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge app-badge-user">{{ $typeSignalement->signalements_count }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('admin.types-signalement.show', $typeSignalement) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Voir">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('admin.types-signalement.edit', $typeSignalement) }}"
                                           class="btn btn-sm btn-outline-primary" title="Modifier">
                                            <i class="bx bx-edit"></i>
                                        </a>

                                        {{-- La suppression est refusée par le contrôleur
                                             si le type a encore des signalements. --}}
                                        <form method="POST"
                                              action="{{ route('admin.types-signalement.destroy', $typeSignalement) }}"
                                              onsubmit="return confirm('Confirmer la suppression de ce type de signalement ?');">
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
                                <td colspan="4" class="text-center text-muted py-4">
                                    Aucun type de signalement ne correspond à votre recherche.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <x-pagination :paginator="$typesSignalement" variant="bootstrap-5" />
@endsection
