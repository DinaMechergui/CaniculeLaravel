@extends('front.layouts.front')

@section('title', $campagne->titre . ' — Campagne')
@section('description', 'Campagne de sensibilisation : ' . $campagne->titre)

@section('content')
    <section class="ve-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="mb-3">
                        <a href="{{ route('campagnes.index') }}" class="btn btn-link px-0">
                            <i class="fa fa-arrow-left" aria-hidden="true"></i> Retour aux campagnes
                        </a>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="mb-3">
                                <span class="badge badge-success mr-1">Active</span>
                                <span class="badge badge-info mr-1">{{ $campagne->canal_label }}</span>
                                <span class="badge badge-secondary">{{ $campagne->public_label }}</span>
                            </div>

                            <h1 class="h3 card-title">{{ $campagne->titre }}</h1>

                            @if ($campagne->message)
                                <div class="alert alert-info mt-3">
                                    <i class="fa fa-bullhorn mr-2" aria-hidden="true"></i>
                                    {{ $campagne->message }}
                                </div>
                            @endif

                            <p class="text-muted">
                                <i class="fa fa-calendar" aria-hidden="true"></i>
                                <strong>Période :</strong>
                                {{ $campagne->date_debut->format('d/m/Y') }}
                                →
                                {{ $campagne->date_fin ? $campagne->date_fin->format('d/m/Y') : 'Sans date de fin' }}
                            </p>

                            <hr>

                            <h2 class="h5">Conseil associé</h2>
                            <div class="card mt-3">
                                <div class="card-body">
                                    <span class="badge badge-light">{{ $campagne->conseil->categorie->nom }}</span>
                                    <h3 class="h6 mt-2">{{ $campagne->conseil->titre }}</h3>
                                    <p class="text-muted">{{ $campagne->conseil->contenu }}</p>
                                    <a href="{{ route('conseils.show', $campagne->conseil) }}" class="btn btn-link px-0">
                                        Lire le conseil complet <i class="fa fa-angle-right" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
