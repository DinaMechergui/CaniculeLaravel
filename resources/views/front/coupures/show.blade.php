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
            <li><i class="fa fa-clock-o" aria-hidden="true"></i><div><strong>Fin prévue</strong><span>@if($coupure->date_fin){{ $coupure->date_fin->format('d/m/Y à H:i') }}@else<em class="text-warning">Estimation : Rétablissement sous 2h à 3h (équipes STEG mobilisées)</em>@endif</span></div></li>
        </ul></article><article class="pf-detail-card"><div class="pf-detail-icon"><i class="fa fa-info-circle" aria-hidden="true"></i></div><h4>Description</h4><p>{{ $coupure->description ?? 'Aucune information complémentaire.' }}</p></article></div>
        <div class="pf-detail-actions text-center"><a href="{{ route('coupures.index', ['quartier' => $coupure->quartier_id]) }}" class="ve-btn-primary"><i class="fa fa-arrow-left" aria-hidden="true"></i> Retour aux coupures</a></div>
    </div></section>
@endsection