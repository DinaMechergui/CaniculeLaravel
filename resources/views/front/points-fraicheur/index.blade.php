@extends('front.layouts.front')

@section('title', 'Points de fraîcheur')
@section('description', 'Parcourez les points de fraîcheur du quartier : parcs, salles climatisées et plages, avec la liste et la carte interactive sur la même page.')

{{-- Styles de Leaflet (fichiers servis localement, pas de CDN) --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/front/vendor/leaflet/leaflet.css') }}">
@endpush

@section('content')
    <section class="ve-section pf-section">
        <div class="container">
            {{-- En-tête de section --}}
            <div class="ve-section-header text-center">
                <span class="ve-section-tag">Points de fraîcheur</span>
                <h2>Trouvez un lieu de <span>rafraîchissement</span> près de chez vous</h2>
                <p>
                    Parcs, salles climatisées et plages ouverts pour souffler pendant les épisodes de chaleur.
                    {{ $points->total() }} point(s) disponible(s){{ $selectedType ? ' pour ce type' : '' }}.
                </p>
            </div>

            {{-- Filtres : recherche + type --}}
            <form method="GET" action="{{ route('points-fraicheur.index') }}" class="pf-filters">
                <div class="pf-filter-field">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Rechercher un nom ou une adresse..."
                    >
                </div>

                <div class="pf-filter-field pf-filter-select">
                    <i class="fa fa-filter" aria-hidden="true"></i>
                    <select name="type">
                        <option value="">Tous les types</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}" @selected($selectedType?->id === $type->id)>
                                {{ $type->nom }} ({{ $type->points_count }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="ve-btn-primary">Filtrer</button>

                @if ($search || request('type'))
                    <a href="{{ route('points-fraicheur.index') }}" class="pf-reset">Réinitialiser</a>
                @endif
            </form>

            {{-- Explorateur : liste + carte intégrée, synchronisées --}}
            <div
                class="pf-explorer"
                id="pf-explorer"
                data-vue="{{ request('vue') === 'carte' ? 'carte' : 'liste' }}"
            >
                {{-- Onglets liste / carte : une seule vue à la fois --}}
                <div class="pf-views" role="tablist" aria-label="Mode d'affichage">
                    <button
                        type="button"
                        class="pf-view-btn"
                        role="tab"
                        data-vue="liste"
                        aria-selected="{{ request('vue') === 'carte' ? 'false' : 'true' }}"
                    >
                        <i class="fa fa-list" aria-hidden="true"></i> Liste
                    </button>

                    @if ($pointsCarte->isNotEmpty())
                        <button
                            type="button"
                            class="pf-view-btn"
                            role="tab"
                            data-vue="carte"
                            aria-selected="{{ request('vue') === 'carte' ? 'true' : 'false' }}"
                        >
                            <i class="fa fa-map-o" aria-hidden="true"></i> Carte
                        </button>
                    @endif
                </div>

                {{-- Colonne gauche : grille des points --}}
                <div class="pf-explorer-colonne">
                    <div class="pf-grid">
                        @forelse ($points as $point)
                            <article class="pf-card wow fadeInUp" data-point-id="{{ $point->id }}" data-wow-delay="100ms">
                                <div class="pf-card-icon">
                                    <i class="{{ $point->type?->icone ?: 'fa fa-snowflake' }}" aria-hidden="true"></i>
                                </div>

                                <div class="pf-card-body">
                                    <span class="pf-badge">{{ $point->type?->nom ?? 'Point de fraîcheur' }}</span>
                                    <h4>{{ $point->nom }}</h4>

                                    <p class="pf-address">
                                        <i class="fa fa-map-marker" aria-hidden="true"></i> {{ $point->adresse }}
                                    </p>

                                    @if ($point->horaires)
                                        <p class="pf-hours">
                                            <i class="fa fa-clock-o" aria-hidden="true"></i> {{ $point->horaires }}
                                        </p>
                                    @endif

                                    <div class="pf-card-footer">
                                        <span class="pf-access {{ $point->accessible ? 'is-open' : 'is-limited' }}">
                                            {{ $point->accessible ? 'Accessible' : 'Accès limité' }}
                                        </span>

                                        <span class="pf-card-actions">
                                            <button
                                                type="button"
                                                class="pf-card-locate"
                                                data-point-id="{{ $point->id }}"
                                                title="Localiser ce point sur la carte"
                                            >
                                                <i class="fa fa-map-marker" aria-hidden="true"></i>
                                                <span>Localiser</span>
                                            </button>

                                            <a href="{{ route('points-fraicheur.show', $point) }}" class="pf-link">
                                                Détail <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                            </a>
                                        </span>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="pf-empty">
                                <i class="fa fa-search" aria-hidden="true"></i>
                                <p>Aucun point de fraîcheur ne correspond à votre recherche.</p>
                                <a href="{{ route('points-fraicheur.index') }}" class="ve-btn-ghost">Voir tous les points</a>
                            </div>
                        @endforelse
                    </div>

                    {{-- Pagination --}}
                    <x-pagination :paginator="$points" variant="bootstrap-4" />
                </div>

                {{-- Colonne droite : carte intégrée (synchronisée avec la liste) --}}
                @if ($pointsCarte->isNotEmpty())
                    <aside class="pf-carte-encart" id="pf-carte" aria-label="Carte des points de fraîcheur">
                        <div class="pf-carte-bar">
                            <span class="pf-carte-compte">
                                <i class="fa fa-map-marker" aria-hidden="true"></i>
                                {{ $pointsCarte->count() }} point(s) sur la carte
                            </span>

                            <button type="button" id="pf-embed-locate" class="pf-carte-action">
                                <i class="fa fa-location-arrow" aria-hidden="true"></i> Près de moi
                            </button>

                            <a href="{{ route('points-fraicheur.carte') }}" class="pf-carte-action">
                                <i class="fa fa-expand" aria-hidden="true"></i> Plein écran
                            </a>
                        </div>

                        <div id="pf-embed-message" class="pf-map-message" role="status" hidden></div>

                        <div
                            id="pf-map-embed"
                            class="pf-carte-canvas"
                            aria-label="Carte des points de fraîcheur"
                        ></div>
                    </aside>
                @endif
            </div>

            {{-- Assistant IA intégré : même moteur que /assistant,
                 conscient des filtres actuellement appliqués --}}
            @include('front.partials.chat-ia', [
                'chatId' => 'ai-embed',
                'titre' => 'Une question sur ces lieux ?',
                'sousTitre' => "L'assistant connaît les 30 points de fraîcheur et vos filtres actuels : type, adresse, horaires, conseils anti-chaleur.",
                'avecFiltres' => true,
                'bienvenue' => "Bonjour ! Je suis l'assistant IA d'Alerte Canicule. Je réponds sur les lieux affichés dans cette page : essayez « Où me rafraîchir près de La Marsa ? » ou « Quels horaires pour le parc ? ».",
                'questions' => [
                    'Où me rafraîchir avec ces filtres ?',
                    'Quels conseils contre la chaleur aujourd’hui ?',
                    'Quel lieu est ouvert maintenant ?',
                ],
            ])
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('assets/front/vendor/leaflet/leaflet.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var explorateur = document.getElementById('pf-explorer');

            if (!explorateur) {
                return;
            }

            var encart = document.getElementById('pf-carte');
            var boutonsVue = explorateur.querySelectorAll('.pf-view-btn');
            var carte = null;
            var vueAjustee = false;

            /** Passe en mode liste ou carte (et met l'URL à jour). */
            function activerVue(vue, mettreUrl) {
                explorateur.setAttribute('data-vue', vue);

                boutonsVue.forEach(function (bouton) {
                    bouton.setAttribute('aria-selected', String(bouton.getAttribute('data-vue') === vue));
                });

                if (mettreUrl) {
                    var url = new URL(window.location.href);

                    if (vue === 'carte') {
                        url.searchParams.set('vue', 'carte');
                    } else {
                        url.searchParams.delete('vue');
                    }

                    window.history.replaceState(null, '', url.toString());
                }

                if (vue === 'carte' && encart) {
                    encart.scrollIntoView({
                        behavior: 'smooth',
                        block: window.matchMedia('(max-width: 991px)').matches ? 'start' : 'nearest'
                    });

                    // La carte était masquée : il faut recalculer sa taille,
                    // puis ajuster la vue sur les marqueurs si ce n'est pas fait.
                    if (carte) {
                        window.setTimeout(function () {
                            carte.invalidateSize();
                            ajusterVueCarte();
                        }, 220);
                    }
                }
            }

            /** Cadre la carte sur l'ensemble des marqueurs (une seule fois). */
            function ajusterVueCarte() {
                var zoneCarte = document.getElementById('pf-map-embed');

                // Carte masquée (onglet Liste) : on réessaiera à l'activation.
                if (vueAjustee || !carte || !entrees.length || !zoneCarte || zoneCarte.offsetWidth === 0) {
                    return;
                }

                vueAjustee = true;
                carte.fitBounds(entrees.map(function (entree) {
                    return [entree.point.lat, entree.point.lng];
                }), { padding: [30, 30] });
            }

            boutonsVue.forEach(function (bouton) {
                bouton.addEventListener('click', function () {
                    activerVue(bouton.getAttribute('data-vue'), true);
                });
            });

            // Bascule initiale depuis l'URL (?vue=carte).
            activerVue(explorateur.getAttribute('data-vue') || 'liste', false);

            // --- Carte intégrée ---------------------------------------------
            var carteElement = document.getElementById('pf-map-embed');
            var points = @json($pointsCarte);

            if (!carteElement || typeof L === 'undefined' || !points.length) {
                return;
            }

            // Icônes des marqueurs servies localement (éviter tout 404).
            L.Icon.Default.prototype.options.imagePath =
                @json(asset('assets/front/vendor/leaflet/images')) + '/';

            // Centre par défaut : Tunis, zoom 11.
            carte = L.map('pf-map-embed').setView([36.8065, 10.1815], 11);

            // Seules les tuiles viennent d'Internet (OpenStreetMap).
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(carte);

            var zoneMessage = document.getElementById('pf-embed-message');
            var boutonLocaliser = document.getElementById('pf-embed-locate');
            var urlProches = @json(route('points-fraicheur.proches'));

            var distances = null;            // id => km (après « Près de moi »)
            var marqueurUtilisateur = null;
            var entrees = [];                // { point, marqueur }

            /**
             * Construit le contenu d'une fenêtre de marqueur avec du DOM
             * sécurisé : le texte passe par textContent (aucun HTML non
             * échappé, donc aucun risque d'injection XSS).
             */
            function construirePopup(point) {
                var conteneur = document.createElement('div');
                conteneur.className = 'pf-popup';

                var titre = document.createElement('strong');
                titre.className = 'pf-popup-titre';
                titre.textContent = point.nom;
                conteneur.appendChild(titre);

                if (point.type) {
                    var type = document.createElement('span');
                    type.className = 'pf-popup-type';
                    type.textContent = point.type;
                    conteneur.appendChild(type);
                }

                if (point.adresse) {
                    var adresse = document.createElement('span');
                    adresse.className = 'pf-popup-meta';
                    adresse.textContent = point.adresse;
                    conteneur.appendChild(adresse);
                }

                if (point.horaires) {
                    var horaires = document.createElement('span');
                    horaires.className = 'pf-popup-meta';
                    horaires.textContent = 'Horaires : ' + point.horaires;
                    conteneur.appendChild(horaires);
                }

                if (typeof point.distance === 'number') {
                    var distance = document.createElement('span');
                    distance.className = 'pf-map-distance';
                    distance.textContent = point.distance.toFixed(1) + ' km de vous';
                    conteneur.appendChild(distance);
                }

                if (point.accessible) {
                    var badge = document.createElement('span');
                    badge.className = 'pf-popup-badge';
                    badge.textContent = 'Accessible';
                    conteneur.appendChild(badge);
                }

                var liens = document.createElement('div');
                liens.className = 'pf-popup-liens';

                var lienDetails = document.createElement('a');
                lienDetails.href = point.url;
                lienDetails.textContent = 'Détails';
                liens.appendChild(lienDetails);

                var lienItineraire = document.createElement('a');
                lienItineraire.href = 'https://www.openstreetmap.org/directions'
                    + '?engine=fossgis_osrm_foot&route=;' + point.lat + ',' + point.lng;
                lienItineraire.target = '_blank';
                lienItineraire.rel = 'noopener';
                lienItineraire.textContent = 'Itinéraire';
                liens.appendChild(lienItineraire);

                conteneur.appendChild(liens);

                return conteneur;
            }

            /** Affiche un message dans la page (jamais d'alert()). */
            function afficherMessage(texte, type) {
                zoneMessage.textContent = texte;
                zoneMessage.className = 'pf-map-message' + (type ? ' is-' + type : '');
                zoneMessage.hidden = !texte;
            }

            /** Met en surbrillance la fiche correspondant à un point. */
            function surlignerFiche(pointId) {
                var cartes = explorateur.querySelectorAll('.pf-card');
                var cible = null;

                cartes.forEach(function (elementCarte) {
                    elementCarte.classList.remove('is-active');
                });

                var carteCible = explorateur.querySelector('.pf-card[data-point-id="' + pointId + '"]');

                if (carteCible) {
                    carteCible.classList.add('is-active');
                    cible = carteCible;
                }

                return cible;
            }

            /** Centre la carte sur un point et ouvre sa fenêtre. */
            function centrerSurPoint(point) {
                activerVue('carte', false);
                vueAjustee = true;
                carte.setView([point.lat, point.lng], 16);

                entrees.forEach(function (entree) {
                    if (entree.point.id === point.id) {
                        entree.marqueur.openPopup();
                    }
                });
            }

            // Création d'un marqueur par point.
            points.forEach(function (point) {
                var marqueur = L.marker([point.lat, point.lng]).addTo(carte);
                marqueur.bindPopup(construirePopup(point));

                marqueur.on('click', function () {
                    var fiche = surlignerFiche(point.id);

                    // La fiche n'est défilée que si l'onglet Liste est affiché.
                    if (fiche && explorateur.getAttribute('data-vue') === 'liste') {
                        fiche.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                });

                entrees.push({ point: point, marqueur: marqueur });
            });

            // Fiches : bouton « Localiser » → carte centrée sur le point.
            explorateur.querySelectorAll('.pf-card-locate').forEach(function (bouton) {
                bouton.addEventListener('click', function () {
                    var pointId = parseInt(bouton.getAttribute('data-point-id'), 10);
                    var entree = null;

                    entrees.forEach(function (candidate) {
                        if (candidate.point.id === pointId) {
                            entree = candidate;
                        }
                    });

                    if (!entree) {
                        return;
                    }

                    surlignerFiche(pointId);
                    centrerSurPoint(entree.point);
                });
            });

            // « Près de moi » : géolocalisation + distance dans les fenêtres.
            boutonLocaliser.addEventListener('click', function () {
                afficherMessage('');

                if (!navigator.geolocation) {
                    afficherMessage("La géolocalisation n'est pas prise en charge par votre navigateur.", 'error');
                    return;
                }

                boutonLocaliser.disabled = true;
                afficherMessage('Recherche de votre position en cours…', 'info');

                navigator.geolocation.getCurrentPosition(function (position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;

                    if (marqueurUtilisateur) {
                        carte.removeLayer(marqueurUtilisateur);
                    }

                    marqueurUtilisateur = L.marker([lat, lng]).addTo(carte);
                    marqueurUtilisateur.bindPopup('<strong>Vous êtes ici</strong>').openPopup();
                    vueAjustee = true;
                    carte.setView([lat, lng], 14);

                    fetch(urlProches + '?lat=' + encodeURIComponent(lat) + '&lng=' + encodeURIComponent(lng))
                        .then(function (reponse) {
                            if (!reponse.ok) {
                                throw new Error('http');
                            }

                            return reponse.json();
                        })
                        .then(function (donnees) {
                            distances = {};

                            (donnees.points || []).forEach(function (point) {
                                distances[point.id] = point.distance;
                            });

                            // Mise à jour des fenêtres avec la distance.
                            entrees.forEach(function (entree) {
                                if (typeof distances[entree.point.id] === 'number') {
                                    entree.point.distance = distances[entree.point.id];
                                } else {
                                    delete entree.point.distance;
                                }

                                entree.marqueur.setPopupContent(construirePopup(entree.point));
                            });

                            afficherMessage('Distances affichées dans les fenêtres, du plus proche au plus éloigné.', 'success');
                            boutonLocaliser.disabled = false;
                        })
                        .catch(function () {
                            boutonLocaliser.disabled = false;
                            afficherMessage('Impossible de charger les points les plus proches.', 'error');
                        });
                }, function (erreur) {
                    boutonLocaliser.disabled = false;

                    if (erreur.code === erreur.PERMISSION_DENIED) {
                        afficherMessage("Permission refusée : autorisez l'accès à votre position pour utiliser « Près de moi ».", 'error');
                    } else if (erreur.code === erreur.POSITION_UNAVAILABLE) {
                        afficherMessage('Position indisponible : impossible de déterminer votre localisation.', 'error');
                    } else if (erreur.code === erreur.TIMEOUT) {
                        afficherMessage('Délai dépassé : la recherche de votre position a trop duré.', 'error');
                    } else {
                        afficherMessage('Votre position est introuvable.', 'error');
                    }
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 60000
                });
            });

            // Ouverture ciblée : /points-fraicheur?point={id}
            var parametres = new URLSearchParams(window.location.search);
            var idPoint = parametres.get('point');
            var entreeCiblee = null;

            if (idPoint) {
                entrees.forEach(function (entree) {
                    if (String(entree.point.id) === idPoint) {
                        entreeCiblee = entree;
                    }
                });
            }

            if (entreeCiblee) {
                activerVue('carte', false);
                vueAjustee = true;
                carte.setView([entreeCiblee.point.lat, entreeCiblee.point.lng], 16);
                entreeCiblee.marqueur.openPopup();
                surlignerFiche(entreeCiblee.point.id);
            } else {
                // Onglet Carte affiché : ajustement immédiat. Onglet Liste :
                // différé jusqu'à la première activation de l'onglet Carte
                // (fitBounds sur un conteneur masqué serait invalide).
                ajusterVueCarte();
            }
        });
    </script>
@endpush
