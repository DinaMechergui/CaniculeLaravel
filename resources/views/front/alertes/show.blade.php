@extends('front.layouts.front')

@section('title', $alerte->titre . ' — Suivi & Consignes')
@section('description', 'Suivi chronologique des bulletins d\'alerte et consignes officielles de sécurité.')

@section('content')
    <section class="ve-section pf-section">
        <div class="container">
            {{-- Navigation retour --}}
            <div class="mb-4">
                <a href="{{ route('alertes.index') }}" class="text-decoration-none text-muted fw-semibold">
                    <i class="fa fa-arrow-left me-1"></i> Retour à la liste des alertes
                </a>
            </div>

            @php
                $niveau = $alerte->niveau_actuel;
                $badgeBg = match($niveau) {
                    'rouge' => 'bg-danger text-white',
                    'orange' => 'bg-warning text-dark',
                    'jaune' => 'bg-warning text-dark',
                    'vert' => 'bg-success text-white',
                    default => 'bg-secondary text-white',
                };
            @endphp

            {{-- Carte d'en-tête de l'alerte --}}
            <div class="card shadow-sm border-0 rounded-3 mb-5 overflow-hidden" style="background: #ffffff;">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        <span class="badge {{ $badgeBg }} px-4 py-2 fs-6 text-uppercase fw-bold shadow-xs">
                            <i class="fa fa-shield me-1"></i> Vigilance {{ $niveau }} (Niveau actuel)
                        </span>

                        <span class="badge bg-light text-dark border px-3 py-2 fs-6">
                            <i class="fa fa-tag text-primary me-1"></i> {{ $alerte->type_label }}
                        </span>
                    </div>

                    <h1 class="h2 fw-bold text-dark mb-3">{{ $alerte->titre }}</h1>

                    {{-- Bannière Zone géographique mise en valeur --}}
                    <div class="p-3 rounded-3 mb-4 d-flex align-items-center" style="background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%); border: 1px solid #fee2e2; border-left: 5px solid #dc2626; box-shadow: 0 2px 4px rgba(220, 38, 38, 0.05);">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle me-3 flex-shrink-0" style="width: 42px; height: 42px; background: #fee2e2; color: #dc2626;">
                            <i class="fa fa-map-marker fs-5"></i>
                        </span>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="text-uppercase fw-bold text-danger" style="font-size: 0.72rem; letter-spacing: 0.8px;">
                                    <i class="fa fa-crosshairs me-1"></i>Périmètre géographique sous alerte
                                </span>
                                <span class="badge bg-danger text-white px-2 py-0.5" style="font-size: 0.65rem;">
                                    Zone à risque
                                </span>
                            </div>
                            <div class="h5 fw-bold text-dark mb-0" style="line-height: 1.4;">
                                {{ $alerte->zone_concernee }}
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 text-muted small mb-4 py-3 border-top border-bottom">
                        <div class="col-sm-6 col-md-4">
                            <i class="fa fa-calendar-check-o text-primary me-1"></i>
                            <strong>Début :</strong> {{ $alerte->date_debut->format('d/m/Y H:i') }}
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <i class="fa fa-clock-o text-info me-1"></i>
                            <strong>Fin prévue :</strong> {{ $alerte->date_fin ? $alerte->date_fin->format('d/m/Y H:i') : 'Indéterminée (en cours)' }}
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <i class="fa fa-building text-secondary me-1"></i>
                            <strong>Source :</strong> {{ $alerte->source ?? 'Autorités officielles' }}
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark mb-2">Description de la situation :</h5>
                    <p class="text-secondary mb-0" style="white-space: pre-line; line-height: 1.7;">
                        {{ $alerte->description }}
                    </p>
                </div>
            </div>

            {{-- Chronologie verticale des bulletins de suivi --}}
            <div class="mb-5">
                <div class="text-center mb-4">
                    <span class="ve-section-tag">Cycle de vie & Historique</span>
                    <h3 class="fw-bold">Chronologie <span>des bulletins officiels</span></h3>
                    <p class="text-muted">
                        Évolution pas à pas de l'événement et consignes diffusées à la population.
                    </p>
                </div>

                <div class="timeline-vertical position-relative py-3">
                    @forelse ($alerte->bulletins as $bulletin)
                        @php
                            $bNiveau = $bulletin->niveau;
                            $bBadgeBg = match($bNiveau) {
                                'rouge' => '#dc3545',
                                'orange' => '#fd7e14',
                                'jaune' => '#ffc107',
                                'vert' => '#198754',
                                default => '#6c757d',
                            };
                            $bBadgeClass = match($bNiveau) {
                                'rouge' => 'bg-danger text-white',
                                'orange' => 'bg-warning text-dark',
                                'jaune' => 'bg-warning text-dark',
                                'vert' => 'bg-success text-white',
                                default => 'bg-secondary text-white',
                            };
                            $iconClass = match($bulletin->evolution) {
                                'aggravation' => 'fa-arrow-up text-danger',
                                'stable' => 'fa-minus text-info',
                                'amelioration' => 'fa-arrow-down text-success',
                                'levee' => 'fa-check text-secondary',
                                default => 'fa-info',
                            };
                        @endphp

                        <div class="row g-0 mb-4 align-items-stretch">
                            {{-- Date & Horodatage (colonne gauche) --}}
                            <div class="col-md-3 text-md-end pe-md-4 pt-2 mb-2 mb-md-0">
                                <div class="fw-bold text-dark fs-6">{{ $bulletin->date_publication->format('d/m/Y') }}</div>
                                <div class="text-muted small">
                                    <i class="fa fa-clock-o me-1"></i>{{ $bulletin->date_publication->format('H:i') }}
                                </div>
                                <span class="badge bg-light text-dark border mt-2">
                                    Bulletin #{{ $loop->iteration }}
                                </span>
                            </div>

                            {{-- Axe central avec puce colorée --}}
                            <div class="col-auto d-none d-md-flex flex-column align-items-center me-4">
                                <div class="rounded-circle shadow-sm d-flex align-items-center justify-content-center text-white"
                                     style="width: 38px; height: 38px; background-color: {{ $bBadgeBg }}; z-index: 2;">
                                    <i class="fa {{ $iconClass }}"></i>
                                </div>
                                @if (!$loop->last)
                                    <div class="h-100 border-start border-2 border-secondary opacity-25 my-1" style="min-height: 50px;"></div>
                                @endif
                            </div>

                            {{-- Contenu du bulletin (colonne droite) --}}
                            <div class="col-md-8">
                                <div class="card shadow-sm border-0 rounded-3 p-4 bg-white">
                                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="badge {{ $bBadgeClass }} text-uppercase fw-bold px-3 py-2">
                                                {{ $bulletin->niveau_label }}
                                            </span>
                                            <span class="badge bg-light text-dark border">
                                                <i class="fa {{ $iconClass }} me-1"></i>{{ $bulletin->evolution_label }}
                                            </span>
                                            @if ($bulletin->temperature_max !== null)
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">
                                                    <i class="fa fa-thermometer-half me-1"></i>{{ $bulletin->temperature_max }}°C max
                                                </span>
                                            @endif
                                        </div>

                                        @if ($loop->last)
                                            <span class="badge bg-success text-white">Dernière mise à jour</span>
                                        @endif
                                    </div>

                                    <h5 class="fw-bold text-dark mb-2">Message officiel :</h5>
                                    <p class="text-secondary mb-3" style="white-space: pre-line; line-height: 1.6;">
                                        {{ $bulletin->message }}
                                    </p>

                                    @if ($bulletin->conduite_a_tenir)
                                        <div class="p-3 bg-light rounded-3 border-start border-4 border-warning mb-3">
                                            <h6 class="fw-bold text-dark mb-1">
                                                <i class="fa fa-shield text-warning me-1"></i>Consignes & Conduite à tenir :
                                            </h6>
                                            <p class="mb-0 small text-secondary" style="white-space: pre-line; line-height: 1.6;">
                                                {{ $bulletin->conduite_a_tenir }}
                                            </p>
                                        </div>
                                    @endif

                                    {{-- Canaux de diffusion dont le statut est "envoyee" --}}
                                    @php
                                        $diffusionsEnvoyees = $bulletin->diffusions->where('statut', 'envoyee');
                                    @endphp
                                    <div class="pt-3 border-top mt-2">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="small fw-semibold text-muted text-uppercase">
                                                <i class="fa fa-bullhorn text-primary me-1"></i>Diffusé à la population par :
                                            </span>

                                            @forelse ($diffusionsEnvoyees as $diff)
                                                @php
                                                    $canalIcon = match($diff->canal) {
                                                        'sms' => 'fa-commenting-o',
                                                        'email' => 'fa-envelope-o',
                                                        'notification_push' => 'fa-bell-o',
                                                        'sirene' => 'fa-bullhorn',
                                                        'affichage' => 'fa-desktop',
                                                        default => 'fa-broadcast-tower',
                                                    };
                                                @endphp
                                                <span class="badge bg-light text-dark border px-2 py-1 shadow-xs" title="Envoyé à {{ number_format($diff->destinataires_estimes, 0, ',', ' ') }} citoyens">
                                                    <i class="fa {{ $canalIcon }} text-primary me-1"></i>
                                                    {{ $diff->canal_label }}
                                                </span>
                                            @empty
                                                <span class="small text-muted fst-italic">Publication sur le portail citoyen uniquement</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <p class="text-muted">Aucun bulletin de suivi n'est encore disponible.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Bouton de retour bas de page --}}
            <div class="text-center pt-3">
                <a href="{{ route('alertes.index') }}" class="ve-btn-primary px-4 py-2">
                    <i class="fa fa-arrow-left me-1"></i> Retour aux alertes
                </a>
            </div>
        </div>
    </section>
@endsection
