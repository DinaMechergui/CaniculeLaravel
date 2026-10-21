@extends('back.layouts.back')
@section('title', 'SmartGrid AI')
@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
    <style>
        #smartgrid-map{height:min(70vh,680px);min-height:400px;z-index:1}
        .sg-panel{max-height:680px;overflow:auto}
        .sg-map-legend span{display:inline-flex;align-items:center;gap:.4rem;margin-right:1rem}
        .sg-dot{width:12px;height:12px;border-radius:50%;display:inline-block}
        .sg-priority{min-width:48px}
        .sg-marker{border:2px solid #fff;border-radius:50%;box-shadow:0 1px 5px #444;display:block}
        @media(max-width:767.98px){#smartgrid-map{height:52vh;min-height:330px}.sg-panel{max-height:none}}
    </style>
@endpush
@section('content')
    <x-page-header title="SmartGrid AI" subtitle="Cartographie opérationnelle, priorisation et aide à la décision">
        @slot('actions')<span class="badge {{ $aiConfigured ? 'bg-success' : 'bg-label-warning' }}">IA {{ $aiConfigured ? 'configurée' : 'non configurée' }}</span><span class="badge {{ $routingConfigured ? 'bg-success' : 'bg-label-warning' }}">Routage {{ $routingConfigured ? 'configuré' : 'non configuré' }}</span>@endslot
    </x-page-header>
    <div class="alert alert-info" role="note">Les scores sont des règles déterministes d’aide au tri, non des prédictions. Toute recommandation IA ou optimisation doit être vérifiée par un administrateur. Aucune position d’équipe n’est suivie.</div>

    <div class="row g-3 mb-4" id="sg-statistics">
        @foreach([['geolocalisees', 'Coupures géolocalisées', 'bx-map-pin'], ['en_cours', 'En cours', 'bx-bolt-circle'], ['prevues', 'Prévues', 'bx-time'], ['interventions_en_cours', 'Interventions en cours', 'bx-wrench'], ['personnes_affectees', 'Personnes affectées', 'bx-group'], ['impacts_critiques', 'Impacts critiques', 'bx-error-circle']] as [$key, $label, $icon])
            <div class="col-6 col-xl-2"><div class="card h-100"><div class="card-body d-flex align-items-center gap-3"><span class="avatar-initial rounded bg-label-primary"><i class="bx {{ $icon }} fs-3"></i></span><div><small class="text-muted">{{ $label }}</small><h4 class="mb-0" data-stat="{{ $key }}">—</h4></div></div></div></div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="card mb-4"><div class="card-header"><h5 class="mb-0">Filtres cartographiques</h5></div><div class="card-body">
                <form id="sg-filters" class="row g-3 align-items-end" novalidate>
                    <div class="col-6 col-lg-4"><label class="form-label" for="sg-quartier">Quartier</label><select class="form-select" id="sg-quartier" name="quartier_id"><option value="">Tous</option>@foreach($quartiers as $quartier)<option value="{{ $quartier->id }}">{{ $quartier->nom }}</option>@endforeach</select></div>
                    <div class="col-6 col-lg-4"><label class="form-label" for="sg-ville">Ville</label><select class="form-select" id="sg-ville" name="ville"><option value="">Toutes</option>@foreach($quartiers->pluck('ville')->unique()->sort() as $ville)<option value="{{ $ville }}">{{ $ville }}</option>@endforeach</select></div>
                    <div class="col-6 col-lg-4"><label class="form-label" for="sg-type">Type</label><select class="form-select" id="sg-type" name="type"><option value="">Tous</option><option value="delestage">Délestage</option><option value="panne">Panne</option><option value="surcharge">Surcharge</option></select></div>
                    <div class="col-6 col-lg-3"><label class="form-label" for="sg-statut">Statut</label><select class="form-select" id="sg-statut" name="statut"><option value="">Tous</option><option value="en_cours">En cours</option><option value="prevue">Prévue</option><option value="terminee">Terminée</option></select></div>
                    <div class="col-6 col-lg-3"><label class="form-label" for="sg-gravite">Gravité d’impact</label><select class="form-select" id="sg-gravite" name="niveau_gravite"><option value="">Toutes</option><option value="moyen">Moyenne</option><option value="eleve">Élevée</option><option value="critique">Critique</option></select></div>
                    <div class="col-6 col-lg-3"><label class="form-label" for="sg-categorie">Établissement</label><select class="form-select" id="sg-categorie" name="categorie"><option value="">Tous</option><option value="hopital">Hôpital</option><option value="ecole">École</option><option value="commerce">Commerce</option><option value="residence">Résidence</option><option value="autre">Autre</option></select></div>
                    <div class="col-6 col-lg-3"><label class="form-label" for="sg-date-debut">Depuis le</label><input class="form-control" type="date" id="sg-date-debut" name="date_debut"></div>
                    <div class="col-6 col-lg-3"><label class="form-label" for="sg-date-fin">Jusqu’au</label><input class="form-control" type="date" id="sg-date-fin" name="date_fin"></div>
                    <div class="col-12 col-lg-9 d-flex flex-wrap gap-3 align-items-center">
                        <label class="form-check"><input class="form-check-input" type="checkbox" id="sg-show-cuts" checked> Coupures</label>
                        <label class="form-check"><input class="form-check-input" type="checkbox" id="sg-show-impacts" checked> Impacts</label>
                        <label class="form-check"><input class="form-check-input" type="checkbox" id="sg-show-interventions"> Interventions (lieu de coupure, pas position d’équipe)</label>
                    </div>
                    <div class="col-12 d-flex gap-2"><button class="btn btn-primary" type="submit">Actualiser</button><button class="btn btn-outline-secondary" type="reset">Réinitialiser les filtres</button></div>
                </form>
            </div></div>
            <div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0">Carte de Tunisie</h5><span class="small text-muted sg-map-legend"><span><i class="sg-dot bg-danger"></i> En cours</span><span><i class="sg-dot bg-warning"></i> Prévue</span><span><i class="sg-dot bg-success"></i> Terminée</span></span></div><div class="card-body p-0"><div id="smartgrid-map" aria-label="Carte des coupures et impacts"></div></div><div class="card-footer small text-muted">© OpenStreetMap contributors. Les marqueurs ne représentent que les coordonnées renseignées.</div></div>
            <div class="card mt-4"><div class="card-header"><h5 class="mb-0">Optimiser les interventions</h5><p class="small text-muted mb-0">Priorité métier décroissante, puis durée routière minimale entre les interventions de priorité égale.</p></div><div class="card-body">
                @if(!$routingConfigured)<div class="alert alert-warning">Ajoutez OPENROUTESERVICE_API_KEY pour calculer des trajets routiers réels. La carte reste disponible.</div>@endif
                <div class="row g-3 align-items-end"><div class="col-6"><label class="form-label" for="sg-origin-lat">Départ — latitude</label><input class="form-control" type="number" step="0.0000001" min="-90" max="90" id="sg-origin-lat"></div><div class="col-6"><label class="form-label" for="sg-origin-lng">Départ — longitude</label><input class="form-control" type="number" step="0.0000001" min="-180" max="180" id="sg-origin-lng"></div><div class="col-12 d-flex gap-2"><button class="btn btn-outline-primary" type="button" id="sg-pick-origin">Choisir le départ sur la carte</button><button class="btn btn-primary" type="button" id="sg-route-button" @disabled(!$routingConfigured)>Calculer le trajet</button></div></div>
                <div id="sg-route-result" class="mt-3" aria-live="polite"></div>
            </div></div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card sg-panel mb-4"><div class="card-header d-flex justify-content-between"><h5 class="mb-0">Coupures correspondantes</h5><span class="badge bg-label-primary" id="sg-cut-count">0</span></div><div class="list-group list-group-flush" id="sg-cut-list"><div class="p-3 text-muted">Chargement…</div></div></div>
            <div class="card mb-4"><div class="card-header"><h5 class="mb-0">Analyse d’une zone</h5></div><div class="card-body">
                <label class="form-label" for="sg-zone">Quartier</label><select id="sg-zone" class="form-select mb-3"><option value="">Sélectionner un quartier</option>@foreach($quartiers as $quartier)<option value="{{ $quartier->id }}">{{ $quartier->nom }} — {{ $quartier->ville }}</option>@endforeach</select>
                @if(!$aiConfigured)<p class="small text-warning">Configurez GEMINI_API_KEY pour activer les analyses. Les scores déterministes restent disponibles.</p>@endif
                <button class="btn btn-outline-primary w-100" id="sg-zone-button" type="button" @disabled(!$aiConfigured)>Analyser la zone avec l’IA</button>
                <div id="sg-zone-result" class="mt-3" aria-live="polite"></div>
            </div></div>
            <div class="card"><div class="card-header"><h5 class="mb-0">Analyse de la coupure sélectionnée</h5></div><div class="card-body" id="sg-selected"><p class="text-muted mb-0">Sélectionnez une coupure dans la liste ou sur la carte.</p></div></div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script>
        window.smartGridConfig = {
            dataUrl: @json(route('admin.smartgrid.data')),
            routeUrl: @json(route('admin.smartgrid.route')),
            analyzeUrl: @json(url('/admin/smartgrid/analyze')),
            analyzeZoneUrl: @json(route('admin.smartgrid.analyze-zone')),
            aiConfigured: @json($aiConfigured),
            csrf: @json(csrf_token()),
        };
    </script>
    <script src="{{ asset('assets/js/smartgrid.js') }}" defer></script>
@endpush
