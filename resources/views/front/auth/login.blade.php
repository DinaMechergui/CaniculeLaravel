@extends('front.layouts.front')

@section('title', 'Connexion')

@section('content')
    <section class="app-inner-section">
        <div class="container">
            <div class="ve-section-header text-center">
                <span class="ve-section-tag">Espace personnel</span>
                <h2>Se <span>connecter</span></h2>
                <p>Accédez à vos alertes et à vos informations de quartier.</p>
            </div>

            <div class="app-form-card">
                <div class="ve-contact-form-wrap">
                    <h2>Bonjour <span>!</span></h2>
                    <p>Connectez-vous pour continuer.</p>

                    <form method="POST" action="{{ route('login') }}" class="ve-contact-form" novalidate>
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

                        <x-form-input
                            name="password"
                            type="password"
                            label="Mot de passe"
                            required
                            autocomplete="current-password"
                        />

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                            <label class="form-check-label" for="remember">Se souvenir de moi</label>
                        </div>

                        <button type="submit" class="ve-btn-primary">Se connecter</button>
                    </form>

                    <div class="app-card-link">
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}">Mot de passe oublié ?</a>
                        @endif
                    </div>

                    <div class="app-card-link">
                        Pas encore de compte ?
                        <a href="{{ route('register') }}">Créer un compte</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
