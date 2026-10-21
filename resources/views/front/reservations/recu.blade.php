@extends('front.layouts.front')

@section('title', 'Arrivée validée — ' . $reservation->code)
@section('description', 'Reçu de validation d\'arrivée de votre réservation.')

@section('content')
    <section class="ve-section pf-section">
        <div class="container">
            <div style="max-width:560px;margin:0 auto;">

                @if ($validee)
                    <div class="text-center" style="margin-bottom:18px;">
                        <div style="width:88px;height:88px;border-radius:50%;background:#198754;color:#fff;
                                    display:flex;align-items:center;justify-content:center;margin:0 auto 12px;
                                    font-size:44px;line-height:1;">
                            <i class="fa fa-check" aria-hidden="true"></i>
                        </div>
                        <h2 style="margin:0;">
                            @if ($dejaValidee)
                                Arrivée <span>déjà validée</span>
                            @else
                                Arrivée <span>validée</span>
                            @endif
                        </h2>
                        <p>Présentez cet écran à l'accueil du point de fraîcheur.</p>
                    </div>
                @else
                    <div class="text-center" style="margin-bottom:18px;">
                        <div style="width:88px;height:88px;border-radius:50%;background:#dc3545;color:#fff;
                                    display:flex;align-items:center;justify-content:center;margin:0 auto 12px;
                                    font-size:44px;line-height:1;">
                            <i class="fa fa-times" aria-hidden="true"></i>
                        </div>
                        <h2 style="margin:0;">Réservation <span>non valide</span></h2>
                        <p>Cette réservation ne peut pas être validée ({{ $reservation->statut_label }}).</p>
                    </div>
                @endif

                <div class="pf-detail-card wow fadeInUp"
                     style="border:2px dashed #ced4da;border-radius:14px;padding:22px;">
                    <div class="text-center" style="margin-bottom:18px;">
                        <span class="badge badge-{{ $reservation->statut_badge }}" style="font-size:0.95rem;">
                            {{ $reservation->statut_label }}
                        </span>
                        <h3 style="margin:10px 0 0;letter-spacing:3px;">{{ $reservation->code }}</h3>
                        <small style="color:#666;">Code de réservation</small>
                    </div>

                    <ul class="pf-detail-list">
                        <li>
                            <i class="fa fa-map-marker" aria-hidden="true"></i>
                            <div>
                                <strong>Lieu</strong>
                                <span>{{ $reservation->pointFraicheur?->nom ?? '—' }}</span>
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
                                <span>{{ $reservation->nom }}</span>
                            </div>
                        </li>
                        @if ($reservation->check_in_le)
                            <li>
                                <i class="fa fa-check-circle" aria-hidden="true"></i>
                                <div>
                                    <strong>Validée le</strong>
                                    <span>{{ $reservation->check_in_le->format('d/m/Y à H:i') }}</span>
                                </div>
                            </li>
                        @endif
                    </ul>

                    <div class="text-center" style="margin-top:18px;">
                        <button type="button" class="ve-btn-primary" onclick="window.print()">
                            <i class="fa fa-print" aria-hidden="true"></i> Imprimer / Enregistrer
                        </button>
                    </div>
                </div>

                <p class="text-center" style="margin-top:16px;">
                    <a href="{{ route('reservations.show', $reservation->code) }}">
                        <i class="fa fa-search" aria-hidden="true"></i> Voir le détail de la réservation
                    </a>
                </p>
            </div>
        </div>
    </section>
@endsection
