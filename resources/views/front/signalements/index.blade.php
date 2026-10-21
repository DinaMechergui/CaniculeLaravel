@extends('front.layouts.front')

@section('title', 'Signalements')
@section('description', 'Consultez les signalements émis par les habitants : coupures, pannes et incidents liés à la canicule.')

@section('content')
    <section class="ve-section pf-section">
        <div class="container">
            {{-- En-tête de section --}}
            <div class="ve-section-header text-center">
                <span class="ve-section-tag">Signalements</span>
                <h2>Incidents et <span>alertes</span> du quartier</h2>
                <p>
                    Consultez les signalements émis par les habitants.
                    {{ $signalements->total() }} signalement(s) disponible(s).
                </p>
            </div>

            {{-- Filtres --}}
            <form method="GET" action="{{ route('signalements.index') }}" class="pf-filters">
                <div class="pf-filter-field">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Rechercher dans les signalements..."
                    >
                </div>

                <div class="pf-filter-field pf-filter-select">
                    <i class="fa fa-filter" aria-hidden="true"></i>
                    <select name="type">
                        <option value="">Tous les types</option>
                        @foreach ($types as $type)
                            <option value="{{ $type->id }}" @selected($selectedType == $type->id)>
                                {{ $type->libelle }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="ve-btn-primary">Filtrer</button>

                @if ($search || $selectedType)
                    <a href="{{ route('signalements.index') }}" class="pf-reset">Réinitialiser</a>
                @endif
            </form>

            {{-- Bouton créer (utilisateur connecté) --}}
            @auth
                <div class="text-center mb-4">
                    <a href="{{ route('signalements.create') }}" class="ve-btn-primary">
                        <i class="fa fa-plus" aria-hidden="true"></i> Soumettre un signalement
                    </a>
                </div>
            @endauth

            {{-- Liste des signalements --}}
            @if ($signalements->isEmpty())
                <div class="text-center py-5">
                    <i class="fa fa-exclamation-circle fa-3x mb-3" style="color:#e67e22;" aria-hidden="true"></i>
                    <p>Aucun signalement ne correspond à vos critères.</p>
                    <a href="{{ route('signalements.index') }}" class="ve-btn-ghost">Voir tous les signalements</a>
                </div>
            @else
                <div class="pf-grid">
                    @foreach ($signalements as $signalement)
                        <div class="pf-card wow fadeInUp">
                            <div class="pf-card-body">
                                <div class="pf-card-type">
                                    <i class="fa fa-flag" aria-hidden="true"></i>
                                    {{ $signalement->typeSignalement?->libelle ?? 'Signalement' }}
                                </div>
                                <h4 class="pf-card-name">
                                    <a href="{{ route('signalements.show', $signalement) }}">
                                        Signalement #{{ $signalement->id }}
                                    </a>
                                </h4>
                                <p class="pf-card-desc">
                                    {{ \Illuminate\Support\Str::limit($signalement->description, 120) }}
                                </p>
                                <div class="pf-card-meta">
                                    <span>
                                        @php
                                            $badgeStyle = match($signalement->statut) {
                                                'nouveau'  => 'background:#3498db;color:#fff;',
                                                'en_cours' => 'background:#f39c12;color:#fff;',
                                                'traite'   => 'background:#27ae60;color:#fff;',
                                                default    => 'background:#95a5a6;color:#fff;',
                                            };
                                            $prioStyle = match($signalement->priorite) {
                                                'faible'  => 'background:#95a5a6;color:#fff;',
                                                'moyenne' => 'background:#f39c12;color:#fff;',
                                                'urgente' => 'background:#e74c3c;color:#fff;',
                                                default   => 'background:#95a5a6;color:#fff;',
                                            };
                                            $priorites = \App\Models\Signalement::PRIORITES;
                                        @endphp
                                        <span style="padding:2px 8px;border-radius:4px;font-size:0.8em;{{ $badgeStyle }}margin-right:5px;">
                                            {{ $statuts[$signalement->statut] ?? $signalement->statut }}
                                        </span>
                                        <span style="padding:2px 8px;border-radius:4px;font-size:0.8em;{{ $prioStyle }}">
                                            {{ strtoupper($priorites[$signalement->priorite] ?? $signalement->priorite) }}
                                        </span>
                                    </span>
                                    <span>
                                        <i class="fa fa-user" aria-hidden="true"></i>
                                        {{ $signalement->user?->name ?? 'Anonyme' }}
                                    </span>
                                    <span>
                                        <i class="fa fa-calendar" aria-hidden="true"></i>
                                        {{ $signalement->created_at->format('d/m/Y') }}
                                    </span>
                                </div>
                            </div>
                            <div class="pf-card-footer">
                                <a href="{{ route('signalements.show', $signalement) }}" class="ve-btn-primary pf-card-btn">
                                    Voir le détail <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="pf-pagination text-center mt-4">
                    {{ $signalements->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
