@extends('front.layouts.front')

@section('title', 'Alertes & Vigilance')
@section('description', 'Suivi officiel en temps réel des alertes et bulletins de vigilance pour protéger la population.')

@section('content')
    <section class="ve-section pf-section">
        <div class="container">
            {{-- En-tête de section --}}
            <div class="ve-section-header text-center">
                <span class="ve-section-tag">Sécurité & Vigilance</span>
                <h2>Alertes <span>en cours</span></h2>
                <p>
                    Suivez le cycle de vie et l'évolution en temps réel des risques majeurs (canicule, orages, inondations...).
                    {{ $alertes->total() }} alerte(s) en cours de surveillance.
                </p>
            </div>

            {{-- Barre de recherche --}}
            <form method="GET" action="{{ route('alertes.index') }}" class="pf-filters mb-4" novalidate id="alertes-search-form">
                @if (!empty($selectedType))
                    <input type="hidden" name="type" value="{{ $selectedType }}">
                @endif

                <div class="pf-filter-field" style="flex: 1 1 380px;">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <input
                        type="text"
                        name="search"
                        id="alertes-search-input"
                        value="{{ $search }}"
                        placeholder="Rechercher par titre, ville ou zone (ex: Tunis, Bizerte)..."
                        autocomplete="off"
                    >
                </div>

                <button type="submit" class="ve-btn-primary">
                    <i class="fa fa-search me-1"></i> Rechercher
                </button>

                @if (!empty($search) || !empty($selectedType))
                    <a href="{{ route('alertes.index') }}" class="pf-reset">
                        <i class="fa fa-times me-1"></i> Réinitialiser
                    </a>
                @endif
            </form>

            {{-- Filtres par type d'événement --}}
            <div class="d-flex justify-content-center flex-wrap gap-2 mb-4">
                <a href="{{ route('alertes.index', array_filter(['search' => $search])) }}"
                   class="btn btn-sm rounded-pill px-3 py-2 {{ empty($selectedType) ? 'btn-primary shadow-sm' : 'btn-outline-secondary' }}">
                    <i class="fa fa-th-large me-1"></i> Toutes les alertes
                </a>
                @foreach ($types as $key => $label)
                    <a href="{{ route('alertes.index', array_filter(['type' => $key, 'search' => $search])) }}"
                       class="btn btn-sm rounded-pill px-3 py-2 {{ $selectedType === $key ? 'btn-primary shadow-sm' : 'btn-outline-secondary' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- Grille des alertes --}}
            <div class="row g-4 mt-2">
                @forelse ($alertes as $alerte)
                    @php
                        $dernier = $alerte->dernier_bulletin;
                        $niveau = $alerte->niveau_actuel;
                        $borderStyle = match($niveau) {
                            'rouge' => 'border-left: 6px solid #dc3545 !important;',
                            'orange' => 'border-left: 6px solid #fd7e14 !important;',
                            'jaune' => 'border-left: 6px solid #ffc107 !important;',
                            'vert' => 'border-left: 6px solid #198754 !important;',
                            default => 'border-left: 6px solid #6c757d !important;',
                        };
                        $badgeBg = match($niveau) {
                            'rouge' => 'bg-danger text-white',
                            'orange' => 'bg-warning text-dark',
                            'jaune' => 'bg-warning text-dark',
                            'vert' => 'bg-success text-white',
                            default => 'bg-secondary text-white',
                        };
                    @endphp

                    <div class="col-lg-6 alerte-card-col">
                        <article class="card h-100 shadow-sm border-0 rounded-3 overflow-hidden position-relative" style="background: #ffffff; {{ $borderStyle }}">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                                    {{-- Badge de niveau de vigilance actuel --}}
                                    <span class="badge {{ $badgeBg }} px-3 py-2 fs-6 text-uppercase fw-bold shadow-xs">
                                        <i class="fa fa-bell me-1"></i> Vigilance {{ $niveau }}
                                    </span>

                                    <span class="badge bg-light text-dark border px-2 py-1">
                                        <i class="fa fa-tag me-1 text-primary"></i> {{ $alerte->type_label }}
                                    </span>
                                </div>

                                <h3 class="h5 fw-bold mb-2">
                                    <a href="{{ route('alertes.show', $alerte->id) }}" class="text-dark text-decoration-none hover-primary">
                                        {{ $alerte->titre }}
                                    </a>
                                </h3>

                                <div class="mb-3 text-muted small">
                                    <span class="me-3">
                                        <i class="fa fa-map-marker text-danger me-1"></i><strong>Zone :</strong> {{ $alerte->zone_concernee }}
                                    </span>
                                    <span>
                                        <i class="fa fa-clock-o text-muted me-1"></i>Depuis le {{ $alerte->date_debut->format('d/m/Y à H:i') }}
                                    </span>
                                </div>

                                <p class="text-secondary small mb-3 line-clamp-2">
                                    {{ \Illuminate\Support\Str::limit($alerte->description, 130) }}
                                </p>

                                {{-- Aperçu du dernier bulletin officiel --}}
                                @if ($dernier)
                                    <div class="p-3 mb-3 rounded bg-light border-start border-3 {{ $niveau === 'rouge' ? 'border-danger' : ($niveau === 'orange' ? 'border-warning' : 'border-info') }}">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="small fw-bold text-dark">
                                                <i class="fa fa-history me-1 text-primary"></i>Dernier bulletin de suivi
                                            </span>
                                            <span class="small text-muted">
                                                {{ $dernier->date_publication->format('d/m H:i') }}
                                            </span>
                                        </div>
                                        <p class="mb-0 small text-muted text-truncate" title="{{ $dernier->message }}">
                                            {{ $dernier->message }}
                                        </p>
                                    </div>
                                @endif

                                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                                    <span class="small text-muted">
                                        <i class="fa fa-file-text-o me-1"></i>{{ $alerte->bulletins->count() }} mise(s) à jour
                                    </span>
                                    <a href="{{ route('alertes.show', $alerte->id) }}" class="ve-btn-primary py-2 px-3 fs-6">
                                        Suivre l'évolution <i class="fa fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <div class="p-5 bg-white rounded-3 shadow-sm border">
                            @if (!empty($search) || !empty($selectedType))
                                <i class="fa fa-search text-warning fa-3x mb-3 d-block"></i>
                                <h4 class="fw-bold text-dark mb-2">Aucune alerte trouvée</h4>
                                <p class="text-muted mb-3">
                                    Aucune alerte ne correspond à votre recherche
                                    @if(!empty($search)) pour <strong>« {{ $search }} »</strong>@endif
                                    @if(!empty($selectedType)) dans la catégorie <strong>{{ $types[$selectedType] ?? $selectedType }}</strong>@endif.
                                </p>
                                <a href="{{ route('alertes.index') }}" class="btn btn-outline-primary">
                                    <i class="fa fa-refresh me-1"></i> Réinitialiser les filtres
                                </a>
                            @else
                                <i class="fa fa-shield text-success fa-4x mb-3 d-block"></i>
                                <h4 class="fw-bold text-success mb-2">Aucune alerte majeure en cours</h4>
                                <p class="text-muted mb-0">
                                    La situation est stable sur l'ensemble des zones surveillées.
                                    Nos services maintiennent une veille permanente 24h/24.
                                </p>
                            @endif
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if ($alertes->hasPages())
                <div class="d-flex justify-content-center mt-5">
                    {{ $alertes->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('alertes-search-input');
        if (!searchInput) return;

        // Filtrage instantané sur les cartes affichées
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.alerte-card-col');
            let visibleCount = 0;

            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                const matches = query === '' || text.includes(query);
                card.style.display = matches ? '' : 'none';
                if (matches) visibleCount++;
            });

            let liveEmptyMsg = document.getElementById('live-empty-message');
            if (visibleCount === 0 && cards.length > 0) {
                if (!liveEmptyMsg) {
                    liveEmptyMsg = document.createElement('div');
                    liveEmptyMsg.id = 'live-empty-message';
                    liveEmptyMsg.className = 'col-12 text-center py-4';
                    liveEmptyMsg.innerHTML = '<div class="p-4 bg-white rounded-3 shadow-sm border text-muted"><i class="fa fa-search fa-2x mb-2 text-warning d-block"></i>Aucune alerte ne correspond à cette saisie sur cette page.<br><small class="text-muted">Appuyez sur Entrée ou cliquez sur « Rechercher » pour lancer une recherche complète dans la base.</small></div>';
                    cards[0].parentElement.appendChild(liveEmptyMsg);
                }
                liveEmptyMsg.style.display = '';
            } else if (liveEmptyMsg) {
                liveEmptyMsg.style.display = 'none';
            }
        });
    });
</script>
@endpush
