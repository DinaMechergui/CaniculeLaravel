@extends('back.layouts.back')

@section('title', 'Conseils')

@section('content')
    <x-page-header title="Conseils" :subtitle="'Liste des conseils (' . $conseils->total() . ')'">
        @slot('actions')
            <a href="{{ route('admin.conseils.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Nouveau conseil
            </a>
        @endslot
    </x-page-header>

    {{-- Recherche --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.conseils.index') }}" class="d-flex flex-wrap gap-2 align-items-end">
                <div class="app-search-bar">
                    <label class="form-label" for="search">Rechercher</label>
                    <input type="text" class="form-control" id="search" name="search"
                           value="{{ $search }}" placeholder="Titre ou contenu...">
                </div>
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bx bx-search me-1"></i> Rechercher
                </button>
                @if ($search)
                    <a href="{{ route('admin.conseils.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Titre</th>
                            <th>Catégorie</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($conseils as $conseil)
                            <tr>
                                <td>{{ $conseil->id }}</td>
                                <td>
                                    <a href="{{ route('admin.conseils.show', $conseil) }}" class="fw-semibold">
                                        {{ $conseil->titre }}
                                    </a>
                                </td>
                                <td><span class="badge app-badge-user">{{ $conseil->categorie->nom }}</span></td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('admin.conseils.show', $conseil) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Voir">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('admin.conseils.edit', $conseil) }}"
                                           class="btn btn-sm btn-outline-primary" title="Modifier">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('admin.conseils.destroy', $conseil) }}"
                                              onsubmit="return confirm('Confirmer la suppression de ce conseil ?');">
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
                                <td colspan="4" class="text-center text-muted py-4">Aucun conseil trouvé.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-pagination :paginator="$conseils" variant="bootstrap-5" />
@endsection
