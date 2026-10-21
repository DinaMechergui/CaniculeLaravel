@extends('back.layouts.back')
@section('title', 'Détail du bulletin #' . $bulletinAlerte->id)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <x-page-header :title="'Bulletin officiel #' . $bulletinAlerte->id" :subtitle="'Alerte : ' . $bulletinAlerte->alerte->titre">
        @slot('actions')
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.diffusions.create', ['bulletin_alerte_id' => $bulletinAlerte->id]) }}" class="btn btn-primary">
                    <i class="bx bx-broadcast me-1"></i> Programmer une diffusion
                </a>
                <a href="{{ route('admin.bulletins-alerte.edit', $bulletinAlerte) }}" class="btn btn-outline-secondary">
                    <i class="bx bx-edit me-1"></i> Modifier le bulletin
                </a>
                <a href="{{ route('admin.alertes.show', $bulletinAlerte->alerte) }}" class="btn btn-outline-info">
                    <i class="bx bx-git-branch me-1"></i> Voir toute la chronologie
                </a>
                <a href="{{ route('admin.bulletins-alerte.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Liste
                </a>
            </div>
        @endslot
    </x-page-header>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible shadow-sm mb-4" role="alert">
            <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible shadow-sm mb-4" role="alert">
            <i class="bx bx-error-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Fiche du bulletin --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Données officielles du bulletin</h5>
                    <span class="badge {{ $bulletinAlerte->niveau_badge_class }} text-uppercase fw-bold">
                        {{ $bulletinAlerte->niveau_label }}
                    </span>
                </div>
                <div class="card-body pt-3">
                    <div class="d-flex align-items-center gap-3 p-3 mb-3 bg-light rounded border">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width: 48px; height: 48px; background-color: {{ match($bulletinAlerte->niveau) { 'rouge'=>'#ff3e1d', 'orange'=>'#ffab00', 'jaune'=>'#03c3ec', default=>'#71dd37' } }}; color: white;">
                            <i class="bx {{ match($bulletinAlerte->evolution) { 'aggravation'=>'bx-trending-up', 'stable'=>'bx-minus', 'amelioration'=>'bx-trending-down', default=>'bx-check-double' } }} fs-3"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">{{ $bulletinAlerte->niveau_label }}</h6>
                            <span class="badge bg-label-dark mt-1">
                                <i class="bx {{ $bulletinAlerte->evolution_icon }} me-1"></i>{{ $bulletinAlerte->evolution_label }}
                            </span>
                            @if ($bulletinAlerte->temperature_max !== null)
                                <span class="badge bg-label-danger mt-1 ms-1">
                                    <i class="bx bxs-thermometer me-1"></i>{{ $bulletinAlerte->temperature_max }}°C max
                                </span>
                            @endif
                        </div>
                    </div>

                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="bx bx-calendar me-1"></i>Date de publication :</span>
                            <span class="fw-semibold">{{ $bulletinAlerte->date_publication->format('d/m/Y à H:i') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="bx bx-bell me-1"></i>Alerte parente :</span>
                            <a href="{{ route('admin.alertes.show', $bulletinAlerte->alerte) }}" class="fw-semibold">
                                {{ $bulletinAlerte->alerte->titre }}
                            </a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="bx bx-map-pin me-1"></i>Zone concernée :</span>
                            <span class="fw-semibold">{{ $bulletinAlerte->alerte->zone_concernee }}</span>
                        </li>
                    </ul>

                    <div class="mb-3">
                        <h6 class="fw-semibold text-muted small text-uppercase mb-2">Message officiel :</h6>
                        <div class="p-3 bg-light rounded border" style="white-space: pre-line;">
                            {{ $bulletinAlerte->message }}
                        </div>
                    </div>

                    @if ($bulletinAlerte->conduite_a_tenir)
                        <div class="mb-0">
                            <h6 class="fw-semibold text-warning-emphasis small text-uppercase mb-2">
                                <i class="bx bx-shield-quarter me-1"></i>Conduite à tenir & Conseils immédiats :
                            </h6>
                            <div class="p-3 bg-warning bg-opacity-10 rounded border border-warning" style="white-space: pre-line;">
                                {{ $bulletinAlerte->conduite_a_tenir }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Diffusions attachées --}}
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-broadcast text-primary me-2"></i>Diffusions de ce bulletin
                    </h5>
                    <a href="{{ route('admin.diffusions.create', ['bulletin_alerte_id' => $bulletinAlerte->id]) }}" class="btn btn-sm btn-primary">
                        <i class="bx bx-plus me-1"></i> Nouvelle diffusion
                    </a>
                </div>
                <div class="card-body pt-3">
                    @forelse ($bulletinAlerte->diffusions as $diff)
                        <div class="p-3 mb-3 bg-light rounded border">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bx {{ $diff->canal_icon }} fs-4 text-primary"></i>
                                    <span class="fw-bold">{{ $diff->canal_label }}</span>
                                </div>
                                <span class="badge {{ $diff->statut_badge_class }}">{{ $diff->statut }}</span>
                            </div>

                            <p class="mb-2 small text-dark fst-italic bg-white p-2 rounded border">
                                « {{ $diff->contenu }} »
                            </p>

                            <div class="d-flex align-items-center justify-content-between text-muted small">
                                <div>
                                    <i class="bx bx-user me-1"></i><strong>{{ number_format($diff->destinataires_estimes, 0, ',', ' ') }}</strong> destinataires visés
                                </div>
                                <div>
                                    <i class="bx bx-time me-1"></i>{{ $diff->date_envoi->format('d/m/Y H:i') }}
                                </div>
                            </div>

                            <div class="mt-2 pt-2 border-top d-flex justify-content-end gap-1">
                                <a href="{{ route('admin.diffusions.show', $diff) }}" class="btn btn-xs btn-outline-primary" style="font-size: 0.75rem;">Voir</a>
                                <a href="{{ route('admin.diffusions.edit', $diff) }}" class="btn btn-xs btn-outline-secondary" style="font-size: 0.75rem;">Modifier</a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="bx bx-broadcast bx-lg text-muted mb-2 d-block"></i>
                            <p class="text-muted mb-3">Aucune diffusion par SMS, Email, Push, Sirène ou Affichage n'a encore été programmée pour ce bulletin.</p>
                            <a href="{{ route('admin.diffusions.create', ['bulletin_alerte_id' => $bulletinAlerte->id]) }}" class="btn btn-sm btn-primary">
                                <i class="bx bx-broadcast me-1"></i> Diffuser à la population
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
