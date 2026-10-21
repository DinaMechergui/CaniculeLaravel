@extends('back.layouts.back')

@section('title', 'Quartiers')

@section('content')
    <x-page-header title="Quartiers" :subtitle="'Liste des quartiers (' . $quartiers->total() . ')'">
        @slot('actions')
            <a href="{{ route('admin.quartiers.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Nouveau quartier
            </a>
        @endslot
    </x-page-header>

    <div class="card mb-4">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-1"><i class="bx bx-filter-alt me-1 text-primary"></i> Recherche et filtres</h5>
                <small class="text-muted">Combinez plusieurs critères pour trouver un quartier.</small>
            </div>
            <span class="badge bg-label-primary">{{ $quartiers->total() }} résultat(s)</span>
        </div>
        <div class="card-body pt-2">
            <form method="GET" action="{{ route('admin.quartiers.index') }}" novalidate>
                <div class="row g-3">
                    <div class="col-12 col-lg-4">
                        <label class="form-label" for="search">Recherche globale</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bx bx-search"></i></span>
                            <input class="form-control" id="search" name="search" value="{{ old('search', $filters['search'] ?? '') }}" placeholder="Nom, ville ou code postal...">
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label" for="ville">Ville</label>
                        <select class="form-select" id="ville" name="ville">
                            <option value="">Toutes les villes</option>
                            @foreach ($villes as $ville)
                                <option value="{{ $ville }}" @selected(old('ville', $filters['ville'] ?? '') === $ville)>{{ $ville }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label" for="code_postal">Code postal</label>
                        <input class="form-control" id="code_postal" name="code_postal" value="{{ old('code_postal', $filters['code_postal'] ?? '') }}" placeholder="Ex. 1004">
                    </div>
                    <div class="col-6 col-lg-2">
                        <label class="form-label" for="coupures_min">Coupures minimum</label>
                        <input class="form-control" type="number" min="0" id="coupures_min" name="coupures_min" value="{{ old('coupures_min', $filters['coupures_min'] ?? '') }}" placeholder="0">
                    </div>
                    <div class="col-6 col-lg-2">
                        <label class="form-label" for="coupures_max">Coupures maximum</label>
                        <input class="form-control" type="number" min="0" id="coupures_max" name="coupures_max" value="{{ old('coupures_max', $filters['coupures_max'] ?? '') }}" placeholder="Sans limite">
                    </div>
                </div>

                <div class="row g-3 align-items-end mt-1">
                    <div class="col-12 col-sm-6 col-lg-3">
                        <label class="form-label" for="sort">Trier par</label>
                        <select class="form-select" id="sort" name="sort">
                            <option value="nom" @selected(($filters['sort'] ?? 'nom') === 'nom')>Nom</option>
                            <option value="ville" @selected(($filters['sort'] ?? '') === 'ville')>Ville</option>
                            <option value="code_postal" @selected(($filters['sort'] ?? '') === 'code_postal')>Code postal</option>
                            <option value="coupures_count" @selected(($filters['sort'] ?? '') === 'coupures_count')>Nombre de coupures</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2">
                        <label class="form-label" for="direction">Ordre</label>
                        <select class="form-select" id="direction" name="direction">
                            <option value="asc" @selected(($filters['direction'] ?? 'asc') === 'asc')>Croissant</option>
                            <option value="desc" @selected(($filters['direction'] ?? '') === 'desc')>Décroissant</option>
                        </select>
                    </div>
                    <div class="col-12 col-lg-7 d-flex flex-wrap justify-content-lg-end gap-2">
                        <button class="btn btn-primary" type="submit"><i class="bx bx-search me-1"></i> Appliquer les filtres</button>
                        @if ($hasFilters)
                            <a href="{{ route('admin.quartiers.index') }}" class="btn btn-outline-secondary"><i class="bx bx-reset me-1"></i> Réinitialiser</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Résultats</h5>
            <small class="text-muted">{{ $quartiers->firstItem() ?? 0 }}–{{ $quartiers->lastItem() ?? 0 }} sur {{ $quartiers->total() }}</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr><th class="ps-4">#</th><th>Nom</th><th>Ville</th><th>Code postal</th><th>Coupures</th><th class="text-end pe-4">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($quartiers as $quartier)
                            <tr>
                                <td class="ps-4 text-muted">{{ $quartier->id }}</td>
                                <td><a class="fw-semibold" href="{{ route('admin.quartiers.show', $quartier) }}">{{ $quartier->nom }}</a></td>
                                <td>{{ $quartier->ville }}</td>
                                <td><span class="font-monospace">{{ $quartier->code_postal }}</span></td>
                                <td><span class="badge bg-label-info">{{ $quartier->coupures_count }}</span></td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex justify-content-end gap-1">
                                        <a href="{{ route('admin.quartiers.show', $quartier) }}" class="btn btn-sm btn-icon btn-outline-secondary" title="Voir" aria-label="Voir {{ $quartier->nom }}"><i class="bx bx-show"></i></a>
                                        <a href="{{ route('admin.quartiers.edit', $quartier) }}" class="btn btn-sm btn-icon btn-outline-primary" title="Modifier" aria-label="Modifier {{ $quartier->nom }}"><i class="bx bx-edit"></i></a>
                                        <form method="POST" action="{{ route('admin.quartiers.destroy', $quartier) }}" onsubmit="return confirm('Confirmer la suppression de ce quartier ?');" novalidate>
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-icon btn-outline-danger" title="Supprimer" aria-label="Supprimer {{ $quartier->nom }}"><i class="bx bx-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5"><i class="bx bx-search-alt-2 fs-2 d-block mb-2"></i>Aucun quartier ne correspond aux filtres sélectionnés.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <x-pagination :paginator="$quartiers" variant="bootstrap-5" />
@endsection