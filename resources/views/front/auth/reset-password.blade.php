@extends('front.layouts.front')

@section('title', 'Réinitialiser le mot de passe')

@section('content')
    <section class="app-inner-section">
        <div class="container">
            <div class="ve-section-header text-center">
                <span class="ve-section-tag">Espace personnel</span>
                <h2>Nouveau <span>mot de passe</span></h2>
                <p>Choisissez un mot de passe sécurisé pour votre compte.</p>
            </div>

            <div class="app-form-card">
                <div class="ve-contact-form-wrap">
                    <h2>Changer de <span>mot de passe</span></h2>
                    <p>Ce mot de passe remplace l'ancien immédiatement.</p>

                    <form method="POST" action="{{ route('password.store') }}" class="ve-contact-form" novalidate>
                        @csrf

                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <x-form-input
                            name="email"
                            type="email"
                            label="Adresse e-mail"
                            :value="$request->email"
                            required
                            autocomplete="username"
                            autofocus
                        />

                        <x-form-input
                            name="password"
                            type="password"
                            label="Nouveau mot de passe"
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

                        <button type="submit" class="ve-btn-primary">Réinitialiser le mot de passe</button>
                    </form>

                    <div class="app-card-link">
                        <a href="{{ route('login') }}">Retour à la connexion</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
