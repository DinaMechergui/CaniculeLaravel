@extends('back.layouts.back')

@section('title', 'Campagnes de sensibilisation')

@section('content')
    <x-page-header title="Campagnes de sensibilisation" :subtitle="'Liste des campagnes (' . $campagnes->total() . ')'">
        @slot('actions')
            <a href="{{ route('admin.campagnes-conseil.statistiques') }}" class="btn btn-outline-info">
                <i class="bx bx-bar-chart-alt-2 me-1"></i> Statistiques
            </a>
            <a href="{{ route('admin.campagnes-conseil.export-csv', request()->query()) }}" class="btn btn-outline-success">
                <i class="bx bx-download me-1"></i> Export CSV
            </a>
            <a href="{{ route('admin.campagnes-conseil.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Nouvelle campagne
            </a>
        @endslot
    </x-page-header>

    {{-- Filtres --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.campagnes-conseil.index') }}" class="d-flex flex-wrap gap-2 align-items-end" novalidate>
                <div class="app-search-bar">
                    <label class="form-label" for="q">Rechercher</label>
                    <input type="text" class="form-control" id="q" name="q"
                           value="{{ $recherche }}" placeholder="Titre de la campagne...">
                </div>
                <div>
                    <label class="form-label" for="statut">Statut</label>
                    <select class="form-select" id="statut" name="statut">
                        <option value="">Tous les statuts</option>
                        @foreach ($statuts as $valeur => $libelle)
                            <option value="{{ $valeur }}" @selected($statutFiltre === $valeur)>{{ $libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" for="canal">Canal</label>
                    <select class="form-select" id="canal" name="canal">
                        <option value="">Tous les canaux</option>
                        @foreach ($canaux as $valeur => $libelle)
                            <option value="{{ $valeur }}" @selected(($canalFiltre ?? '') === $valeur)>{{ $libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" for="public">Public</label>
                    <select class="form-select" id="public" name="public">
                        <option value="">Tous les publics</option>
                        @foreach ($publics as $valeur => $libelle)
                            <option value="{{ $valeur }}" @selected(($publicFiltre ?? '') === $valeur)>{{ $libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bx bx-filter-alt me-1"></i> Filtrer
                </button>
                @if ($recherche !== '' || $statutFiltre || ($canalFiltre ?? '') || ($publicFiltre ?? ''))
                    <a href="{{ route('admin.campagnes-conseil.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Titre</th>
                            <th>Conseil</th>
                            <th>Public</th>
                            <th>Canal</th>
                            <th>Période</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($campagnes as $campagne)
                            <tr>
                                <td>{{ $campagne->id }}</td>
                                <td>
                                    <a href="{{ route('admin.campagnes-conseil.show', $campagne) }}" class="fw-semibold">
                                        {{ $campagne->titre }}
                                    </a>
                                    @if ($campagne->jours_restants !== null && $campagne->jours_restants <= 7 && $campagne->statut === 'active')
                                        <span class="badge bg-danger ms-1">{{ $campagne->jours_restants }}j restants</span>
                                    @endif
                                </td>
                                <td>{{ $campagne->conseil->titre }}</td>
                                <td>{{ $campagne->public_label }}</td>
                                <td>{{ $campagne->canal_label }}</td>
                                <td>
                                    {{ $campagne->date_debut->format('d/m/Y') }}
                                    →
                                    {{ $campagne->date_fin ? $campagne->date_fin->format('d/m/Y') : 'Sans fin' }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $campagne->statut_badge }}">{{ $campagne->statut_label }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('admin.campagnes-conseil.show', $campagne) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Voir">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('admin.campagnes-conseil.edit', $campagne) }}"
                                           class="btn btn-sm btn-outline-primary" title="Modifier">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('admin.campagnes-conseil.destroy', $campagne) }}"
                                              onsubmit="return confirm('Confirmer la suppression de cette campagne ?');" novalidate>
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
                                <td colspan="8" class="text-center text-muted py-4">Aucune campagne trouvée.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-pagination :paginator="$campagnes" variant="bootstrap-5" />
@endsection