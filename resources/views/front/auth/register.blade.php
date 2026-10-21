@extends('front.layouts.front')

@section('title', 'Inscription')

@section('content')
    <section class="app-inner-section">
        <div class="container">
            <div class="ve-section-header text-center">
                <span class="ve-section-tag">Espace personnel</span>
                <h2>Créer <span>un compte</span></h2>
                <p>Recevez les alertes de votre quartier en quelques secondes.</p>
            </div>

            <div class="app-form-card">
                <div class="ve-contact-form-wrap">
                    <h2>Inscription <span>gratuite</span></h2>
                    <p>Vos informations restent strictement confidentielles.</p>

                    <form method="POST" action="{{ route('register') }}" class="ve-contact-form" novalidate>
                        @csrf

                        <x-form-input
                            name="name"
                            label="Nom complet"
                            placeholder="Ex. Marie Dupont"
                            required
                            autocomplete="name"
                            autofocus
                        />

                        <x-form-input
                            name="email"
                            type="email"
                            label="Adresse e-mail"
                            placeholder="exemple@domaine.fr"
                            required
                            autocomplete="email"
                        />

                        <x-form-input
                            name="password"
                            type="password"
                            label="Mot de passe"
                            placeholder="8 caractères minimum"
                            required
                            autocomplete="new-password"
                        />

                        <x-form-input
                            name="password_confirmation"
                            type="password"
                            label="Confirmer le mot de passe"
                            required
                            autocomplete="new-password"
                        />

                        <button type="submit" class="ve-btn-primary">Créer mon compte</button>
                    </form>

                    <div class="app-card-link">
                        Déjà inscrit ?
                        <a href="{{ route('login') }}">Se connecter</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
