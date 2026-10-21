@extends('back.layouts.back')
@section('title', 'Détail de la diffusion #' . $diffusion->id)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <x-page-header :title="'Diffusion #' . $diffusion->id . ' (' . $diffusion->canal_label . ')'" subtitle="Suivi de la transmission d'alerte à la population">
        @slot('actions')
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.diffusions.edit', $diffusion) }}" class="btn btn-outline-secondary">
                    <i class="bx bx-edit me-1"></i> Modifier
                </a>
                <a href="{{ route('admin.bulletins-alerte.show', $diffusion->bulletinAlerte) }}" class="btn btn-outline-info">
                    <i class="bx bx-file me-1"></i> Bulletin lié
                </a>
                <a href="{{ route('admin.diffusions.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Retour à la liste
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

    <div class="row g-4">
        {{-- Fiche de la diffusion --}}
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Paramètres et statut de l'envoi</h5>
                    <span class="badge {{ $diffusion->statut_badge_class }}">{{ $diffusion->statut }}</span>
                </div>
                <div class="card-body pt-3">
                    <div class="d-flex align-items-center gap-3 p-3 mb-3 bg-light rounded border">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                            <i class="bx {{ $diffusion->canal_icon }} fs-2"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">{{ $diffusion->canal_label }}</h5>
                            <span class="text-muted small">
                                <i class="bx bx-calendar me-1"></i>Date d'envoi : {{ $diffusion->date_envoi->format('d/m/Y à H:i') }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-semibold text-muted small text-uppercase mb-2">Message court transmis (SMS / Notification / Affichage) :</h6>
                        <div class="p-3 bg-light rounded border fs-6 text-dark font-monospace" style="white-space: pre-line;">
                            {{ $diffusion->contenu }}
                        </div>
                        <div class="text-end small text-muted mt-1">{{ strlen($diffusion->contenu) }} / 160 caractères</div>
                    </div>

                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="bx bx-group me-1"></i>Destinataires visés :</span>
                            <span class="fw-bold text-primary fs-6">{{ number_format($diffusion->destinataires_estimes, 0, ',', ' ') }} citoyens</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="bx bx-check-shield me-1"></i>Statut de transmission :</span>
                            <span class="badge {{ $diffusion->statut_badge_class }}">{{ $diffusion->statut }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Contexte : bulletin et alerte liés --}}
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="card-title mb-0">Bulletin & Alerte rattachés</h5>
                </div>
                <div class="card-body pt-3">
                    @php
                        $bulletin = $diffusion->bulletinAlerte;
                        $alerte = $bulletin->alerte;
                    @endphp

                    <div class="p-3 mb-3 rounded border bg-light">
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Niveau officiel du bulletin</div>
                        <span class="badge {{ $bulletin->niveau_badge_class }} text-uppercase fw-bold fs-6">
                            {{ $bulletin->niveau_label }}
                        </span>
                        <span class="badge bg-label-dark ms-1">
                            {{ $bulletin->evolution_label }}
                        </span>
                        <div class="small text-muted mt-2">
                            Publié le : {{ $bulletin->date_publication->format('d/m/Y à H:i') }}
                        </div>
                    </div>

                    <h6 class="fw-semibold text-dark mb-1">{{ $alerte->titre }}</h6>
                    <p class="small text-muted mb-3">
                        <i class="bx bx-map-pin me-1"></i>Zone : {{ $alerte->zone_concernee }}
                        <br><i class="bx bx-category me-1"></i>Type : {{ $alerte->type_label }}
                    </p>

                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.bulletins-alerte.show', $bulletin) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bx bx-file me-1"></i> Voir le bulletin complet
                        </a>
                        <a href="{{ route('admin.alertes.show', $alerte) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bx bx-git-branch me-1"></i> Voir la chronologie de l'alerte
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
