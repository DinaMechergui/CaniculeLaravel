@extends('front.layouts.front')

@section('title', 'Rechercher une réservation')
@section('description', 'Retrouvez votre réservation de créneau grâce à votre code et votre e-mail.')

@section('content')
    <section class="app-inner-section">
        <div class="container">
            {{-- Fil d'Ariane --}}
            <nav class="pf-breadcrumb" aria-label="Fil d'Ariane">
                <a href="{{ route('front.home') }}">Accueil</a>
                <span>/</span>
                <a href="{{ route('points-fraicheur.index') }}">Points de fraîcheur</a>
                <span>/</span>
                <strong>Rechercher une réservation</strong>
            </nav>

            <div class="ve-section-header text-center">
                <span class="ve-section-tag">Suivi</span>
                <h2>Rechercher ma <span>réservation</span></h2>
                <p>Saisissez le code de réservation reçu par e-mail ainsi que l'adresse utilisée lors de la réservation.</p>
            </div>

            <div class="app-form-card">
                <div class="ve-contact-form-wrap">
                    <h2>Critères de recherche</h2>

                    <form method="POST" action="{{ route('reservations.recherche.trouver') }}" class="ve-contact-form" novalidate>
                        @csrf
                        <x-form-input
                            name="code"
                            label="Code de réservation"
                            :value="old('code')"
                            placeholder="Ex. A1B2C3D4"
                            maxlength="8"
                            required
                        />

                        <x-form-input
                            name="email"
                            type="email"
                            label="Adresse e-mail utilisée"
                            :value="old('email')"
                            placeholder="Ex. amina@example.com"
                            autocomplete="email"
                            required
                        />

                        <button type="submit" class="ve-btn-primary">
                            <i class="fa fa-search" aria-hidden="true"></i> Rechercher
                        </button>
                        <a href="{{ route('points-fraicheur.index') }}" class="ve-btn-ghost">Retour</a>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
