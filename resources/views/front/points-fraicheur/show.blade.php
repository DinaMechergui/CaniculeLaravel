@extends('front.layouts.front')

@section('title', 'Point de fraîcheur — ' . $point->nom)
@section('description', 'Détail du point de fraîcheur : ' . $point->nom . ', ' . $point->adresse)

@section('content')
    <section class="ve-section pf-section pf-detail">
        <div class="container">
            {{-- Fil d'Ariane --}}
            <nav class="pf-breadcrumb" aria-label="Fil d'Ariane">
                <a href="{{ route('front.home') }}">Accueil</a>
                <span>/</span>
                <a href="{{ route('points-fraicheur.index') }}">Points de fraîcheur</a>
                <span>/</span>
                <strong>{{ $point->nom }}</strong>
            </nav>

            <div class="ve-section-header text-center">
                <span class="ve-section-tag">{{ $point->type?->nom ?? 'Point de fraîcheur' }}</span>
                <h2>{{ $point->nom }}</h2>
                <p><i class="fa fa-map-marker" aria-hidden="true"></i> {{ $point->adresse }}</p>
            </div>

            <div class="pf-detail-grid">
                {{-- Informations pratiques --}}
                <div class="pf-detail-card wow fadeInUp">
                    <div class="pf-detail-icon">
                        <i class="{{ $point->type?->icone ?: 'fa fa-snowflake' }}" aria-hidden="true"></i>
                    </div>

                    <h4>Informations pratiques</h4>

                    <ul class="pf-detail-list">
                        <li>
                            <i class="fa fa-tag" aria-hidden="true"></i>
                            <div>
                                <strong>Type</strong>
                                <span>{{ $point->type?->nom ?? '—' }}</span>
                            </div>
                        </li>
                        <li>
                            <i class="fa fa-map-marker" aria-hidden="true"></i>
                            <div>
                                <strong>Adresse</strong>
                                <span>{{ $point->adresse }}</span>
                            </div>
                        </li>
                        <li>
                            <i class="fa fa-clock-o" aria-hidden="true"></i>
                            <div>
                                <strong>Horaires</strong>
                                <span>{{ $point->horaires ?? 'Non renseignés' }}</span>
                            </div>
                        </li>
                        <li>
                            <i class="fa fa-wheelchair" aria-hidden="true"></i>
                            <div>
                                <strong>Accessibilité</strong>
                                <span>{{ $point->accessible ? 'Accessible au public' : 'Accès limité' }}</span>
                            </div>
                        </li>
                    </ul>
                </div>

                {{-- Localisation sur la carte --}}
                <div class="pf-detail-card wow fadeInUp" data-wow-delay="200ms">
                    <div class="pf-detail-icon">
                        <i class="fa fa-globe" aria-hidden="true"></i>
                    </div>

                    <h4>Localisation</h4>

                    <ul class="pf-detail-list">
                        <li>
                            <i class="fa fa-crosshairs" aria-hidden="true"></i>
                            <div>
                                <strong>Latitude</strong>
                                <span>{{ $point->latitude }}</span>
                            </div>
                        </li>
                        <li>
                            <i class="fa fa-crosshairs" aria-hidden="true"></i>
                            <div>
                                <strong>Longitude</strong>
                                <span>{{ $point->longitude }}</span>
                            </div>
                        </li>
                    </ul>

                    <a class="ve-btn-primary pf-map-btn"
                       href="https://www.openstreetmap.org/?mlat={{ $point->latitude }}&amp;mlon={{ $point->longitude }}#map=17/{{ $point->latitude }}/{{ $point->longitude }}"
                       target="_blank"
                       rel="noopener noreferrer">
                        Ouvrir la carte <i class="fa fa-external-link" aria-hidden="true"></i>
                    </a>
                </div>
            </div>

            <div class="pf-detail-actions text-center">
                <a href="{{ route('reservations.create', $point) }}" class="ve-btn-primary">
                    <i class="fa fa-calendar-check-o" aria-hidden="true"></i> Réserver un créneau
                </a>
                <a href="{{ route('points-fraicheur.index', ['point' => $point->id, 'vue' => 'carte']) }}" class="ve-btn-primary">
                    <i class="fa fa-map-marker" aria-hidden="true"></i> Voir sur la carte
                </a>
                <a href="https://www.openstreetmap.org/directions?engine=fossgis_osrm_foot&amp;route=;{{ $point->latitude }},{{ $point->longitude }}"
                   class="ve-btn-ghost"
                   target="_blank"
                   rel="noopener">
                    <i class="fa fa-location-arrow" aria-hidden="true"></i> Itinéraire
                </a>
                <a href="{{ route('points-fraicheur.index') }}" class="ve-btn-ghost">
                    <i class="fa fa-arrow-left" aria-hidden="true"></i> Retour à la liste
                </a>
            </div>
        </div>
    </section>
@endsection
