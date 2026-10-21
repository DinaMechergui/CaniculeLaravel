@extends('back.layouts.back')
@section('title', 'Bulletins de suivi d\'alerte')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <x-page-header title="Bulletins de suivi" :subtitle="'Historique des mises à jour officielles (' . $bulletins->total() . ')'">
        @slot('actions')
            <a href="{{ route('admin.bulletins-alerte.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Nouveau bulletin
            </a>
        @endslot
    </x-page-header>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible shadow-sm mb-4" role="alert">
            <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible shadow-sm mb-4" role="alert">
            <i class="bx bx-error-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    @endif

    {{-- Filtres --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.bulletins-alerte.index') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Filtrer par niveau de vigilance</label>
                    <select name="niveau" class="form-select">
                        <option value="">Tous les niveaux</option>
                        <option value="vert" @selected(request('niveau') === 'vert')>🟢 Vert</option>
                        <option value="jaune" @selected(request('niveau') === 'jaune')>🟡 Jaune</option>
                        <option value="orange" @selected(request('niveau') === 'orange')>🟠 Orange</option>
                        <option value="rouge" @selected(request('niveau') === 'rouge')>🔴 Rouge</option>
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Filtrer par évolution</label>
                    <select name="evolution" class="form-select">
                        <option value="">Toutes les évolutions</option>
                        <option value="aggravation" @selected(request('evolution') === 'aggravation')>↗ Aggravation</option>
                        <option value="stable" @selected(request('evolution') === 'stable')>→ Stable</option>
                        <option value="amelioration" @selected(request('evolution') === 'amelioration')>↘ Amélioration</option>
                        <option value="levee" @selected(request('evolution') === 'levee')>✓ Levée</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-filter-alt me-1"></i> Filtrer
                    </button>
                    @if (request()->hasAny(['niveau', 'evolution', 'alerte_id']))
                        <a href="{{ route('admin.bulletins-alerte.index') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                            <i class="bx bx-reset"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Table des bulletins --}}
    <div class="card shadow-sm border-0">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Niveau</th>
                        <th>Évolution</th>
                        <th>Alerte parente</th>
                        <th>Message officiel</th>
                        <th class="text-center">Diffusions</th>
                        <th>Date publication</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bulletins as $bulletin)
                        <tr>
                            <td>
                                <span class="badge {{ $bulletin->niveau_badge_class }} text-uppercase fw-bold shadow-xs">
                                    {{ $bulletin->niveau }}
                                </span>
                                @if ($bulletin->temperature_max !== null)
                                    <div class="small text-danger fw-semibold mt-1">
                                        <i class="bx bxs-thermometer"></i>{{ $bulletin->temperature_max }}°C
                                    </div>
                                @endif
                            </td>

                            <td>
                                <span class="badge bg-label-dark">
                                    <i class="bx {{ $bulletin->evolution_icon }} me-1"></i>{{ $bulletin->evolution_label }}
                                </span>
                            </td>

                            <td>
                                <a href="{{ route('admin.alertes.show', $bulletin->alerte) }}" class="fw-semibold text-primary">
                                    {{ $bulletin->alerte->titre }}
                                </a>
                                <div class="small text-muted">
                                    <i class="bx bx-map-pin me-1"></i>{{ $bulletin->alerte->zone_concernee }}
                                    · <span class="badge bg-label-secondary">{{ $bulletin->alerte->type_label }}</span>
                                </div>
                            </td>

                            <td>
                                <div class="text-truncate" style="max-width: 320px;" title="{{ $bulletin->message }}">
                                    {{ $bulletin->message }}
                                </div>
                                @if ($bulletin->conduite_a_tenir)
                                    <div class="small text-muted text-truncate" style="max-width: 320px;">
                                        <i class="bx bx-shield-quarter me-1 text-warning"></i>{{ $bulletin->conduite_a_tenir }}
                                    </div>
                                @endif
                            </td>

                            <td class="text-center">
                                <a href="{{ route('admin.bulletins-alerte.show', $bulletin) }}" class="badge bg-label-info px-2 py-1">
                                    <i class="bx bx-broadcast me-1"></i>{{ $bulletin->diffusions->count() }} canal/canaux
                                </a>
                            </td>

                            <td>
                                <span class="small fw-semibold text-dark">
                                    {{ $bulletin->date_publication->format('d/m/Y H:i') }}
                                </span>
                            </td>

                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="{{ route('admin.bulletins-alerte.show', $bulletin) }}" class="btn btn-sm btn-outline-primary" title="Voir les détails">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    <a href="{{ route('admin.bulletins-alerte.edit', $bulletin) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.bulletins-alerte.destroy', $bulletin) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce bulletin ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer" @disabled($bulletin->diffusions->isNotEmpty())>
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bx bx-file-blank bx-lg text-muted mb-2 d-block"></i>
                                <p class="text-muted mb-3">Aucun bulletin de suivi trouvé.</p>
                                <a href="{{ route('admin.bulletins-alerte.create') }}" class="btn btn-sm btn-primary">
                                    <i class="bx bx-plus me-1"></i> Rédiger le premier bulletin
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($bulletins->hasPages())
            <div class="card-footer bg-transparent border-top py-3 d-flex justify-content-center">
                {{ $bulletins->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
