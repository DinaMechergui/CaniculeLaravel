@extends('front.layouts.front')

@section('title', 'Réservation ' . $reservation->code)
@section('description', 'Confirmation et suivi de votre réservation de créneau.')

@section('content')
    <section class="ve-section pf-section">
        <div class="container">
            {{-- Fil d'Ariane --}}
            <nav class="pf-breadcrumb" aria-label="Fil d'Ariane">
                <a href="{{ route('front.home') }}">Accueil</a>
                <span>/</span>
                <a href="{{ route('points-fraicheur.index') }}">Points de fraîcheur</a>
                <span>/</span>
                <strong>Réservation {{ $reservation->code }}</strong>
            </nav>

            <div class="ve-section-header text-center">
                <span class="ve-section-tag">Réservation</span>
                <h2>Votre réservation est <span>enregistrée</span></h2>
                <p>Conservez précieusement le code ci-dessous pour suivre votre réservation.</p>
            </div>

            <div style="max-width:720px;margin:0 auto;">
                <div class="pf-detail-card wow fadeInUp">
                    {{-- Code de suivi --}}
                    <div class="text-center" style="margin-bottom:24px;">
                        <span class="badge badge-{{ $reservation->statut_badge }}" style="font-size:0.95rem;">
                            {{ $reservation->statut_label }}
                        </span>
                        <h3 style="margin:10px 0 0;letter-spacing:3px;">{{ $reservation->code }}</h3>
                        <small style="color:#666;">Code de réservation</small>

                        {{-- QR code menant à la validation d'arrivée (SVG : sans GD). --}}
                        <div style="margin-top:14px;">
                            <img src="{{ route('reservations.qr', $reservation->code) }}"
                                 alt="QR code de la réservation {{ $reservation->code }}"
                                 width="160" height="160">
                            <div><small style="color:#666;">Scannez ce code à l'arrivée pour valider votre venue.</small></div>
                        </div>
                    </div>

                    <ul class="pf-detail-list">
                        <li>
                            <i class="fa fa-map-marker" aria-hidden="true"></i>
                            <div>
                                <strong>Lieu</strong>
                                <span>
                                    <a href="{{ route('points-fraicheur.show', $reservation->pointFraicheur) }}">
                                        {{ $reservation->pointFraicheur?->nom ?? '—' }}
                                    </a>
                                </span>
                            </div>
                        </li>
                        <li>
                            <i class="fa fa-calendar" aria-hidden="true"></i>
                            <div>
                                <strong>Date</strong>
                                <span>{{ $reservation->date_reservation?->format('d/m/Y') }}</span>
                            </div>
                        </li>
                        <li>
                            <i class="fa fa-clock-o" aria-hidden="true"></i>
                            <div>
                                <strong>Créneau</strong>
                                <span>{{ $reservation->heure_debut_courte }} – {{ $reservation->heure_fin_courte }}</span>
                            </div>
                        </li>
                        <li>
                            <i class="fa fa-users" aria-hidden="true"></i>
                            <div>
                                <strong>Nombre de personnes</strong>
                                <span>{{ $reservation->nombre_personnes }}</span>
                            </div>
                        </li>
                        <li>
                            <i class="fa fa-user" aria-hidden="true"></i>
                            <div>
                                <strong>Au nom de</strong>
                                <span>{{ $reservation->nom }} ({{ $reservation->email }})</span>
                            </div>
                        </li>
                        @if ($reservation->besoins_specifiques)
                            <li>
                                <i class="fa fa-heartbeat" aria-hidden="true"></i>
                                <div>
                                    <strong>Besoins spécifiques</strong>
                                    <span>{{ $reservation->besoins_specifiques }}</span>
                                </div>
                            </li>
                        @endif
                    </ul>

                    <div class="pf-detail-actions text-center" style="margin-top:20px;">
                        <button type="button" class="ve-btn-primary" onclick="window.print()">
                            <i class="fa fa-print" aria-hidden="true"></i> Imprimer
                        </button>

                        @if (in_array($reservation->statut, [
                            \App\Models\Reservation::STATUT_EN_ATTENTE,
                            \App\Models\Reservation::STATUT_CONFIRMEE,
                            \App\Models\Reservation::STATUT_LISTE_ATTENTE,
                        ], true) && ! ($reservation->date_reservation?->copy()->setTimeFromTimeString($reservation->heure_fin_courte)?->isPast()))
                            <form method="POST"
                                  action="{{ route('reservations.annuler', $reservation->code) }}"
                                  style="display:inline-block;"
                                  onsubmit="return confirm('Confirmer l\'annulation de cette réservation ?');">
                                @csrf
                                <button type="submit" class="ve-btn-ghost">
                                    <i class="fa fa-times" aria-hidden="true"></i> Annuler ma réservation
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('reservations.recherche') }}" class="ve-btn-ghost">
                            <i class="fa fa-search" aria-hidden="true"></i> Rechercher une réservation
                        </a>
                        <a href="{{ route('points-fraicheur.index') }}" class="ve-btn-ghost">
                            <i class="fa fa-arrow-left" aria-hidden="true"></i> Retour aux points de fraîcheur
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
