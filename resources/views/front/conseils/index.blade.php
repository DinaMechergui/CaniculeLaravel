@extends('front.layouts.front')

@section('title', 'Conseils')
@section('description', 'Conseils pratiques pour bien vivre les canicules et les coupures de courant : énergie, hydratation, équipements.')

@section('content')
    <section class="ve-section">
        <div class="container">
            {{-- En-tête --}}
            <div class="ve-section-header text-center">
                <span class="ve-section-tag">Conseils</span>
                <h2>Nos conseils <span>pratiques</span></h2>
                <p>
                    Des gestes simples pour passer les épisodes de chaleur et les coupures de courant.
                    {{ $conseils->total() }} conseil(s){{ $selectedCategorie ? ' dans « ' . $selectedCategorie->nom . ' »' : '' }}.
                </p>
            </div>

            {{-- Filtres : recherche + catégorie --}}
            <form method="GET" action="{{ route('conseils.index') }}" class="form-row justify-content-center mb-4">
                <div class="col-12 col-md-5 mb-2">
                    <input type="text" name="search" value="{{ $search }}"
                           class="form-control" placeholder="Rechercher un conseil...">
                </div>
                <div class="col-12 col-md-4 mb-2">
                    <select name="categorie" class="form-control">
                        <option value="">Toutes les catégories</option>
                        @foreach ($categories as $categorie)
                            <option value="{{ $categorie->id }}" @selected($selectedCategorie?->id === $categorie->id)>
                                {{ $categorie->nom }} ({{ $categorie->conseils_count }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-auto mb-2">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                    @if ($search || request('categorie'))
                        <a href="{{ route('conseils.index') }}" class="btn btn-link">Réinitialiser</a>
                    @endif
                </div>
            </form>

            {{-- Grille des conseils --}}
            <div class="row">
                @forelse ($conseils as $conseil)
                    <div class="col-12 col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body d-flex flex-column">
                                <span class="badge badge-info align-self-start mb-2">
                                    {{ $conseil->categorie->nom }}
                                </span>
                                <h5 class="card-title">{{ $conseil->titre }}</h5>
                                <p class="card-text text-muted">
                                    {{ \Illuminate\Support\Str::limit(strip_tags($conseil->contenu), 120) }}
                                </p>
                                                                <p class="small text-muted mb-2">
                                    <i class="fa fa-clock-o" aria-hidden="true"></i> {{ $conseil->temps_lecture }} min de lecture
                                </p>
                                <a href="{{ route('conseils.show', $conseil) }}" class="mt-auto">
                                    Lire le conseil <i class="fa fa-angle-right" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-5">
                        Aucun conseil ne correspond à votre recherche.
                    </div>
                @endforelse
            </div>

            <x-pagination :paginator="$conseils" variant="bootstrap-4" />
        </div>
    </section>
@endsection
