@extends('front.layouts.front')

@section('title', $alerte->titre)
@section('description', Str::limit($alerte->message, 150))

@section('content')
    <section class="ve-section py-5">
        <div class="container">
            {{-- Navigation retour --}}
            <div class="mb-4">
                <a href="{{ route('alertes-meteo.index') }}" class="text-decoration-none text-muted fw-semibold">
                    <i class="fa fa-arrow-left me-1"></i> Retour à la liste des alertes
                </a>
            </div>

            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                {{-- En-tête avec niveau d'alerte --}}
                @php
                    $couleur = $alerte->niveauAlerte->couleur ?? 'vert';
                    $headerBg = match($couleur) {
                        'vert' => 'bg-success text-white',
                        'jaune' => 'bg-warning text-dark',
                        'orange' => 'text-white',
                        'rouge' => 'bg-danger text-white',
                        default => 'bg-secondary text-white'
                    };
                    $headerStyle = ($couleur === 'orange') ? 'background-color: #fd7e14 !important;' : '';
                @endphp

                <div class="card-header p-4 {{ $headerBg }}" style="{{ $headerStyle }}">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <span class="badge bg-white text-dark px-3 py-2 fs-6 rounded-pill fw-bold">
                            Level {{ $alerte->niveauAlerte->niveau ?? '-' }} - {{ strtoupper($couleur) }} ({{ $alerte->niveauAlerte->libelle ?? '' }})
                        </span>
                        <span class="fs-6">
                            <i class="fa fa-calendar me-1"></i> Début : {{ $alerte->date_debut->format('d/m/Y à H:i') }}
                            @if ($alerte->date_fin)
                                | Fin : {{ $alerte->date_fin->format('d/m/Y à H:i') }}
                            @endif
                        </span>
                    </div>
                    <h2 class="mt-3 mb-0 text-white fw-bold">{{ $alerte->titre }}</h2>
                </div>

                {{-- Corps du détail --}}
                <div class="card-body p-4 p-md-5">
                    @if ($alerte->source)
                        <div class="mb-4 p-3 bg-light rounded-3 border">
                            <i class="fa fa-info-circle text-primary me-2"></i>
                            <strong>Source officielle :</strong> {{ $alerte->source }}
                        </div>
                    @endif

                    <h4 class="fw-bold mb-3">Bulletin d'information & Consignes :</h4>
                    <div class="fs-5 text-secondary leading-relaxed mb-4">
                        {!! nl2br(e($alerte->message)) !!}
                    </div>

                    {{-- Mesures de prévention associées (prévues et en cours) --}}
                    <div class="mt-5 pt-3 border-top">
                        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                            <div>
                                <h4 class="fw-bold mb-1">
                                    <i class="fa fa-shield text-primary me-2"></i> Mesures de prévention déployées
                                </h4>
                                <small class="text-muted">Mesures d'urgence et dispositifs préventifs activés par les autorités.</small>
                            </div>
                            <span class="badge bg-primary rounded-pill px-3 py-2">
                                {{ $alerte->mesures->count() }} {{ Str::plural('mesure', $alerte->mesures->count()) }} active(s)
                            </span>
                        </div>

                        @if ($alerte->mesures->isNotEmpty())
                            <div class="row g-3 mt-1">
                                @foreach ($alerte->mesures as $mesure)
                                    <div class="col-md-6">
                                        <div class="card h-100 border rounded-3 p-3 shadow-sm bg-light">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <span class="badge {{ $mesure->type_badge_class }} px-2 py-1">
                                                    {{ $mesure->type_label }}
                                                </span>
                                                <span class="badge {{ $mesure->statut_badge_class }} px-2 py-1">
                                                    {{ $mesure->statut_label }}
                                                </span>
                                            </div>
                                            <h5 class="fw-bold text-dark mb-2">{{ $mesure->titre }}</h5>
                                            <p class="text-secondary small mb-3 leading-relaxed">
                                                {{ $mesure->description }}
                                            </p>
                                            <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center small text-muted flex-wrap gap-1">
                                                <span>
                                                    <i class="fa fa-calendar me-1"></i> Depuis le {{ $mesure->date_debut->format('d/m/Y à H:i') }}
                                                </span>
                                                <span>
                                                    <i class="fa fa-user-circle me-1"></i> {{ $mesure->responsable }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-light border text-center py-4 rounded-3 text-muted mt-2">
                                <i class="fa fa-info-circle fa-2x mb-2 text-primary d-block"></i>
                                Aucune mesure d'urgence supplémentaire n'est requise pour cette alerte météo.
                            </div>
                        @endif
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 text-muted small">
                        <span>
                            <i class="fa fa-clock-o me-1"></i> Publié le {{ $alerte->created_at->format('d/m/Y à H:i') }}
                        </span>
                        <a href="{{ route('alertes-meteo.index') }}" class="ve-btn-primary px-4 py-2">
                            Voir toutes les alertes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
