@extends('back.layouts.back')

@section('title', 'Détails de la mesure de prévention')

@section('content')
    <x-page-header title="Détails de la mesure de prévention" :subtitle="$mesurePrevention->titre">
        @slot('actions')
            <a href="{{ route('admin.mesures-prevention.edit', $mesurePrevention) }}" class="btn btn-primary">
                <i class="bx bx-edit me-1"></i> Modifier
            </a>
            <a href="{{ route('admin.mesures-prevention.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        @endslot
    </x-page-header>

    <div class="row g-4">
        {{-- Description et contenu principal --}}
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h5 class="card-title mb-0">{{ $mesurePrevention->titre }}</h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge {{ $mesurePrevention->type_badge_class }} fs-6">
                            {{ $mesurePrevention->type_label }}
                        </span>
                        <span class="badge {{ $mesurePrevention->statut_badge_class }} fs-6">
                            {{ $mesurePrevention->statut_label }}
                        </span>
                    </div>
                </div>
                <div class="card-body mt-3">
                    <h6 class="fw-semibold">Description et consignes d'application :</h6>
                    <div class="p-3 bg-lighter rounded border leading-relaxed">
                        {!! nl2br(e($mesurePrevention->description)) !!}
                    </div>
                </div>
            </div>
        </div>

        {{-- Métadonnées et Alerte météo liée --}}
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Alerte météo associée</h5>
                </div>
                <div class="card-body mt-3">
                    @if ($mesurePrevention->alerteMeteo)
                        @php
                            $alerte = $mesurePrevention->alerteMeteo;
                            $couleur = $alerte->niveauAlerte->couleur ?? 'gris';
                            $badgeStyle = ($couleur === 'orange') ? 'background-color: #fd7e14 !important;' : '';
                            $badgeClass = match ($couleur) {
                                'vert' => 'bg-success',
                                'jaune' => 'bg-warning text-dark',
                                'orange' => 'text-white',
                                'rouge' => 'bg-danger',
                                default => 'bg-secondary',
                            };
                        @endphp
                        <div class="mb-3">
                            <span class="badge {{ $badgeClass }} mb-2" style="{{ $badgeStyle }}">
                                Niveau {{ $alerte->niveauAlerte->niveau ?? '-' }} - {{ strtoupper($couleur) }} ({{ $alerte->niveauAlerte->libelle ?? '' }})
                            </span>
                            <h6 class="fw-bold mb-1">
                                <a href="{{ route('admin.alertes-meteo.show', $alerte) }}" class="text-dark">
                                    {{ $alerte->titre }}
                                </a>
                            </h6>
                            <small class="text-muted d-block">
                                <i class="bx bx-calendar me-1"></i>
                                Du {{ $alerte->date_debut->format('d/m/Y H:i') }}
                                @if ($alerte->date_fin)
                                    au {{ $alerte->date_fin->format('d/m/Y H:i') }}
                                @endif
                            </small>
                        </div>
                        <a href="{{ route('admin.alertes-meteo.show', $alerte) }}" class="btn btn-sm btn-outline-primary w-100">
                            <i class="bx bx-show me-1"></i> Consulter l'alerte
                        </a>
                    @else
                        <p class="text-muted mb-0">Aucune alerte météo associée.</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Informations & Suivi</h5>
                </div>
                <div class="card-body mt-3">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Responsable</span>
                            <span class="text-end">{{ $mesurePrevention->responsable }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Date de début</span>
                            <span>{{ $mesurePrevention->date_debut->format('d/m/Y H:i') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Créée le</span>
                            <span>{{ $mesurePrevention->created_at->format('d/m/Y H:i') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Dernière mise à jour</span>
                            <span>{{ $mesurePrevention->updated_at->format('d/m/Y H:i') }}</span>
                        </li>
                    </ul>

                    <div class="mt-3">
                        <form method="POST"
                              action="{{ route('admin.mesures-prevention.destroy', $mesurePrevention) }}"
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette mesure de prévention ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bx bx-trash me-1"></i> Supprimer cette mesure
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
