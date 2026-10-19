@extends('back.layouts.back')

@section('title', 'Détails du niveau d\'alerte')

@section('content')
    <x-page-header title="Détails du niveau d'alerte" :subtitle="$niveauAlerte->libelle">
        @slot('actions')
            <a href="{{ route('admin.niveaux-alerte.edit', $niveauAlerte) }}" class="btn btn-primary">
                <i class="bx bx-edit me-1"></i> Modifier
            </a>
            <a href="{{ route('admin.niveaux-alerte.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        @endslot
    </x-page-header>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Informations générales</h5>
                </div>
                <div class="card-body mt-3">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Libellé</span>
                            <span>{{ $niveauAlerte->libelle }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Niveau</span>
                            <span class="badge bg-label-dark">Niveau {{ $niveauAlerte->niveau }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Couleur</span>
                            @switch($niveauAlerte->couleur)
                                @case('vert')
                                    <span class="badge bg-success">Vert</span>
                                    @break
                                @case('jaune')
                                    <span class="badge bg-warning text-dark">Jaune</span>
                                    @break
                                @case('orange')
                                    <span class="badge bg-orange text-white" style="background-color: #fd7e14 !important;">Orange</span>
                                    @break
                                @case('rouge')
                                    <span class="badge bg-danger">Rouge</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ $niveauAlerte->couleur }}</span>
                            @endswitch
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Alertes associées</span>
                            <span class="badge bg-primary">{{ $alertes->total() }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Alertes météo associées à ce niveau</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Titre</th>
                                    <th>Date début</th>
                                    <th>Date fin</th>
                                    <th>Source</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($alertes as $alerte)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.alertes-meteo.show', $alerte) }}" class="fw-semibold text-dark">
                                                {{ $alerte->titre }}
                                            </a>
                                        </td>
                                        <td>{{ $alerte->date_debut->format('d/m/Y H:i') }}</td>
                                        <td>{{ $alerte->date_fin ? $alerte->date_fin->format('d/m/Y H:i') : '-' }}</td>
                                        <td>{{ $alerte->source ?? 'N/A' }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.alertes-meteo.show', $alerte) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="bx bx-show"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Aucune alerte météo associée à ce niveau.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <x-pagination :paginator="$alertes" variant="bootstrap-5" />
            </div>
        </div>
    </div>
@endsection
