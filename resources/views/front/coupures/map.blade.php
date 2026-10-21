@extends('front.layouts.front')
@section('title', 'Carte publique des coupures')
@section('description', 'Carte publique des coupures prévues et en cours et des impacts géolocalisés.')
@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
    <style>
        #public-outage-map{height:min(70vh,650px);min-height:380px}
        @media(max-width:767.98px){#public-outage-map{height:55vh;min-height:320px}}
    </style>
@endpush
@section('content')
    <section class="ve-section"><div class="container">
        <nav class="pf-breadcrumb" aria-label="Fil d’Ariane"><a href="{{ route('front.home') }}">Accueil</a><span>/</span><a href="{{ route('coupures.index') }}">Coupures</a><span>/</span><strong>Carte</strong></nav>
        <div class="ve-section-header text-center"><span class="ve-section-tag">SmartGrid</span><h2>Carte publique des coupures</h2><p>Seules les coupures prévues ou en cours et les positions explicitement enregistrées sont affichées.</p></div>
        <form id="public-map-filters" class="pf-filters mb-4">
            <div class="pf-filter-field pf-filter-select"><i class="fa fa-map-marker" aria-hidden="true"></i><select name="quartier_id" aria-label="Filtrer par quartier"><option value="">Tous les quartiers</option>@foreach($quartiers as $quartier)<option value="{{ $quartier->id }}">{{ $quartier->nom }} — {{ $quartier->ville }}</option>@endforeach</select></div>
            <div class="pf-filter-field pf-filter-select"><i class="fa fa-bolt" aria-hidden="true"></i><select name="statut" aria-label="Filtrer par statut"><option value="">Tous les statuts</option><option value="en_cours">En cours</option><option value="prevue">Prévue</option></select></div>
            <button type="submit" class="ve-btn-primary">Filtrer</button>
            <button type="reset" class="pf-reset">Réinitialiser</button>
        </form>
        <div id="public-map-error" class="alert alert-danger d-none" role="alert"></div>
        <div id="public-outage-map" class="rounded shadow-sm" aria-label="Carte publique des coupures"></div>
        <p class="small text-muted mt-2">© OpenStreetMap contributors. Les rapports techniques, adresses privées et positions d’équipes ne sont pas publiés.</p>
        <div id="public-map-empty" class="pf-empty mt-3 d-none">Aucune coupure géolocalisée ne correspond aux filtres. Les coupures sans position précise ne sont pas placées sur la carte.</div>
        <div id="public-map-list" class="pf-grid mt-4"></div>
    </div></section>
@endsection
@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script>window.publicOutageMapUrl = @json(route('coupures.map.data'));</script>
    <script src="{{ asset('assets/js/public-outage-map.js') }}" defer></script>
@endpush
