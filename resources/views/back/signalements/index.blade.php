@extends('back.layouts.back')

@section('title', 'Signalements')

@section('content')
    <x-page-header title="Signalements" :subtitle="'Liste des signalements (' . $signalements->total() . ')'">
        @slot('actions')
            <a href="{{ route('admin.signalements.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Nouveau signalement
            </a>
        @endslot
    </x-page-header>

    {{-- Filtres --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.signalements.index') }}" class="d-flex flex-wrap gap-2 align-items-end" novalidate>
                <div class="app-search-bar">
                    <label class="form-label" for="search">Rechercher</label>
                    <input
                        type="text"
                        class="form-control"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Description..."
                    >
                </div>
                <div>
                    <label class="form-label" for="statut">Statut</label>
                    <select class="form-select" id="statut" name="statut">
                        <option value="">Tous les statuts</option>
                        @foreach ($statuts as $key => $label)
                            <option value="{{ $key }}" @selected($statut === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bx bx-search me-1"></i> Filtrer
                </button>
                @if ($search || $statut)
                    <a href="{{ route('admin.signalements.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Liste des signalements --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Type</th>
                            <th>Auteur</th>
                            <th>Description</th>
                            <th>Priorité IA</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($signalements as $signalement)
                            <tr>
                                <td>
                                    <span class="badge bg-label-primary">
                                        {{ $signalement->typeSignalement?->libelle ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>{{ $signalement->user?->name ?? 'N/A' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($signalement->description, 60) }}</td>
                                <td>
                                    @php
                                        $prioClass = match($signalement->priorite) {
                                            'faible'  => 'badge bg-secondary',
                                            'moyenne' => 'badge bg-warning text-dark',
                                            'urgente' => 'badge bg-danger',
                                            default   => 'badge bg-secondary',
                                        };
                                        $priorites = \App\Models\Signalement::PRIORITES;
                                    @endphp
                                    <span class="{{ $prioClass }}">
                                        {{ strtoupper($priorites[$signalement->priorite] ?? $signalement->priorite) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $badgeClass = match($signalement->statut) {
                                            'nouveau'  => 'badge bg-info',
                                            'en_cours' => 'badge bg-warning text-dark',
                                            'traite'   => 'badge bg-success',
                                            default    => 'badge bg-secondary',
                                        };
                                    @endphp
                                    <span class="{{ $badgeClass }}">
                                        {{ $statuts[$signalement->statut] ?? $signalement->statut }}
                                    </span>
                                </td>
                                <td>{{ $signalement->created_at->format('d/m/Y') }}</td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('admin.signalements.show', $signalement) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Voir">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('admin.signalements.edit', $signalement) }}"
                                           class="btn btn-sm btn-outline-primary" title="Modifier">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('admin.signalements.destroy', $signalement) }}"
                                              onsubmit="return confirm('Confirmer la suppression de ce signalement ?');" novalidate>
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
                                <td colspan="7" class="text-center text-muted py-4">
                                    Aucun signalement ne correspond à vos critères.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <x-pagination :paginator="$signalements" variant="bootstrap-5" />
@endsection
