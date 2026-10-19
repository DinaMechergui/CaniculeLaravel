@extends('back.layouts.back')

@section('title', 'Mesures de prévention')

@section('content')
    <x-page-header title="Mesures de prévention" :subtitle="'Gestion des mesures d\'urgence (' . $mesuresPrevention->total() . ')'">
        @slot('actions')
            <a href="{{ route('admin.mesures-prevention.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Nouvelle mesure
            </a>
        @endslot
    </x-page-header>

    {{-- Recherche & Filtres par type, statut et recherche --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.mesures-prevention.index') }}" class="d-flex flex-wrap gap-2 align-items-end">
                <div class="app-search-bar" style="min-width: 250px;">
                    <label class="form-label" for="search">Rechercher</label>
                    <input
                        type="text"
                        class="form-control"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Titre, responsable, description..."
                    >
                </div>

                <div>
                    <label class="form-label" for="type">Type de mesure</label>
                    <select name="type" id="type" class="form-select">
                        <option value="">Tous les types</option>
                        @foreach ($types as $key => $label)
                            <option value="{{ $key }}" {{ $type === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label" for="statut">Statut</label>
                    <select name="statut" id="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        @foreach ($statuts as $key => $label)
                            <option value="{{ $key }}" {{ $statut === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-outline-primary">
                    <i class="bx bx-search me-1"></i> Filtrer
                </button>

                @if ($search || $type || $statut || $alerteId)
                    <a href="{{ route('admin.mesures-prevention.index') }}" class="btn btn-outline-secondary">
                        Réinitialiser
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Tableau des mesures de prévention --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Titre de la mesure</th>
                            <th>Alerte météo liée & Niveau</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th>Responsable</th>
                            <th>Date de début</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mesuresPrevention as $mesure)
                            <tr>
                                <td>{{ $mesure->id }}</td>
                                <td>
                                    <a href="{{ route('admin.mesures-prevention.show', $mesure) }}" class="fw-semibold text-dark">
                                        {{ $mesure->titre }}
                                    </a>
                                </td>
                                <td>
                                    @if ($mesure->alerteMeteo)
                                        <div class="d-flex align-items-center gap-1">
                                            @php
                                                $couleur = $mesure->alerteMeteo->niveauAlerte->couleur ?? 'gris';
                                                $badgeStyle = ($couleur === 'orange') ? 'background-color: #fd7e14 !important;' : '';
                                                $badgeClass = match ($couleur) {
                                                    'vert' => 'bg-success',
                                                    'jaune' => 'bg-warning text-dark',
                                                    'orange' => 'text-white',
                                                    'rouge' => 'bg-danger',
                                                    default => 'bg-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }}" style="{{ $badgeStyle }}" title="Niveau {{ $couleur }}">
                                                {{ strtoupper($couleur) }}
                                            </span>
                                            <a href="{{ route('admin.alertes-meteo.show', $mesure->alerteMeteo) }}" class="text-truncate d-inline-block" style="max-width: 220px;" title="{{ $mesure->alerteMeteo->titre }}">
                                                {{ $mesure->alerteMeteo->titre }}
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted fst-italic">Non rattachée</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $mesure->type_badge_class }}">
                                        {{ $mesure->type_label }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $mesure->statut_badge_class }}">
                                        {{ $mesure->statut_label }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $mesure->responsable }}</span>
                                </td>
                                <td>
                                    <span class="small">{{ $mesure->date_debut->format('d/m/Y H:i') }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('admin.mesures-prevention.show', $mesure) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Voir les détails">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('admin.mesures-prevention.edit', $mesure) }}"
                                           class="btn btn-sm btn-outline-primary" title="Modifier">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('admin.mesures-prevention.destroy', $mesure) }}"
                                              onsubmit="return confirm('Confirmer la suppression de cette mesure de prévention ?');">
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
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bx bx-shield-x fs-2 d-block mb-2"></i>
                                    Aucune mesure de prévention ne correspond aux critères sélectionnés.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <x-pagination :paginator="$mesuresPrevention" variant="bootstrap-5" />
@endsection
