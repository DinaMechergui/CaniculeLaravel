@extends('back.layouts.back')
@section('title', 'Gestion des alertes')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <x-page-header title="Alertes" :subtitle="'Événements à risque surveillés (' . $alertes->total() . ')'">
        @slot('actions')
            <a href="{{ route('admin.alertes.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Nouvelle alerte
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

    {{-- Filtres et recherche --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.alertes.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Recherche par mot-clé</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text"
                               name="q"
                               class="form-control"
                               placeholder="Titre, zone, organisme..."
                               value="{{ request('q') }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Filtrer par aléa</label>
                    <select name="type" class="form-select">
                        <option value="">Tous les types</option>
                        @foreach (\App\Models\Alerte::TYPES as $key => $label)
                            <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small fw-semibold">Filtrer par statut</label>
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="en_cours" @selected(request('statut') === 'en_cours')>🟡 En cours</option>
                        <option value="terminee" @selected(request('statut') === 'terminee')>⚪ Terminée</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-filter-alt me-1"></i> Filtrer
                    </button>
                    @if (request()->hasAny(['q', 'type', 'statut']))
                        <a href="{{ route('admin.alertes.index') }}" class="btn btn-outline-secondary" title="Réinitialiser les filtres">
                            <i class="bx bx-reset"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Tableau des alertes --}}
    <div class="card shadow-sm border-0">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Titre & Type</th>
                        <th>Zone concernée</th>
                        <th class="text-center">Niveau actuel</th>
                        <th class="text-center">Bulletins</th>
                        <th class="text-center">Statut</th>
                        <th>Dates</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($alertes as $alerte)
                        @php
                            $dernier = $alerte->dernier_bulletin;
                            $niveau = $alerte->niveau_actuel;
                            $badgeClass = match ($niveau) {
                                'rouge' => 'bg-danger text-white',
                                'orange' => 'bg-warning text-dark',
                                'jaune' => 'bg-info text-dark',
                                'vert' => 'bg-success text-white',
                                default => 'bg-secondary text-white',
                            };
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.alertes.show', $alerte) }}" class="fw-semibold text-primary">
                                    {{ $alerte->titre }}
                                </a>
                                <div class="small text-muted">
                                    <span class="badge bg-label-secondary me-1">{{ $alerte->type_label }}</span>
                                    @if ($alerte->source)
                                        <i class="bx bx-buildings ms-1"></i> {{ $alerte->source }}
                                    @endif
                                </div>
                            </td>

                            <td>
                                <i class="bx bx-map text-muted me-1"></i> {{ $alerte->zone_concernee }}
                            </td>

                            <td class="text-center">
                                <span class="badge {{ $badgeClass }} px-3 py-2 text-uppercase fw-bold shadow-xs">
                                    {{ $niveau === 'gris' ? 'Aucun bulletin' : 'Vigilance ' . $niveau }}
                                </span>
                                @if ($dernier)
                                    <div class="small text-muted mt-1">
                                        <i class="bx {{ $dernier->evolution_icon }} me-1"></i>{{ $dernier->evolution_label }}
                                    </div>
                                @endif
                            </td>

                            <td class="text-center">
                                <a href="{{ route('admin.alertes.show', $alerte) }}" class="badge bg-label-primary px-2 py-1">
                                    <i class="bx bx-file me-1"></i>{{ $alerte->bulletins_count }} bulletin(s)
                                </a>
                            </td>

                            <td class="text-center">
                                @if ($alerte->statut === 'en_cours')
                                    <span class="badge bg-label-warning fw-semibold">
                                        <i class="bx bx-loader-circle me-1 bx-spin"></i> En cours
                                    </span>
                                @else
                                    <span class="badge bg-label-secondary fw-semibold">
                                        <i class="bx bx-check-circle me-1"></i> Terminée
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="small">
                                    <span class="text-muted">Début :</span> {{ $alerte->date_debut->format('d/m/Y H:i') }}
                                </div>
                                @if ($alerte->date_fin)
                                    <div class="small text-muted">
                                        <span>Fin :</span> {{ $alerte->date_fin->format('d/m/Y H:i') }}
                                    </div>
                                @endif
                            </td>

                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="{{ route('admin.alertes.show', $alerte) }}" class="btn btn-sm btn-outline-primary" title="Voir l'historique et la chronologie">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    <a href="{{ route('admin.alertes.edit', $alerte) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.alertes.destroy', $alerte) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette alerte ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer" @disabled($alerte->bulletins_count > 0)>
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bx bx-bell-off bx-lg text-muted mb-2 d-block"></i>
                                <p class="text-muted mb-3">Aucune alerte trouvée correspondant à vos critères.</p>
                                <a href="{{ route('admin.alertes.create') }}" class="btn btn-sm btn-primary">
                                    <i class="bx bx-plus me-1"></i> Créer la première alerte
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($alertes->hasPages())
            <div class="card-footer bg-transparent border-top py-3 d-flex justify-content-center">
                {{ $alertes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
