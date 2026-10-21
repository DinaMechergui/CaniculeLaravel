@extends('front.layouts.front')

@section('title', 'Confirmation du mot de passe')

@section('content')
    <section class="app-inner-section">
        <div class="container">
            <div class="ve-section-header text-center">
                <span class="ve-section-tag">Espace personnel</span>
                <h2>Confirmez votre <span>mot de passe</span></h2>
                <p>Cette page protège l'accès à une action sensible.</p>
            </div>

            <div class="app-form-card">
                <div class="ve-contact-form-wrap">
                    <h2>Confirmation <span>sécurisée</span></h2>
                    <p>Saisissez votre mot de passe pour continuer.</p>

                    <form method="POST" action="{{ route('password.confirm') }}" class="ve-contact-form" novalidate>
                        @csrf

                        <x-form-input
                            name="password"
                            type="password"
                            label="Mot de passe"
                            required
                            autocomplete="current-password"
                            autofocus
                        />

                        <button type="submit" class="ve-btn-primary">Confirmer</button>
                    </form>

                    <div class="app-card-link">
                        <a href="{{ route('password.request') }}">Mot de passe oublié ?</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
