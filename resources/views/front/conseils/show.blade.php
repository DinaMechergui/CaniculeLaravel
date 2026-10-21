@extends('front.layouts.front')

@section('title', $conseil->titre)
@section('description', \Illuminate\Support\Str::limit(strip_tags($conseil->contenu), 150))

@section('content')
    <section class="ve-section">
        <div class="container">
            @if($campagnes->isNotEmpty())<div class="alert alert-info"><strong>Campagnes liées</strong><ul class="mb-0">@foreach($campagnes as $campagne)<li><a href="{{ route('campagnes.show',$campagne) }}">{{ $campagne->titre }}</a></li>@endforeach</ul></div>@endif
            {{-- Fil d'Ariane --}}
            <nav class="mb-4" aria-label="Fil d'Ariane">
                <a href="{{ route('front.home') }}">Accueil</a>
                <span>/</span>
                <a href="{{ route('conseils.index') }}">Conseils</a>
                <span>/</span>
                <strong>{{ $conseil->titre }}</strong>
            </nav>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <article class="card shadow-sm">
                        <div class="card-body p-4 p-md-5">
                            <a href="{{ route('conseils.index', ['categorie' => $conseil->categorie->id]) }}"
                               class="badge badge-info mb-3">
                                {{ $conseil->categorie->nom }}
                            </a>

                            <h2 class="mb-3">{{ $conseil->titre }}</h2>

                                                      <p class="text-muted small mb-4">
                                <i class="fa fa-calendar" aria-hidden="true"></i>
                                Publié le {{ $conseil->created_at?->format('d/m/Y') }}
                                &nbsp;·&nbsp;
                                <i class="fa fa-clock-o" aria-hidden="true"></i>
                                {{ $conseil->temps_lecture }} min de lecture
                            </p>

                            <div class="conseil-contenu">
                                {!! nl2br(e($conseil->contenu)) !!}
                            </div>
                        </div>
                    </article>
                    {{-- Conseils similaires (même catégorie) --}}
                    @if ($similaires->isNotEmpty())
                        <h4 class="mt-5 mb-3">Conseils similaires</h4>
                        <div class="list-group">
                            @foreach ($similaires as $similaire)
                                <a href="{{ route('conseils.show', $similaire) }}"
                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    {{ $similaire->titre }}
                                    <small class="text-muted">{{ $similaire->temps_lecture }} min</small>
                                </a>
                            @endforeach
                        </div>
                    @endif
                    <div class="mt-4">
                        <a href="{{ route('conseils.index') }}" class="btn btn-outline-primary">
                            <i class="fa fa-arrow-left" aria-hidden="true"></i> Retour aux conseils
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
