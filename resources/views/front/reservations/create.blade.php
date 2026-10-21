@extends('front.layouts.front')

@section('title', 'Réserver un créneau — ' . $point->nom)
@section('description', 'Réservez un créneau dans le point de fraîcheur « ' . $point->nom .' ».')

@section('content')
    <section class="app-inner-section">
        <div class="container">
            {{-- Fil d'Ariane --}}
            <nav class="pf-breadcrumb" aria-label="Fil d'Ariane">
                <a href="{{ route('front.home') }}">Accueil</a>
                <span>/</span>
                <a href="{{ route('points-fraicheur.index') }}">Points de fraîcheur</a>
                <span>/</span>
                <a href="{{ route('points-fraicheur.show', $point) }}">{{ $point->nom }}</a>
                <span>/</span>
                <strong>Réserver</strong>
            </nav>

            <div class="ve-section-header text-center">
                <span class="ve-section-tag">{{ $point->type?->nom ?? 'Point de fraîcheur' }}</span>
                <h2>Réserver un <span>créneau</span></h2>
                <p>
                    <i class="fa fa-map-marker" aria-hidden="true"></i> {{ $point->nom }} — {{ $point->adresse }}
                </p>
                <p>
                    <i class="fa fa-users" aria-hidden="true"></i>
                    Capacité d'accueil : {{ $point->capacite }} personnes
                    @if ($point->horaires)
                        &nbsp;·&nbsp; <i class="fa fa-clock-o" aria-hidden="true"></i> {{ $point->horaires }}
                    @endif
                </p>
            </div>

            <p class="text-center" style="margin-bottom:18px;">
                <a href="{{ route('reservations.recherche') }}">
                    <i class="fa fa-search" aria-hidden="true"></i> Vous avez déjà réservé ? Rechercher ma réservation
                </a>
            </p>

            <div class="app-form-card">
                <div class="ve-contact-form-wrap">
                    <h2>Assistant de réservation</h2>
                    <p>
                        Décrivez votre besoin en une phrase : l'assistant pré-remplit le formulaire pour vous.
                    </p>
                    <div class="ve-form-group app-form-group">
                        <textarea id="assistant-message" rows="2" maxlength="500" class="form-control"
                                  placeholder="Ex. Je voudrais réserver 3 places demain après-midi"></textarea>
                        <button type="button" class="ve-btn-ghost" id="assistant-btn" style="margin-top:8px;">
                            <i class="fa fa-magic" aria-hidden="true"></i> Pré-remplir avec l'assistant
                        </button>
                        <div id="assistant-feedback" class="text-muted" style="margin-top:8px;"></div>
                    </div>

                    <hr>

                    <h2>Disponibilités du jour</h2>
                    <p>Choisissez un créneau libre. Les créneaux complets peuvent accueillir votre demande en liste d'attente.</p>

                    <div id="creneaux-list" class="row" style="margin-bottom:24px;"></div>

                    <h2>Vos informations</h2>
                    <p>Le créneau est réservé au nom indiqué ci-dessous. Aucun compte n'est requis.</p>

                    <form method="POST" action="{{ route('reservations.store') }}" class="ve-contact-form">
                        @csrf

                        {{-- Le point concerné (fixé par la fiche du lieu). --}}
                        <input type="hidden" name="point_fraicheur_id" value="{{ $point->id }}">

                        <x-form-input
                            name="nom"
                            label="Nom complet"
                            :value="old('nom', auth()->user()->name ?? '')"
                            placeholder="Ex. Amina Ben Salah"
                            autocomplete="name"
                            required
                        />

                        <x-form-input
                            name="email"
                            type="email"
                            label="Adresse e-mail"
                            :value="old('email', auth()->user()->email ?? '')"
                            placeholder="Ex. amina@example.com"
                            autocomplete="email"
                            required
                        />

                        <x-form-input
                            name="telephone"
                            label="Téléphone (facultatif)"
                            :value="old('telephone')"
                            placeholder="Ex. 20 123 456"
                            autocomplete="tel"
                        />

                        <x-form-input
                            name="nombre_personnes"
                            type="number"
                            label="Nombre de personnes"
                            :value="old('nombre_personnes', 1)"
                            placeholder="Ex. 2"
                            required
                        />

                        <x-form-input
                            name="date_reservation"
                            type="date"
                            label="Date souhaitée"
                            :value="old('date_reservation', now()->toDateString())"
                            required
                        />

                        <x-form-input
                            name="heure_debut"
                            type="time"
                            label="Heure de début"
                            :value="old('heure_debut', '09:00')"
                            required
                        />

                        <x-form-input
                            name="heure_fin"
                            type="time"
                            label="Heure de fin"
                            :value="old('heure_fin', '10:00')"
                            required
                        />

                        <div class="ve-form-group app-form-group">
                            <label for="besoins_specifiques">Besoins spécifiques (facultatif)</label>
                            <textarea
                                name="besoins_specifiques"
                                id="besoins_specifiques"
                                rows="3"
                                maxlength="500"
                                class="form-control @error('besoins_specifiques') is-invalid @enderror"
                                placeholder="PMR, enfants en bas âge, personne âgée, problème de santé..."
                            >{{ old('besoins_specifiques') }}</textarea>
                            @error('besoins_specifiques')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="ve-form-group app-form-group">
                            <label style="display:flex;align-items:center;gap:8px;font-weight:normal;">
                                <input type="checkbox" name="liste_attente" value="1" @checked(old('liste_attente'))>
                                M'inscrire sur la liste d'attente si le créneau est complet
                            </label>
                            @error('liste_attente')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Pot de miel (honeypot) : champ masqué, doit rester vide. --}}
                        <input type="text" name="website" value="{{ old('website') }}"
                               style="position:absolute;left:-9999px;top:-9999px;"
                               tabindex="-1" autocomplete="off" aria-hidden="true">

                        <button type="submit" class="ve-btn-primary">
                            <i class="fa fa-calendar-check-o" aria-hidden="true"></i> Confirmer la réservation
                        </button>
                        <a href="{{ route('points-fraicheur.show', $point) }}" class="ve-btn-ghost">Annuler</a>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <style>
        .creneau-item {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px 12px;
            margin: 6px 0;
            cursor: pointer;
            transition: all .15s ease;
        }
        .creneau-item:hover { border-color: #0d6efd; box-shadow: 0 2px 10px rgba(13,110,253,.15); }
        .creneau-item.complet { opacity: .55; cursor: not-allowed; }
        .creneau-item .heure { font-weight: 600; }
        .affluence-bar { height: 6px; border-radius: 3px; background: #e9ecef; overflow: hidden; margin-top: 6px; }
        .affluence-bar > span { display: block; height: 100%; }
        .affluence-libre > span { background: #198754; }
        .affluence-modere > span { background: #ffc107; }
        .affluence-presque_complet > span { background: #fd7e14; }
        .affluence-complet > span { background: #dc3545; }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            const url = @json(route('reservations.creneaux', $point));
            const conteneur = document.getElementById('creneaux-list');
            const champDate = document.querySelector('input[name="date_reservation"]');
            const champDebut = document.querySelector('input[name="heure_debut"]');
            const champFin = document.querySelector('input[name="heure_fin"]');

            function afficher(creneaux) {
                conteneur.innerHTML = '';
                if (!creneaux.length) {
                    conteneur.innerHTML = '<div class="col-12 text-muted">Aucun créneau pour cette date.</div>';
                    return;
                }
                creneaux.forEach(function (c) {
                    const pct = c.capacite > 0 ? Math.min(100, Math.round((c.utilisees / c.capacite) * 100)) : 100;
                    const item = document.createElement('div');
                    item.className = 'col-md-4';
                    item.innerHTML =
                        '<div class="creneau-item ' + (c.disponible ? '' : 'complet') + '">' +
                            '<div class="heure">' + c.libelle + '</div>' +
                            '<div><small>' + (c.disponible ? c.restantes + ' place(s) libre(s)' : 'Complet') +
                                ' · ' + c.affluence_label + '</small></div>' +
                            '<div class="affluence-bar affluence-' + c.affluence + '"><span style="width:' + pct + '%"></span></div>' +
                        '</div>';
                    if (c.disponible) {
                        item.querySelector('.creneau-item').addEventListener('click', function () {
                            champDebut.value = c.debut;
                            champFin.value = c.fin;
                        });
                    }
                    conteneur.appendChild(item);
                });
            }

            function charger() {
                if (!champDate) { return; }
                fetch(url + '?date=' + encodeURIComponent(champDate.value), {
                    headers: { 'Accept': 'application/json' }
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) { afficher(data.creneaux || []); })
                    .catch(function () { conteneur.innerHTML = ''; });
            }

            if (champDate) {
                champDate.addEventListener('change', charger);
                charger();
            }
        })();

        // Assistant de réservation en langage naturel.
        (function () {
            const bouton = document.getElementById('assistant-btn');
            const zone = document.getElementById('assistant-message');
            const retour = document.getElementById('assistant-feedback');

            if (! bouton || ! zone) { return; }

            const url = @json(route('reservations.assistant'));

            function remplir(donnees) {
                const personnes = document.querySelector('input[name="nombre_personnes"]');
                const date = document.querySelector('input[name="date_reservation"]');
                const debut = document.querySelector('input[name="heure_debut"]');
                const fin = document.querySelector('input[name="heure_fin"]');

                if (donnees.nombre_personnes && personnes) { personnes.value = donnees.nombre_personnes; }
                if (donnees.date_reservation && date) {
                    date.value = donnees.date_reservation;
                    date.dispatchEvent(new Event('change'));
                }
                if (donnees.heure_debut && debut) { debut.value = donnees.heure_debut; }
                if (donnees.heure_fin && fin) { fin.value = donnees.heure_fin; }
            }

            bouton.addEventListener('click', function () {
                const message = zone.value.trim();

                if (message.length < 3) {
                    retour.textContent = 'Décrivez votre besoin en quelques mots.';
                    return;
                }

                bouton.disabled = true;
                retour.textContent = 'Analyse en cours…';

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': @json(csrf_token()),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message: message })
                })
                    .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
                    .then(function (res) {
                        if (! res.ok) {
                            retour.textContent = res.data.message || 'Demande impossible pour le moment.';
                            return;
                        }
                        remplir(res.data);
                        retour.textContent = res.data.message || 'Formulaire pré-rempli.';
                    })
                    .catch(function () { retour.textContent = 'Assistant indisponible pour le moment.'; })
                    .finally(function () { bouton.disabled = false; });
            });
        })();
    </script>
@endpush
