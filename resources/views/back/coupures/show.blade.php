@extends('back.layouts.back')
@section('title', 'Détail de la coupure')
@section('content')
    <x-page-header :title="'Coupure #' . $coupure->id" subtitle="Détail de la coupure">
        @slot('actions')<a href="{{ route('admin.coupures.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i> Retour</a><a href="{{ route('admin.coupures.edit', $coupure) }}" class="btn btn-primary"><i class="bx bx-edit me-1"></i> Modifier</a>@endslot
    </x-page-header>
    <div class="card"><div class="card-header"><h5 class="mb-0">Informations</h5></div><div class="card-body">
        <div class="row mb-3"><div class="col-sm-3 text-muted">Quartier</div><div class="col-sm-9"><a href="{{ route('admin.quartiers.show', $coupure->quartier) }}">{{ $coupure->quartier?->nom ?? '—' }}</a></div></div>
        <div class="row mb-3"><div class="col-sm-3 text-muted">Type</div><div class="col-sm-9">{{ ucfirst($coupure->type) }}</div></div>
        <div class="row mb-3"><div class="col-sm-3 text-muted">Statut</div><div class="col-sm-9"><span class="badge {{ ['en_cours' => 'bg-danger', 'prevue' => 'bg-warning text-dark', 'terminee' => 'bg-success'][$coupure->statut] }}">{{ str_replace('_', ' ', ucfirst($coupure->statut)) }}</span></div></div>
        <div class="row mb-3"><div class="col-sm-3 text-muted">Début</div><div class="col-sm-9">{{ $coupure->date_debut->format('d/m/Y H:i') }}</div></div>
        <div class="row mb-3"><div class="col-sm-3 text-muted">Fin</div><div class="col-sm-9">{{ $coupure->date_fin?->format('d/m/Y H:i') ?? 'Non renseignée' }}</div></div>
        <div class="row"><div class="col-sm-3 text-muted">Description</div><div class="col-sm-9">{{ $coupure->description ?? '—' }}</div></div>
    </div></div>
    <div class="row g-4 mt-1">
        <div class="col-lg-6">
            <div class="card h-100"><div class="card-header"><h5 class="mb-0">Interventions ({{ $coupure->interventions->count() }})</h5></div><div class="card-body">
                @forelse ($coupure->interventions as $intervention)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div><a href="{{ route('admin.interventions.show', $intervention) }}">{{ $intervention->equipe }}</a><div class="small text-muted">{{ $intervention->date_intervention->format('d/m/Y H:i') }}</div></div>
                        <span class="badge {{ ['prevue' => 'bg-warning text-dark', 'en_cours' => 'bg-primary', 'terminee' => 'bg-success'][$intervention->statut] }}">{{ str_replace('_', ' ', ucfirst($intervention->statut)) }}</span>
                    </div>
                @empty<p class="text-muted mb-0">Aucune intervention rattachée.</p>@endforelse
            </div></div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100"><div class="card-header"><h5 class="mb-0">Impacts ({{ $coupure->impacts->count() }})</h5></div><div class="card-body">
                @forelse ($coupure->impacts as $impact)
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div><a href="{{ route('admin.impacts-coupures.show', $impact) }}">{{ $impact->nom_structure }}</a><div class="small text-muted">{{ number_format($impact->personnes_affectees, 0, ',', ' ') }} personne(s) affectée(s)</div></div>
                        <span class="badge {{ ['moyen' => 'bg-info', 'eleve' => 'bg-warning text-dark', 'critique' => 'bg-danger'][$impact->niveau_gravite] }}">{{ ucfirst($impact->niveau_gravite) }}</span>
                    </div>
                @empty<p class="text-muted mb-0">Aucun impact rattaché.</p>@endforelse
            </div></div>
        </div>
    </div>
@endsection