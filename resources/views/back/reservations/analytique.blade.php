@extends('back.layouts.back')

@section('title', 'Analyse IA des réservations')

@section('content')
    <x-page-header
        title="Analyse IA"
        subtitle="Prévisions de fréquentation et indicateurs du module Réservations">
        @slot('actions')
            <a href="{{ route('admin.reservations.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
            <form method="POST" action="{{ route('admin.reservations.analytique.generer') }}" class="d-inline" novalidate>
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="bx bx-refresh me-1"></i> Recalculer
                </button>
            </form>
        @endslot
    </x-page-header>

    @php
        $dernier = $indicateurs->first();
        $totalAfficher = $totalResas14j ?? ($dernier?->total_reservations ?? 0);
        $presenceAfficher = $presencePonderee ?? ($dernier?->taux_presence ?? 0);
        $noShowAfficher = $noShowPondere ?? ($dernier?->taux_no_show ?? 0);
        $scoreAfficher = $scoreMoyenPondere ?? ($dernier?->score_risque_moyen ?? 25.0);
    @endphp

    {{-- Indicateurs consolidés (14 jours) --}}
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card text-center h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small fw-semibold mb-1">Réservations (14 derniers jours)</div>
                    <h3 class="mb-1 fw-bold text-dark">{{ $totalAfficher }}</h3>
                    <small class="text-muted">Dernier jour : {{ $dernier?->total_reservations ?? 0 }} résa(s)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card text-center h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small fw-semibold mb-1">Taux de présence global</div>
                    <h3 class="mb-1 fw-bold text-success">{{ number_format($presenceAfficher, 2) }}%</h3>
                    <small class="text-muted">Moyenne sur la période</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card text-center h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small fw-semibold mb-1">Taux de non-présentation</div>
                    <h3 class="mb-1 fw-bold text-danger">{{ number_format($noShowAfficher, 2) }}%</h3>
                    <small class="text-muted">Non honorées / expirées</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card text-center h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small fw-semibold mb-1">Score de risque moyen (IA)</div>
                    <h3 class="mb-1 fw-bold text-primary">{{ number_format($scoreAfficher, 2) }}%</h3>
                    <small class="text-muted">Calculé par le modèle IA</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Prévisions --}}
        <div class="col-lg-7 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Prévisions de fréquentation à venir</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Point</th>
                                <th class="text-end">Prévu</th>
                                <th class="text-end">Capacité</th>
                                <th class="text-end">Taux</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($previsions as $prevision)
                                <tr>
                                    <td>{{ $prevision->date?->format('d/m/Y') }}</td>
                                    <td>{{ $prevision->pointFraicheur?->nom ?? '—' }}</td>
                                    <td class="text-end">{{ $prevision->reservations_prevues }}</td>
                                    <td class="text-end">{{ $prevision->capacite }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-{{ (float) $prevision->taux_remplissage >= 80 ? 'danger' : ((float) $prevision->taux_remplissage >= 50 ? 'warning' : 'success') }}">
                                            {{ $prevision->taux_remplissage }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Aucune prévision. Cliquez sur « Recalculer ».
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Historique des indicateurs globaux --}}
        <div class="col-lg-5 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Indicateurs globaux (14 derniers jours)</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th class="text-end">Résa.</th>
                                <th class="text-end">Présence</th>
                                <th class="text-end">No-show</th>
                                <th class="text-end">Annul.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($indicateurs as $ligne)
                                <tr>
                                    <td>{{ $ligne->date?->format('d/m/Y') }}</td>
                                    <td class="text-end">{{ $ligne->total_reservations }}</td>
                                    <td class="text-end">{{ $ligne->taux_presence }}%</td>
                                    <td class="text-end text-danger">{{ $ligne->taux_no_show }}%</td>
                                    <td class="text-end">{{ $ligne->taux_annulation }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        Aucun indicateur. Cliquez sur « Recalculer ».
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
