@extends('back.layouts.back')

@section('title', 'Détail d\'un point de fraîcheur')

@section('content')
    <x-page-header :title="$pointFraicheur->nom" :subtitle="'Point de fraîcheur #' . $pointFraicheur->id">
        @slot('actions')
            <a href="{{ route('admin.points-fraicheur.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
            <a href="{{ route('admin.points-fraicheur.edit', $pointFraicheur) }}" class="btn btn-primary">
                <i class="bx bx-edit me-1"></i> Modifier
            </a>
        @endslot
    </x-page-header>

    <div class="row">
        {{-- Informations du point --}}
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Nom</div>
                        <div class="col-sm-8">{{ $pointFraicheur->nom }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Type</div>
                        <div class="col-sm-8">
                            @if ($pointFraicheur->type)
                                <a href="{{ route('admin.types-point.show', $pointFraicheur->type) }}">
                                    {{ $pointFraicheur->type->nom }}
                                </a>
                            @else
                                —
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Adresse</div>
                        <div class="col-sm-8">{{ $pointFraicheur->adresse }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Horaires</div>
                        <div class="col-sm-8">{{ $pointFraicheur->horaires ?? '—' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Capacité</div>
                        <div class="col-sm-8">{{ $pointFraicheur->capacite }} personnes</div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4 text-muted">Accessible</div>
                        <div class="col-sm-8">
                            <span class="badge {{ $pointFraicheur->accessible ? 'app-badge-admin' : 'app-badge-user' }}">
                                {{ $pointFraicheur->accessible ? 'Oui' : 'Non' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Localisation + suppression --}}
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Localisation</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Latitude</div>
                        <div class="col-sm-8"><code>{{ $pointFraicheur->latitude }}</code></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Longitude</div>
                        <div class="col-sm-8"><code>{{ $pointFraicheur->longitude }}</code></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Créé le</div>
                        <div class="col-sm-8">{{ $pointFraicheur->created_at?->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4 text-muted">Carte</div>
                        <div class="col-sm-8">
                            <a href="https://www.openstreetmap.org/?mlat={{ $pointFraicheur->latitude }}&amp;mlon={{ $pointFraicheur->longitude }}#map=17/{{ $pointFraicheur->latitude }}/{{ $pointFraicheur->longitude }}"
                               target="_blank" rel="noopener noreferrer">
                                Ouvrir dans OpenStreetMap
                                <i class="bx bx-link-external"></i>
                            </a>
                        </div>
                    </div>

                    <hr>

                    <form method="POST"
                          action="{{ route('admin.points-fraicheur.destroy', $pointFraicheur) }}"
                          onsubmit="return confirm('Confirmer la suppression de ce point de fraîcheur ?');" novalidate>
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bx bx-trash me-1"></i> Supprimer ce point
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
