@extends('front.layouts.front')
@section('title', 'Coupure — ' . ($coupure->quartier?->nom ?? 'Quartier'))
@section('description', 'Détail d’une coupure de courant à ' . ($coupure->quartier?->nom ?? ''))
@section('content')
    <section class="ve-section"><div class="container">
        <nav class="pf-breadcrumb" aria-label="Fil d’Ariane"><a href="{{ route('front.home') }}">Accueil</a><span>/</span><a href="{{ route('coupures.index') }}">Coupures</a><span>/</span><strong>{{ $coupure->quartier?->nom }}</strong></nav>
        <div class="ve-section-header text-center"><span class="ve-section-tag">{{ $coupure->quartier?->ville }}</span><h2>{{ $coupure->quartier?->nom }}</h2><p><span class="badge {{ ['en_cours' => 'bg-danger', 'prevue' => 'bg-warning text-dark', 'terminee' => 'bg-success'][$coupure->statut] }}">{{ str_replace('_', ' ', ucfirst($coupure->statut)) }}</span></p></div>
        <div class="pf-detail-grid"><article class="pf-detail-card"><div class="pf-detail-icon"><i class="fa fa-bolt" aria-hidden="true"></i></div><h4>Informations sur la coupure</h4><ul class="pf-detail-list">
            <li><i class="fa fa-map-marker" aria-hidden="true"></i><div><strong>Quartier</strong><span>{{ $coupure->quartier?->nom }} — {{ $coupure->quartier?->ville }}</span></div></li>
            <li><i class="fa fa-bolt" aria-hidden="true"></i><div><strong>Type</strong><span>{{ ucfirst($coupure->type) }}</span></div></li>
            <li><i class="fa fa-clock-o" aria-hidden="true"></i><div><strong>Date de début</strong><span>{{ $coupure->date_debut->format('d/m/Y à H:i') }}</span></div></li>
            <li><i class="fa fa-clock-o" aria-hidden="true"></i><div><strong>Date de fin</strong><span>{{ $coupure->date_fin?->format('d/m/Y à H:i') ?? 'Non précisée' }}</span></div></li>
        </ul></article><article class="pf-detail-card"><div class="pf-detail-icon"><i class="fa fa-info-circle" aria-hidden="true"></i></div><h4>Description</h4><p>{{ $coupure->description ?? 'Aucune information complémentaire.' }}</p></article></div>
        @if ($coupure->interventions->isNotEmpty())
            <div class="pf-detail-card mt-4">
                <h4>Interventions techniques</h4>
                <div class="table-responsive"><table class="table align-middle mb-0">
                    <thead><tr><th>Équipe</th><th>Date</th><th>Statut</th></tr></thead>
                    <tbody>@foreach ($coupure->interventions as $intervention)
                        <tr>
                            <td>{{ $intervention->equipe }}</td>
                            <td>{{ $intervention->date_intervention->format('d/m/Y H:i') }}</td>
                            <td><span class="badge {{ ['prevue' => 'bg-warning text-dark', 'en_cours' => 'bg-primary', 'terminee' => 'bg-success'][$intervention->statut] }}">{{ str_replace('_', ' ', ucfirst($intervention->statut)) }}</span></td>
                        </tr>
                    @endforeach</tbody>
                </table></div>
            </div>
        @endif
        @if ($coupure->impacts->isNotEmpty())
            <div class="pf-detail-card mt-4">
                <h4>Structures et populations affectées</h4>
                <div class="table-responsive"><table class="table align-middle mb-0">
                    <thead><tr><th>Structure</th><th>Catégorie</th><th>Gravité</th><th>Personnes affectées</th><th>Statut</th></tr></thead>
                    <tbody>@foreach ($coupure->impacts as $impact)
                        <tr>
                            <td>{{ $impact->nom_structure }}</td>
                            <td>{{ ['hopital' => 'Hôpital', 'ecole' => 'École', 'commerce' => 'Commerce', 'residence' => 'Résidence', 'autre' => 'Autre'][$impact->categorie] ?? 'Autre' }}</td>
                            <td><span class="badge {{ ['moyen' => 'bg-info', 'eleve' => 'bg-warning text-dark', 'critique' => 'bg-danger'][$impact->niveau_gravite] }}">{{ ucfirst($impact->niveau_gravite) }}</span></td>
                            <td>{{ number_format($impact->personnes_affectees, 0, ',', ' ') }}</td>
                            <td><span class="badge {{ $impact->statut === 'resolu' ? 'bg-success' : 'bg-primary' }}">{{ str_replace('_', ' ', ucfirst($impact->statut)) }}</span></td>
                        </tr>
                        @if ($impact->mesures_urgence)
                            <tr><td colspan="5"><small class="text-muted">Mesures d’urgence publiques : {{ $impact->mesures_urgence }}</small></td></tr>
                        @endif
                    @endforeach</tbody>
                </table></div>
            </div>
        @endif
        <div class="pf-detail-actions text-center"><a href="{{ route('coupures.index', ['quartier' => $coupure->quartier_id]) }}" class="ve-btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i> Retour aux coupures</a></div>
    </div></section>
@endsection