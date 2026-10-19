@extends('back.layouts.back')

@section('title', 'Détails de l\'alerte météo')

@section('content')
    <x-page-header title="Détails de l'alerte météo" :subtitle="$alerteMeteo->titre">
        @slot('actions')
            <a href="{{ route('admin.alertes-meteo.edit', $alerteMeteo) }}" class="btn btn-primary">
                <i class="bx bx-edit me-1"></i> Modifier
            </a>
            <a href="{{ route('admin.alertes-meteo.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        @endslot
    </x-page-header>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">{{ $alerteMeteo->titre }}</h5>
                    @if ($alerteMeteo->niveauAlerte)
                        @switch($alerteMeteo->niveauAlerte->couleur)
                            @case('vert')
                                <span class="badge bg-success fs-6">Vert (Niveau {{ $alerteMeteo->niveauAlerte->niveau }})</span>
                                @break
                            @case('jaune')
                                <span class="badge bg-warning text-dark fs-6">Jaune (Niveau {{ $alerteMeteo->niveauAlerte->niveau }})</span>
                                @break
                            @case('orange')
                                <span class="badge text-white fs-6" style="background-color: #fd7e14 !important;">Orange (Niveau {{ $alerteMeteo->niveauAlerte->niveau }})</span>
                                @break
                            @case('rouge')
                                <span class="badge bg-danger fs-6">Rouge (Niveau {{ $alerteMeteo->niveauAlerte->niveau }})</span>
                                @break
                            @default
                                <span class="badge bg-secondary fs-6">{{ $alerteMeteo->niveauAlerte->libelle }}</span>
                        @endswitch
                    @endif
                </div>
                <div class="card-body mt-3">
                    <h6 class="fw-semibold">Message de l'alerte :</h6>
                    <div class="p-3 bg-lighter rounded border">
                        {!! nl2br(e($alerteMeteo->message)) !!}
                    </div>
                </div>
            </div>

            {{-- Mesures de prévention rattachées à cette alerte --}}
            <div class="card">
                <div class="card-header bg-light d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-shield-quarter me-1 text-primary"></i>
                        Mesures de prévention associées ({{ $alerteMeteo->mesures->count() }})
                    </h5>
                    <a href="{{ route('admin.mesures-prevention.create', ['alerte_meteo_id' => $alerteMeteo->id]) }}" class="btn btn-sm btn-primary">
                        <i class="bx bx-plus me-1"></i> Ajouter une mesure
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Titre</th>
                                    <th>Type</th>
                                    <th>Statut</th>
                                    <th>Responsable</th>
                                    <th>Date début</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($alerteMeteo->mesures as $mesure)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.mesures-prevention.show', $mesure) }}" class="fw-semibold text-dark">
                                                {{ $mesure->titre }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge {{ $mesure->type_badge_class }}">
                                                {{ $mesure->type_label }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $mesure->statut_badge_class }}">
                                                {{ $mesure->statut_label }}
                                            </span>
                                        </td>
                                        <td>{{ $mesure->responsable }}</td>
                                        <td>{{ $mesure->date_debut->format('d/m/Y H:i') }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.mesures-prevention.show', $mesure) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="bx bx-show"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">
                                            Aucune mesure de prévention enregistrée pour cette alerte.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Informations & Dates</h5>
                </div>
                <div class="card-body mt-3">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Niveau d'alerte</span>
                            <span>{{ $alerteMeteo->niveauAlerte->libelle ?? 'Non spécifié' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Date de début</span>
                            <span>{{ $alerteMeteo->date_debut->format('d/m/Y H:i') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Date de fin</span>
                            <span>{{ $alerteMeteo->date_fin ? $alerteMeteo->date_fin->format('d/m/Y H:i') : 'Non définie' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Source</span>
                            <span>{{ $alerteMeteo->source ?? 'Non renseignée' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Créée le</span>
                            <span>{{ $alerteMeteo->created_at->format('d/m/Y H:i') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
