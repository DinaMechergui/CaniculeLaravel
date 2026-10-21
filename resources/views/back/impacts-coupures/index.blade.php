@extends('back.layouts.back')
@section('title', 'Impacts des coupures')
@section('content')
    <x-page-header title="Gestion des impacts des coupures électriques" :subtitle="'Impacts recensés (' . $impacts->total() . ')'">
        @slot('actions')<a href="{{ route('admin.impacts-coupures.create') }}" class="btn btn-primary"><i class="bx bx-plus me-1"></i> Nouvel impact</a>@endslot
    </x-page-header>
    <div class="row g-3 mb-4">
        @foreach([['Total des impacts', $statistiques['total'], 'bx-pulse', 'primary'], ['Personnes affectées', number_format($statistiques['personnes'], 0, ',', ' '), 'bx-group', 'info'], ['Impacts critiques', $statistiques['critiques'], 'bx-error-circle', 'danger'], ['Résolus', $statistiques['resolus'], 'bx-check-circle', 'success'], ['En traitement', $statistiques['en_traitement'], 'bx-time-five', 'warning']] as [$label, $value, $icon, $color])
            <div class="col-6 col-xl"><div class="card h-100"><div class="card-body d-flex align-items-center gap-3"><span class="avatar-initial rounded bg-label-{{ $color }}"><i class="bx {{ $icon }} fs-3"></i></span><div><small class="text-muted">{{ $label }}</small><h4 class="mb-0">{{ $value }}</h4></div></div></div></div>
        @endforeach
    </div>
    <div class="card mb-4"><div class="card-body">
        <form method="GET" action="{{ route('admin.impacts-coupures.index') }}" class="row g-3 align-items-end">
            <div class="col-12 col-md-4"><label class="form-label" for="search">Structure</label><input class="form-control" id="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nom de la structure"></div>
            <div class="col-12 col-md-4"><label class="form-label" for="quartier_id">Quartier</label><select class="form-select" id="quartier_id" name="quartier_id"><option value="">Tous</option>@foreach($quartiers as $quartier)<option value="{{ $quartier->id }}" @selected((string) ($filters['quartier_id'] ?? '') === (string) $quartier->id)>{{ $quartier->nom }}</option>@endforeach</select></div>
            <div class="col-12 col-md-4"><label class="form-label" for="coupure_id">Coupure</label><select class="form-select" id="coupure_id" name="coupure_id"><option value="">Toutes</option>@foreach($coupures as $coupure)<option value="{{ $coupure->id }}" @selected((string) ($filters['coupure_id'] ?? '') === (string) $coupure->id)>#{{ $coupure->id }} · {{ $coupure->quartier?->nom }}</option>@endforeach</select></div>
            <div class="col-12 col-sm-6 col-lg-3"><label class="form-label" for="categorie">Catégorie</label><select class="form-select" id="categorie" name="categorie"><option value="">Toutes</option>@foreach(['hopital' => 'Hôpital', 'ecole' => 'École', 'commerce' => 'Commerce', 'residence' => 'Résidence', 'autre' => 'Autre'] as $value => $label)<option value="{{ $value }}" @selected(($filters['categorie'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-12 col-sm-6 col-lg-3"><label class="form-label" for="niveau_gravite">Gravité</label><select class="form-select" id="niveau_gravite" name="niveau_gravite"><option value="">Tous les niveaux</option>@foreach(['moyen' => 'Moyen', 'eleve' => 'Élevé', 'critique' => 'Critique'] as $value => $label)<option value="{{ $value }}" @selected(($filters['niveau_gravite'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-12 col-sm-6 col-lg-3"><label class="form-label" for="statut">Statut</label><select class="form-select" id="statut" name="statut"><option value="">Tous</option>@foreach(['en_traitement' => 'En traitement', 'resolu' => 'Résolu'] as $value => $label)<option value="{{ $value }}" @selected(($filters['statut'] ?? '') === $value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-12 col-lg-3 d-flex gap-2"><button class="btn btn-primary" type="submit">Filtrer</button><a class="btn btn-outline-secondary" href="{{ route('admin.impacts-coupures.index') }}">Effacer</a></div>
        </form>
    </div></div>
    <div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="table-dark"><tr><th>Structure</th><th>Coupure / quartier</th><th>Catégorie</th><th>Gravité</th><th>Personnes</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
        <tbody>
            @forelse($impacts as $impact)
                <tr>
                    <td><a class="fw-semibold" href="{{ route('admin.impacts-coupures.show', $impact) }}">{{ $impact->nom_structure }}</a></td>
                    <td>#{{ $impact->coupure_id }} · {{ $impact->coupure?->quartier?->nom }}</td>
                    <td>{{ ['hopital' => 'Hôpital', 'ecole' => 'École', 'commerce' => 'Commerce', 'residence' => 'Résidence', 'autre' => 'Autre'][$impact->categorie] ?? 'Autre' }}</td>
                    <td><span class="badge {{ ['moyen' => 'bg-info', 'eleve' => 'bg-warning text-dark', 'critique' => 'bg-danger'][$impact->niveau_gravite] }}">{{ ucfirst($impact->niveau_gravite) }}</span></td>
                    <td>{{ number_format($impact->personnes_affectees, 0, ',', ' ') }}</td>
                    <td><span class="badge {{ $impact->statut === 'resolu' ? 'bg-success' : 'bg-primary' }}">{{ str_replace('_', ' ', ucfirst($impact->statut)) }}</span></td>
                    <td class="text-end"><div class="d-inline-flex gap-1"><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.impacts-coupures.show', $impact) }}" aria-label="Voir"><i class="bx bx-show"></i></a><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.impacts-coupures.edit', $impact) }}" aria-label="Modifier"><i class="bx bx-edit"></i></a><form method="POST" action="{{ route('admin.impacts-coupures.destroy', $impact) }}" onsubmit="return confirm('Confirmer la suppression de cet impact ?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" aria-label="Supprimer"><i class="bx bx-trash"></i></button></form></div></td>
                </tr>
            @empty<tr><td colspan="7" class="text-center text-muted py-4">Aucun impact trouvé.</td></tr>@endforelse
        </tbody>
    </table></div><div class="card-body">{{ $impacts->links() }}</div></div>
@endsection
