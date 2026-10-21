@extends('front.layouts.front')

@section('title', 'Vérification de l\'e-mail')

@section('content')
    <section class="app-inner-section">
        <div class="container">
            <div class="ve-section-header text-center">
                <span class="ve-section-tag">Espace personnel</span>
                <h2>Vérifiez votre <span>adresse e-mail</span></h2>
                <p>Un lien de vérification vous a été envoyé par e-mail.</p>
            </div>

            <div class="app-form-card">
                <div class="ve-contact-form-wrap">
                    <h2>Confirmation <span>requise</span></h2>

                    @if (session('status') === 'verification-link-sent')
                        <div class="alert alert-success">
                            Un nouveau lien de vérification a été envoyé à l'adresse
                            {{ auth()->user()->email }}.
                        </div>
                    @endif

                    <p>
                        Merci de cliquer sur le lien reçu par e-mail pour activer votre compte.
                        Pensez à vérifier vos spams.
                    </p>

                    <form method="POST" action="{{ route('verification.send') }}" class="ve-contact-form" novalidate>
                        @csrf
                        <button type="submit" class="ve-btn-primary">Renvoyer l'e-mail de vérification</button>
                    </form>

                    <div class="app-card-link">
                        <form method="POST" action="{{ route('logout') }}" novalidate>
                            @csrf
                            <button type="submit" class="app-link-button">Se déconnecter</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
