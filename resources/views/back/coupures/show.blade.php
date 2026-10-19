@extends('back.layouts.back')
@section('title', 'Détail de la coupure')

@section('content')
    <x-page-header :title="'Coupure : ' . ($coupure->quartier?->nom ?? 'Détail')" subtitle="Détail et suivi de l'incident électrique">
        @slot('actions')
            <a href="{{ route('admin.coupures.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
            <a href="{{ route('admin.coupures.edit', $coupure) }}" class="btn btn-primary">
                <i class="bx bx-edit me-1"></i> Modifier
            </a>
        @endslot
    </x-page-header>

    @if (!empty($prediction))
        <div class="card mb-4 border border-primary shadow-sm">
            <div class="card-header bg-label-primary py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h6 class="mb-0 text-primary fw-bold">
                    <i class="bx bx-bulb me-1"></i> Prédiction du temps de fin de coupure (IA & Réseau STEG)
                </h6>
                <span class="badge bg-primary">{{ $prediction['confiance'] }}</span>
            </div>
            <div class="card-body py-3">
                <div class="row align-items-center g-3">
                    <div class="col-md-4 text-center border-end">
                        <span class="text-muted d-block small">Fin de coupure estimée</span>
                        <span class="fs-4 fw-bold text-primary">{{ $prediction['date_fin_affichage'] }}</span>
                        <div class="mt-1">
                            <span class="badge bg-label-info">Durée calculée : ~{{ $prediction['duree_formatee'] }}</span>
                        </div>
                    </div>
                    <div class="col-md-8 ps-md-4">
                        <div class="fw-semibold text-dark mb-1">{{ $prediction['explication'] }}</div>
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            @foreach ($prediction['facteurs'] as $facteur)
                                <span class="badge bg-label-secondary" style="font-size: 0.75rem;">{{ $facteur }}</span>
                            @endforeach
                        </div>
                        <small class="text-muted font-monospace" style="font-size: 0.75rem;">
                            <i class="bx bx-chip me-1"></i> Moteur : {{ $prediction['source'] }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Informations de la coupure</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-sm-3 text-muted">Quartier</div>
                <div class="col-sm-9">
                    <a href="{{ route('admin.quartiers.show', $coupure->quartier) }}" class="fw-semibold">
                        {{ $coupure->quartier?->nom ?? '—' }} ({{ $coupure->quartier?->ville ?? '—' }})
                    </a>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-3 text-muted">Type d'incident</div>
                <div class="col-sm-9">
                    @php
                        $typeBadge = match($coupure->type) {
                            'delestage' => 'badge bg-label-warning',
                            'panne' => 'badge bg-label-danger',
                            'surcharge' => 'badge bg-label-info',
                            default => 'badge bg-label-secondary',
                        };
                    @endphp
                    <span class="{{ $typeBadge }} fs-6">{{ ucfirst($coupure->type) }}</span>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-3 text-muted">Statut actuel</div>
                <div class="col-sm-9">
                    <span class="badge {{ ['en_cours' => 'bg-danger', 'prevue' => 'bg-warning text-dark', 'terminee' => 'bg-success'][$coupure->statut] }} fs-6">
                        {{ str_replace('_', ' ', ucfirst($coupure->statut)) }}
                    </span>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-3 text-muted">Date et heure de début</div>
                <div class="col-sm-9">{{ $coupure->date_debut->format('d/m/Y H:i') }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-3 text-muted">Date et heure de fin confirmée</div>
                <div class="col-sm-9">
                    @if ($coupure->date_fin)
                        <span class="text-success fw-semibold">{{ $coupure->date_fin->format('d/m/Y H:i') }}</span>
                    @else
                        <span class="text-muted font-italic">En attente de rétablissement (voir estimation IA ci-dessus)</span>
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="col-sm-3 text-muted">Description</div>
                <div class="col-sm-9">{{ $coupure->description ?? 'Aucune description spécifique renseignée.' }}</div>
            </div>
        </div>
    </div>
@endsection