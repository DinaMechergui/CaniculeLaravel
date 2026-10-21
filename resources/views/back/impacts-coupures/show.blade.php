@extends('back.layouts.back')
@section('title', 'Détail de l’impact')
@section('content')
    <x-page-header :title="$impact->nom_structure" :subtitle="'Impact #' . $impact->id">
        @slot('actions')
            <a href="{{ route('admin.impacts-coupures.index') }}" class="btn btn-outline-secondary">Retour</a>
            <a href="{{ route('admin.impacts-coupures.edit', $impact) }}" class="btn btn-primary">Modifier</a>
        @endslot
    </x-page-header>
    <div class="card"><div class="card-body"><div class="row g-3">
        <div class="col-md-6"><span class="text-muted">Coupure / quartier</span><p><a href="{{ route('admin.coupures.show', $impact->coupure) }}">#{{ $impact->coupure_id }} — {{ $impact->coupure?->quartier?->nom }}</a></p></div>
        <div class="col-md-6"><span class="text-muted">Catégorie</span><p>{{ ['hopital' => 'Hôpital', 'ecole' => 'École', 'commerce' => 'Commerce', 'residence' => 'Résidence', 'autre' => 'Autre'][$impact->categorie] ?? 'Autre' }}</p></div>
        <div class="col-md-6"><span class="text-muted">Adresse</span><p>{{ $impact->adresse }}</p></div>
        <div class="col-md-6"><span class="text-muted">Personnes affectées</span><p>{{ number_format($impact->personnes_affectees, 0, ',', ' ') }}</p></div>
        <div class="col-md-6"><span class="text-muted">Gravité</span><p><span class="badge {{ ['moyen' => 'bg-info', 'eleve' => 'bg-warning text-dark', 'critique' => 'bg-danger'][$impact->niveau_gravite] }}">{{ ucfirst($impact->niveau_gravite) }}</span></p></div>
        <div class="col-md-6"><span class="text-muted">Statut</span><p><span class="badge {{ $impact->statut === 'resolu' ? 'bg-success' : 'bg-primary' }}">{{ str_replace('_', ' ', ucfirst($impact->statut)) }}</span></p></div>
        <div class="col-md-6"><span class="text-muted">Signalé le</span><p>{{ $impact->date_signalement->format('d/m/Y H:i') }}</p></div>
        <div class="col-12"><span class="text-muted">Mesures d’urgence</span><p>{{ $impact->mesures_urgence ?: 'Aucune mesure renseignée.' }}</p></div>
        <div class="col-12"><span class="text-muted">Description interne</span><p class="mb-0">{{ $impact->description ?: 'Aucune description.' }}</p></div>
    </div></div></div>
@endsection
