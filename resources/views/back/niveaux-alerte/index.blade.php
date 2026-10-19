@extends('back.layouts.back')

@section('title', 'Niveaux d\'alerte')

@section('content')
    <x-page-header title="Niveaux d'alerte météo" :subtitle="'Gestion des niveaux d\'alerte (' . $niveauxAlerte->total() . ')'">
        @slot('actions')
            <a href="{{ route('admin.niveaux-alerte.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Nouveau niveau
            </a>
        @endslot
    </x-page-header>

    {{-- Formulaire de recherche --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.niveaux-alerte.index') }}" class="d-flex flex-wrap gap-2 align-items-end">
                <div class="app-search-bar">
                    <label class="form-label" for="search">Rechercher</label>
                    <input
                        type="text"
                        class="form-control"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Libellé ou couleur..."
                    >
                </div>
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bx bx-search me-1"></i> Rechercher
                </button>
                @if ($search)
                    <a href="{{ route('admin.niveaux-alerte.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Tableau des niveaux d'alerte --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Niveau</th>
                            <th>Libellé</th>
                            <th>Couleur</th>
                            <th>Alertes associées</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($niveauxAlerte as $niveauAlerte)
                            <tr>
                                <td><span class="badge bg-label-dark fs-6">Niveau {{ $niveauAlerte->niveau }}</span></td>
                                <td>
                                    <a href="{{ route('admin.niveaux-alerte.show', $niveauAlerte) }}" class="fw-semibold text-dark">
                                        {{ $niveauAlerte->libelle }}
                                    </a>
                                </td>
                                <td>
                                    @switch($niveauAlerte->couleur)
                                        @case('vert')
                                            <span class="badge bg-success">Vert</span>
                                            @break
                                        @case('jaune')
                                            <span class="badge bg-warning text-dark">Jaune</span>
                                            @break
                                        @case('orange')
                                            <span class="badge bg-orange text-white" style="background-color: #fd7e14 !important;">Orange</span>
                                            @break
                                        @case('rouge')
                                            <span class="badge bg-danger">Rouge</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $niveauAlerte->couleur }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <span class="badge bg-label-primary fs-6">{{ $niveauAlerte->alertes_count }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('admin.niveaux-alerte.show', $niveauAlerte) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Voir">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('admin.niveaux-alerte.edit', $niveauAlerte) }}"
                                           class="btn btn-sm btn-outline-primary" title="Modifier">
                                            <i class="bx bx-edit"></i>
                                        </a>

                                        {{-- Confirmation de suppression (gérée par le contrôleur) --}}
                                        <form method="POST"
                                              action="{{ route('admin.niveaux-alerte.destroy', $niveauAlerte) }}"
                                              onsubmit="return confirm('Confirmer la suppression de ce niveau d\'alerte ?');">
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
                                <td colspan="5" class="text-center text-muted py-4">
                                    Aucun niveau d'alerte trouvé.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <x-pagination :paginator="$niveauxAlerte" variant="bootstrap-5" />
@endsection
