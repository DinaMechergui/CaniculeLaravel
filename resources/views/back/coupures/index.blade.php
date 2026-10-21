@extends('back.layouts.back')
@section('title', 'Coupures')
@section('content')
    <x-page-header title="Coupures" :subtitle="'Liste des coupures (' . $coupures->total() . ')'">
        @slot('actions')
            <a href="{{ route('admin.coupures.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Nouvelle coupure</a>
        @endslot
    </x-page-header>

    {{-- Bannière d'alerte des zones critiques --}}
    @if (!empty($quartiersCritiques) && $quartiersCritiques->isNotEmpty())
        <div class="alert alert-danger border-danger mb-4 shadow-sm" role="alert">
            <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                <div class="d-flex align-items-center">
                    <i class="bx bx-error-circle bx-sm me-2 text-danger"></i>
                    <h5 class="alert-heading mb-0 text-danger fw-bold">
                        🚨 Alerte Admin : Forte concentration de coupures détectée (≥ 3 incidents)
                    </h5>
                </div>
                <span class="badge bg-danger">{{ $quartiersCritiques->count() }} quartier(s) en alerte</span>
            </div>
            <p class="mb-2 text-dark">
                Le système a détecté une récurrence anormale de coupures dans les secteurs suivants. Cliquez sur un quartier pour filtrer ses incidents :
            </p>
            <div class="d-flex flex-wrap gap-2">
                @foreach ($quartiersCritiques as $critique)
                    <a href="{{ route('admin.coupures.index', ['quartier_id' => $critique->id]) }}" class="badge bg-danger p-2 text-white text-decoration-none">
                        <i class="bx bx-map-pin me-1"></i> {{ $critique->nom }} ({{ $critique->ville }}) — <strong>{{ $critique->coupures_count }} coupures</strong>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-1"><i class="bx bx-filter-alt me-1 text-primary"></i> Recherche avancée</h5>
                <small class="text-muted">Filtrez par zone, état, type et période.</small>
            </div>
            <span class="badge bg-label-primary">{{ $coupures->total() }} résultat(s)</span>
        </div>
        <div class="card-body pt-2">
            <form method="GET" action="{{ route('admin.coupures.index') }}">
                <div class="row g-3">
                    <div class="col-12 col-lg-4">
                        <label class="form-label" for="search">Recherche globale</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input class="form-control" id="search" name="search" value="{{ old('search', $filters['search'] ?? '') }}" placeholder="Quartier, ville ou description...">
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label" for="quartier_id">Quartier</label>
                        <select class="form-select" id="quartier_id" name="quartier_id">
                            <option value="">Tous les quartiers</option>
                            @foreach ($quartiers as $quartier)
                                <option value="{{ $quartier->id }}" @selected((string) old('quartier_id', $filters['quartier_id'] ?? '') === (string) $quartier->id)>{{ $quartier->nom }} · {{ $quartier->ville }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label" for="type">Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">Tous les types</option>
                            <option value="delestage" @selected(old('type', $filters['type'] ?? '') === 'delestage')>Délestage</option>
                            <option value="panne" @selected(old('type', $filters['type'] ?? '') === 'panne')>Panne</option>
                            <option value="surcharge" @selected(old('type', $filters['type'] ?? '') === 'surcharge')>Surcharge</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label" for="statut">Statut</label>
                        <select class="form-select" id="statut" name="statut">
                            <option value="">Tous les statuts</option>
                            <option value="prevue" @selected(old('statut', $filters['statut'] ?? '') === 'prevue')>Prévue</option>
                            <option value="en_cours" @selected(old('statut', $filters['statut'] ?? '') === 'en_cours')>En cours</option>
                            <option value="terminee" @selected(old('statut', $filters['statut'] ?? '') === 'terminee')>Terminée</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-12"><small class="text-uppercase fw-semibold text-muted">Période de début</small></div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <label class="form-label" for="date_debut_from">À partir du</label>
                        <input class="form-control" type="date" id="date_debut_from" name="date_debut_from" value="{{ old('date_debut_from', $filters['date_debut_from'] ?? '') }}">
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <label class="form-label" for="date_debut_to">Jusqu'au</label>
                        <input class="form-control" type="date" id="date_debut_to" name="date_debut_to" value="{{ old('date_debut_to', $filters['date_debut_to'] ?? '') }}">
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <label class="form-label" for="date_fin_from">Fin à partir du</label>
                        <input class="form-control" type="date" id="date_fin_from" name="date_fin_from" value="{{ old('date_fin_from', $filters['date_fin_from'] ?? '') }}">
                    </div>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <label class="form-label" for="date_fin_to">Fin jusqu'au</label>
                        <input class="form-control" type="date" id="date_fin_to" name="date_fin_to" value="{{ old('date_fin_to', $filters['date_fin_to'] ?? '') }}">
                    </div>
                </div>

                <div class="row g-3 align-items-end mt-1">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label" for="sort">Trier par</label>
                        <select class="form-select" id="sort" name="sort">
                            <option value="date_debut" @selected(($filters['sort'] ?? 'date_debut') === 'date_debut')>Date de début</option>
                            <option value="date_fin" @selected(($filters['sort'] ?? '') === 'date_fin')>Date de fin</option>
                            <option value="created_at" @selected(($filters['sort'] ?? '') === 'created_at')>Date d'ajout</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label" for="direction">Ordre</label>
                        <select class="form-select" id="direction" name="direction">
                            <option value="desc" @selected(($filters['direction'] ?? 'desc') === 'desc')>Plus récentes</option>
                            <option value="asc" @selected(($filters['direction'] ?? '') === 'asc')>Plus anciennes</option>
                        </select>
                    </div>
                    <div class="col-12 col-lg-7 d-flex flex-wrap justify-content-lg-end gap-2">
                        <button class="btn btn-primary" type="submit"><i class="bx bx-search me-1"></i> Appliquer les filtres</button>
                        @if ($hasFilters)
                            <a href="{{ route('admin.coupures.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset me-1"></i> Réinitialiser</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Résultats</h5>
            <small class="text-muted">{{ $coupures->firstItem() ?? 0 }}–{{ $coupures->lastItem() ?? 0 }} sur {{ $coupures->total() }}</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr><th class="ps-4">#</th><th>Quartier</th><th>Type</th><th>Statut</th><th>Début</th><th>Fin</th><th class="text-end pe-4">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($coupures as $coupure)
                            @php
                                $typeLabels = ['delestage' => 'Délestage', 'panne' => 'Panne', 'surcharge' => 'Surcharge'];
                                $statutLabels = ['en_cours' => 'En cours', 'prevue' => 'Prévue', 'terminee' => 'Terminée'];
                                $statutClasses = ['en_cours' => 'bg-label-danger', 'prevue' => 'bg-label-warning', 'terminee' => 'bg-label-success'];
                            @endphp
                            <tr>
                                <td class="ps-4 text-muted">{{ $coupure->id }}</td>
                                <td>
                                    <a class="fw-semibold" href="{{ route('admin.quartiers.show', $coupure->quartier) }}">{{ $coupure->quartier?->nom ?? '—' }}</a>
                                    <small class="d-block text-muted">{{ $coupure->quartier?->ville }}</small>
                                </td>
                                <td>{{ $typeLabels[$coupure->type] ?? $coupure->type }}</td>
                                <td><span class="badge {{ $statutClasses[$coupure->statut] ?? 'bg-label-secondary' }}">{{ $statutLabels[$coupure->statut] ?? $coupure->statut }}</span></td>
                                <td class="text-nowrap">{{ $coupure->date_debut->format('d/m/Y H:i') }}</td>
                                <td class="text-nowrap">{{ $coupure->date_fin?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex justify-content-end gap-1">
                                        <a href="{{ route('admin.coupures.show', $coupure) }}" class="btn btn-sm btn-icon btn-outline-secondary" title="Voir" aria-label="Voir la coupure {{ $coupure->id }}"><i class="bx bx-show"></i></a>
                                        <a href="{{ route('admin.coupures.edit', $coupure) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Modifier" aria-label="Modifier la coupure {{ $coupure->id }}"><i class="bx bx-edit"></i></a>
                                        <form method="POST" action="{{ route('admin.coupures.destroy', $coupure) }}" onsubmit="return confirm('Confirmer la suppression de cette coupure ?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-icon btn-outline-danger" title="Supprimer" aria-label="Supprimer la coupure {{ $coupure->id }}"><i class="bx bx-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-5"><i class="bx bx-search-alt-2 fs-2 d-block mb-2"></i>Aucune coupure ne correspond aux filtres sélectionnés.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <x-pagination :paginator="$coupures" variant="bootstrap-5" />
@endsection