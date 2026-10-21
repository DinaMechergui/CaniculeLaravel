@extends('front.layouts.front')

@section('title', 'Mot de passe oublié')

@section('content')
    <section class="app-inner-section">
        <div class="container">
            <div class="ve-section-header text-center">
                <span class="ve-section-tag">Espace personnel</span>
                <h2>Mot de passe <span>oublié</span></h2>
                <p>Nous vous enverrons un lien pour réinitialiser votre mot de passe.</p>
            </div>

            <div class="app-form-card">
                <div class="ve-contact-form-wrap">
                    <h2>Réinitialiser <span>le mot de passe</span></h2>
                    <p>Indiquez l'adresse e-mail associée à votre compte.</p>

                    <form method="POST" action="{{ route('password.email') }}" class="ve-contact-form" novalidate>
                        @csrf

                        <x-form-input
                            name="email"
                            type="email"
                            label="Adresse e-mail"
                            placeholder="exemple@domaine.fr"
                            required
                            autocomplete="username"
                            autofocus
                        />

                        <button type="submit" class="ve-btn-primary">Envoyer le lien</button>
                    </form>

                    <div class="app-card-link">
                        <a href="{{ route('login') }}">Retour à la connexion</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
