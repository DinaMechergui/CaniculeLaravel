@extends('front.layouts.front')
@section('title', 'Coupures de courant')
@section('description', 'Consultez les coupures de courant prévues ou en cours par quartier.')
@section('content')
    <section class="ve-section"><div class="container">
        <div class="ve-section-header text-center"><span class="ve-section-tag">Coupures de courant</span><h2>Suivre les coupures par quartier</h2><p>{{ $coupures->total() }} coupure(s) prévue(s) ou en cours.</p></div>
        <div class="text-center mb-4"><a href="{{ route('coupures.map') }}" class="ve-btn-primary"><i class="fa fa-map-marker" aria-hidden="true"></i> Voir la carte publique</a></div>
        <form method="GET" action="{{ route('coupures.index') }}" class="pf-filters mb-4" novalidate>
            <div class="pf-filter-field pf-filter-select"><i class="fa fa-map-marker" aria-hidden="true"></i><select name="quartier" aria-label="Filtrer par quartier"><option value="">Tous les quartiers</option>@foreach ($quartiers as $quartier)<option value="{{ $quartier->id }}" @selected((string) $selectedQuartier === (string) $quartier->id)>{{ $quartier->nom }} — {{ $quartier->ville }}</option>@endforeach</select></div>
            <button type="submit" class="ve-btn-primary">Filtrer</button>
            @if ($selectedQuartier)<a href="{{ route('coupures.index') }}" class="pf-reset">Réinitialiser</a>@endif
        </form>
        <div class="pf-grid">
            @forelse ($coupures as $coupure)
                <article class="pf-card wow fadeInUp"><div class="pf-card-body">
                    <span class="pf-badge">{{ $coupure->quartier->nom }} · {{ ucfirst($coupure->type) }}</span>
                    <h4>{{ $coupure->quartier->ville }}</h4>
                    <p><span class="badge {{ $coupure->statut === 'en_cours' ? 'bg-danger' : 'bg-warning text-dark' }}">{{ $coupure->statut === 'en_cours' ? 'En cours' : 'Prévue' }}</span></p>
                    <p><i class="fa fa-clock-o" aria-hidden="true"></i> Début : {{ $coupure->date_debut->format('d/m/Y H:i') }}</p>
                    <p><i class="fa fa-clock-o" aria-hidden="true"></i> Fin : {{ $coupure->date_fin?->format('d/m/Y H:i') ?? 'Non précisée' }}</p>
                    @if ($coupure->description)<p>{{ \Illuminate\Support\Str::limit($coupure->description, 160) }}</p>@endif
                    <a href="{{ route('coupures.show', $coupure) }}" class="pf-link">Détail <i class="fa fa-arrow-right" aria-hidden="true"></i></a>
                </div></article>
            @empty
                <div class="pf-empty"><i class="fa fa-bolt" aria-hidden="true"></i><p>Aucune coupure prévue ou en cours pour ce quartier.</p><a href="{{ route('coupures.index') }}" class="ve-btn-ghost">Voir tous les quartiers</a></div>
            @endforelse
        </div>
        <x-pagination :paginator="$coupures" variant="bootstrap-4" />
    </div></section>
@endsection