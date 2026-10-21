@extends('back.layouts.back')

@section('title', 'Points de fraîcheur')

@section('content')
    <x-page-header title="Points de fraîcheur" :subtitle="'Liste des points (' . $points->total() . ')'">
        @slot('actions')
            <a href="{{ route('admin.points-fraicheur.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Nouveau point
            </a>
        @endslot
    </x-page-header>

    {{-- Recherche --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.points-fraicheur.index') }}" class="d-flex flex-wrap gap-2 align-items-end" novalidate>
                <div class="app-search-bar">
                    <label class="form-label" for="search">Rechercher</label>
                    <input
                        type="text"
                        class="form-control"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Nom ou adresse..."
                    >
                </div>
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bx bx-search me-1"></i> Rechercher
                </button>
                @if ($search)
                    <a href="{{ route('admin.points-fraicheur.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Liste des points de fraîcheur --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Adresse</th>
                            <th>Accessible</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($points as $point)
                            <tr>
                                <td>{{ $point->id }}</td>
                                <td>
                                    <a href="{{ route('admin.points-fraicheur.show', $point) }}" class="fw-semibold">
                                        {{ $point->nom }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge app-badge-user">
                                        {{ $point->type?->nom ?? '—' }}
                                    </span>
                                </td>
                                <td>{{ \Illuminate\Support\Str::limit($point->adresse, 50) }}</td>
                                <td>
                                    <span class="badge {{ $point->accessible ? 'app-badge-admin' : 'app-badge-user' }}">
                                        {{ $point->accessible ? 'Oui' : 'Non' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('admin.points-fraicheur.show', $point) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Voir">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('admin.points-fraicheur.edit', $point) }}"
                                           class="btn btn-sm btn-outline-primary" title="Modifier">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('admin.points-fraicheur.destroy', $point) }}"
                                              onsubmit="return confirm('Confirmer la suppression de ce point de fraîcheur ?');" novalidate>
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
                                    Aucun point de fraîcheur ne correspond à votre recherche.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <x-pagination :paginator="$points" variant="bootstrap-5" />
@endsection
