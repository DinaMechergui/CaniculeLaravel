@extends('back.layouts.back')
@section('title', 'Diffusions des bulletins d\'alerte')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <x-page-header title="Diffusions des bulletins" :subtitle="'Historique des envois multicanaux à la population (' . $diffusions->total() . ')'">
        @slot('actions')
            <a href="{{ route('admin.diffusions.create') }}" class="btn btn-primary">
                <i class="bx bx-broadcast me-1"></i> Programmer une diffusion
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

    {{-- Indicateur du total de destinataires informés --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0 bg-primary text-white">
                <div class="card-body d-flex align-items-center justify-content-between p-3 flex-wrap gap-2">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-white bg-opacity-25 p-3 me-3 text-white">
                            <i class="bx bx-user-check bx-md"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 text-white fw-bold">{{ number_format($totalDestinatairesEnvoyes, 0, ',', ' ') }}</h4>
                            <span class="small opacity-75">Destinataires cumulés informés via les diffusions envoyées avec succès</span>
                        </div>
                    </div>
                    <span class="badge bg-white text-primary fw-semibold px-3 py-2">
                        <i class="bx bx-broadcast me-1"></i>Transmission multicanal active
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.diffusions.index') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Filtrer par canal</label>
                    <select name="canal" class="form-select">
                        <option value="">Tous les canaux</option>
                        @foreach (\App\Models\Diffusion::CANAUX as $k => $lbl)
                            <option value="{{ $k }}" @selected(request('canal') === $k)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Filtrer par statut</label>
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="envoyee" @selected(request('statut') === 'envoyee')>🟢 Envoyée</option>
                        <option value="planifiee" @selected(request('statut') === 'planifiee')>🔵 Planifiée</option>
                        <option value="echec" @selected(request('statut') === 'echec')>🔴 Échec</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-filter-alt me-1"></i> Filtrer
                    </button>
                    @if (request()->hasAny(['canal', 'statut', 'bulletin_alerte_id']))
                        <a href="{{ route('admin.diffusions.index') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                            <i class="bx bx-reset"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Table des diffusions --}}
    <div class="card shadow-sm border-0">
        <div class="table-responsive text-nowrap">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Canal</th>
                        <th>Niveau lié</th>
                        <th>Alerte & Bulletin</th>
                        <th>Contenu court transmis</th>
                        <th class="text-center">Destinataires</th>
                        <th class="text-center">Statut</th>
                        <th>Date d'envoi</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($diffusions as $diff)
                        @php
                            $bulletin = $diff->bulletinAlerte;
                            $bBadgeClass = match ($bulletin?->niveau) {
                                'rouge' => 'bg-danger text-white',
                                'orange' => 'bg-warning text-dark',
                                'jaune' => 'bg-info text-dark',
                                'vert' => 'bg-success text-white',
                                default => 'bg-secondary text-white',
                            };
                        @endphp
                        <tr>
                            <td>
                                <span class="d-inline-flex align-items-center gap-2 fw-semibold text-dark">
                                    <i class="bx {{ $diff->canal_icon }} fs-4 text-primary"></i>
                                    {{ $diff->canal_label }}
                                </span>
                            </td>

                            <td>
                                @if ($bulletin)
                                    <span class="badge {{ $bBadgeClass }} text-uppercase fw-bold shadow-xs">
                                        {{ $bulletin->niveau }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">-</span>
                                @endif
                            </td>

                            <td>
                                @if ($bulletin)
                                    <a href="{{ route('admin.alertes.show', $bulletin->alerte) }}" class="fw-semibold text-primary">
                                        {{ $bulletin->alerte->titre }}
                                    </a>
                                    <div class="small text-muted">
                                        Bulletin du {{ $bulletin->date_publication->format('d/m H:i') }}
                                        · <span class="badge bg-label-dark">{{ $bulletin->evolution_label }}</span>
                                    </div>
                                @endif
                            </td>

                            <td>
                                <div class="text-truncate" style="max-width: 280px;" title="{{ $diff->contenu }}">
                                    « {{ $diff->contenu }} »
                                </div>
                            </td>

                            <td class="text-center">
                                <span class="badge bg-label-info fw-bold">
                                    <i class="bx bx-user me-1"></i>{{ number_format($diff->destinataires_estimes, 0, ',', ' ') }}
                                </span>
                            </td>

                            <td class="text-center">
                                <span class="badge {{ $diff->statut_badge_class }}">
                                    {{ $diff->statut }}
                                </span>
                            </td>

                            <td>
                                <span class="small text-muted">
                                    {{ $diff->date_envoi->format('d/m/Y H:i') }}
                                </span>
                            </td>

                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="{{ route('admin.diffusions.show', $diff) }}" class="btn btn-sm btn-outline-primary" title="Détail">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    <a href="{{ route('admin.diffusions.edit', $diff) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.diffusions.destroy', $diff) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cet enregistrement de diffusion ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bx bx-broadcast bx-lg text-muted mb-2 d-block"></i>
                                <p class="text-muted mb-3">Aucune diffusion trouvée.</p>
                                <a href="{{ route('admin.diffusions.create') }}" class="btn btn-sm btn-primary">
                                    <i class="bx bx-broadcast me-1"></i> Programmer la première diffusion
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($diffusions->hasPages())
            <div class="card-footer bg-transparent border-top py-3 d-flex justify-content-center">
                {{ $diffusions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
