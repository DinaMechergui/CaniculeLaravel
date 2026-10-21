@extends('back.layouts.back')
@section('title', 'Détail de l\'alerte : ' . $alerte->titre)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <x-page-header :title="$alerte->titre" subtitle="Suivi du cycle de vie et chronologie des bulletins officiels">
        @slot('actions')
            <div class="d-flex gap-2 flex-wrap">
                @if ($alerte->statut === 'en_cours')
                    <a href="{{ route('admin.bulletins-alerte.create', ['alerte_id' => $alerte->id]) }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Nouveau bulletin de suivi
                    </a>
                @endif
                <a href="{{ route('admin.alertes.edit', $alerte) }}" class="btn btn-outline-secondary">
                    <i class="bx bx-edit me-1"></i> Modifier l'alerte
                </a>
                <a href="{{ route('admin.alertes.index') }}" class="btn btn-outline-secondary">
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

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible shadow-sm mb-4" role="alert">
            <i class="bx bx-error-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Fiche résumé de l'alerte --}}
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 85px;">
                <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Paramètres de l'alerte</h5>
                    <span class="badge bg-label-info">Alerte #{{ $alerte->id }}</span>
                </div>
                <div class="card-body pt-3">
                    @php
                        $niveau = $alerte->niveau_actuel;
                        $badgeClass = match ($niveau) {
                            'rouge' => 'bg-danger text-white',
                            'orange' => 'bg-warning text-dark',
                            'jaune' => 'bg-info text-dark',
                            'vert' => 'bg-success text-white',
                            default => 'bg-secondary text-white',
                        };
                    @endphp

                    <div class="text-center p-3 mb-3 rounded bg-light border">
                        <div class="small text-muted text-uppercase fw-semibold mb-1">Niveau de vigilance actuel</div>
                        <span class="badge {{ $badgeClass }} px-3 py-2 text-uppercase fs-6 shadow-xs">
                            {{ $niveau === 'gris' ? 'Aucun bulletin' : 'Vigilance ' . $niveau }}
                        </span>
                        @if ($alerte->statut === 'en_cours')
                            <div class="mt-2 text-warning small fw-semibold">
                                <i class="bx bx-radio-circle-marked bx-burst"></i> Événement en cours d'évolution
                            </div>
                        @else
                            <div class="mt-2 text-muted small fw-semibold">
                                <i class="bx bx-check-circle"></i> Alerte levée et terminée
                            </div>
                        @endif
                    </div>

                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="bx bx-category me-1"></i>Type d'aléa :</span>
                            <span class="fw-semibold">{{ $alerte->type_label }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="bx bx-map-pin me-1"></i>Zone touchée :</span>
                            <span class="fw-semibold text-end">{{ $alerte->zone_concernee }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="bx bx-buildings me-1"></i>Source :</span>
                            <span class="fw-semibold text-end">{{ $alerte->source ?? 'Non précisée' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="bx bx-calendar-event me-1"></i>Date de début :</span>
                            <span class="fw-semibold">{{ $alerte->date_debut->format('d/m/Y à H:i') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="bx bx-calendar-check me-1"></i>Date de fin :</span>
                            <span class="fw-semibold">{{ $alerte->date_fin ? $alerte->date_fin->format('d/m/Y à H:i') : 'Indéterminée (en cours)' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="bx bx-file me-1"></i>Total bulletins :</span>
                            <span class="badge bg-label-primary">{{ $alerte->bulletins->count() }}</span>
                        </li>
                    </ul>

                    <div class="border-top pt-3">
                        <h6 class="fw-semibold mb-2 text-muted small text-uppercase">Description générale :</h6>
                        <p class="text-muted mb-0 small" style="white-space: pre-line;">{{ $alerte->description }}</p>
                    </div>

                    @if ($alerte->statut === 'terminee')
                        <div class="alert alert-secondary mt-3 mb-0 py-2 small" role="alert">
                            <i class="bx bx-info-circle me-1"></i> Cette alerte a été levée. Aucun nouveau bulletin ne peut y être ajouté.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Chronologie des bulletins de l'alerte --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-git-branch text-primary me-2"></i>Historique d'évolution de l'alerte
                    </h5>
                    @if ($alerte->statut === 'en_cours')
                        <a href="{{ route('admin.bulletins-alerte.create', ['alerte_id' => $alerte->id]) }}" class="btn btn-sm btn-primary">
                            <i class="bx bx-plus me-1"></i> Ajouter un bulletin
                        </a>
                    @endif
                </div>

                <div class="card-body pt-4">
                    @forelse ($alerte->bulletins as $bulletin)
                        @php
                            $bBadgeClass = match ($bulletin->niveau) {
                                'rouge' => 'bg-danger text-white',
                                'orange' => 'bg-warning text-dark',
                                'jaune' => 'bg-info text-dark',
                                'vert' => 'bg-success text-white',
                                default => 'bg-secondary text-white',
                            };
                            $isDernier = $loop->first;
                        @endphp

                        <div class="d-flex mb-4">
                            {{-- Indicateur visuel de la chronologie --}}
                            <div class="d-flex flex-column align-items-center me-3" style="min-width: 45px;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-xs"
                                     style="width: 38px; height: 38px; background-color: {{ match($bulletin->niveau) { 'rouge'=>'#ff3e1d', 'orange'=>'#ffab00', 'jaune'=>'#03c3ec', default=>'#71dd37' } }}; color: white;">
                                    <i class="bx {{ match($bulletin->evolution) { 'aggravation'=>'bx-trending-up', 'stable'=>'bx-minus', 'amelioration'=>'bx-trending-down', default=>'bx-check-double' } }} fs-5"></i>
                                </div>
                                @if (!$loop->last)
                                    <div class="flex-grow-1 border-start border-2 border-secondary opacity-25 my-1" style="min-height: 40px;"></div>
                                @endif
                            </div>

                            {{-- Contenu du bulletin --}}
                            <div class="card flex-grow-1 shadow-xs border {{ $isDernier ? 'border-primary' : 'border-light' }}">
                                <div class="card-header bg-transparent border-bottom py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge {{ $bBadgeClass }} text-uppercase fw-bold">
                                            {{ $bulletin->niveau_label }}
                                        </span>
                                        <span class="badge bg-label-dark">
                                            <i class="bx {{ $bulletin->evolution_icon }} me-1"></i>{{ $bulletin->evolution_label }}
                                        </span>
                                        @if ($bulletin->temperature_max !== null)
                                            <span class="badge bg-label-danger">
                                                <i class="bx bxs-thermometer me-1"></i>{{ $bulletin->temperature_max }}°C max
                                            </span>
                                        @endif
                                        @if ($isDernier)
                                            <span class="badge bg-primary text-white">Dernier bulletin officiel</span>
                                        @endif
                                    </div>

                                    <div class="small text-muted">
                                        <i class="bx bx-time-five me-1"></i>{{ $bulletin->date_publication->format('d/m/Y à H:i') }}
                                    </div>
                                </div>

                                <div class="card-body py-3">
                                    <div class="mb-3">
                                        <h6 class="fw-semibold text-dark mb-1">Message d'alerte :</h6>
                                        <p class="mb-0 text-secondary" style="white-space: pre-line;">{{ $bulletin->message }}</p>
                                    </div>

                                    @if ($bulletin->conduite_a_tenir)
                                        <div class="p-3 bg-light rounded border-start border-3 border-warning mb-3">
                                            <h6 class="fw-semibold text-warning-emphasis mb-1">
                                                <i class="bx bx-shield-quarter me-1"></i>Conduite à tenir & Conseils immédiats :
                                            </h6>
                                            <p class="mb-0 small text-muted" style="white-space: pre-line;">{{ $bulletin->conduite_a_tenir }}</p>
                                        </div>
                                    @endif

                                    {{-- Diffusions attachées à ce bulletin --}}
                                    <div class="mt-3 pt-2 border-top">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="small fw-semibold text-muted text-uppercase">
                                                <i class="bx bx-broadcast me-1"></i>Canaux de diffusion activés ({{ $bulletin->diffusions->count() }}) :
                                            </span>
                                            <a href="{{ route('admin.diffusions.create', ['bulletin_alerte_id' => $bulletin->id]) }}" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 0.75rem;">
                                                <i class="bx bx-plus me-1"></i>Programmer un canal
                                            </a>
                                        </div>

                                        @if ($bulletin->diffusions->isNotEmpty())
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach ($bulletin->diffusions as $diff)
                                                    <span class="badge {{ $diff->statut_badge_class }} d-inline-flex align-items-center gap-1 py-1 px-2" title="{{ $diff->contenu }}">
                                                        <i class="bx {{ $diff->canal_icon }}"></i>
                                                        {{ $diff->canal_label }}
                                                        ({{ number_format($diff->destinataires_estimes, 0, ',', ' ') }} pers.)
                                                        <span class="opacity-75">· {{ $diff->statut }}</span>
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted small fst-italic">Aucune diffusion enregistrée pour ce bulletin.</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="card-footer bg-transparent border-top py-2 d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.bulletins-alerte.show', $bulletin) }}" class="btn btn-xs btn-outline-primary" style="font-size: 0.8rem;">
                                        <i class="bx bx-show me-1"></i>Détail
                                    </a>
                                    <a href="{{ route('admin.bulletins-alerte.edit', $bulletin) }}" class="btn btn-xs btn-outline-secondary" style="font-size: 0.8rem;">
                                        <i class="bx bx-edit me-1"></i>Modifier
                                    </a>
                                    <form action="{{ route('admin.bulletins-alerte.destroy', $bulletin) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce bulletin ?');" novalidate>
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-outline-danger" style="font-size: 0.8rem;" @disabled($bulletin->diffusions->isNotEmpty())>
                                            <i class="bx bx-trash me-1"></i>Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="bx bx-file-blank bx-lg text-muted mb-2 d-block"></i>
                            <h6 class="text-muted mb-2">Aucun bulletin de suivi n'a encore été publié pour cette alerte.</h6>
                            <p class="text-muted small mb-3">Chaque bulletin permet de faire évoluer le niveau de vigilance (jaune, orange, rouge, ou levée).</p>
                            @if ($alerte->statut === 'en_cours')
                                <a href="{{ route('admin.bulletins-alerte.create', ['alerte_id' => $alerte->id]) }}" class="btn btn-primary">
                                    <i class="bx bx-plus me-1"></i> Publier le premier bulletin
                                </a>
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
