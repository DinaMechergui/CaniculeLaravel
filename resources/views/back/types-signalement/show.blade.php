@extends('back.layouts.back')

@section('title', $typeSignalement->libelle)

@section('content')
    <x-page-header :title="$typeSignalement->libelle" subtitle="Détail du type de signalement">
        @slot('actions')
            <a href="{{ route('admin.types-signalement.edit', $typeSignalement) }}" class="btn btn-primary">
                <i class="bx bx-edit me-1"></i> Modifier
            </a>
            <a href="{{ route('admin.types-signalement.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        @endslot
    </x-page-header>

    {{-- Informations du type --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Informations</h5>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">ID</dt>
                <dd class="col-sm-9">{{ $typeSignalement->id }}</dd>

                <dt class="col-sm-3">Libellé</dt>
                <dd class="col-sm-9">{{ $typeSignalement->libelle }}</dd>

                <dt class="col-sm-3">Créé le</dt>
                <dd class="col-sm-9">{{ $typeSignalement->created_at->format('d/m/Y H:i') }}</dd>

                <dt class="col-sm-3">Modifié le</dt>
                <dd class="col-sm-9">{{ $typeSignalement->updated_at->format('d/m/Y H:i') }}</dd>
            </dl>
        </div>
    </div>

    {{-- Signalements rattachés --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Signalements rattachés ({{ $signalements->count() }})</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Auteur</th>
                            <th>Description</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($signalements as $signalement)
                            <tr>
                                <td>{{ $signalement->user?->name ?? 'N/A' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($signalement->description, 60) }}</td>
                                <td>
                                    @php
                                        $statuts = \App\Models\Signalement::STATUTS;
                                        $badgeClass = match($signalement->statut) {
                                            'nouveau'  => 'badge bg-info',
                                            'en_cours' => 'badge bg-warning text-dark',
                                            'traite'   => 'badge bg-success',
                                            default    => 'badge bg-secondary',
                                        };
                                    @endphp
                                    <span class="{{ $badgeClass }}">
                                        {{ $statuts[$signalement->statut] ?? $signalement->statut }}
                                    </span>
                                </td>
                                <td>{{ $signalement->created_at->format('d/m/Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.signalements.show', $signalement) }}"
                                       class="btn btn-sm btn-outline-secondary" title="Voir">
                                        <i class="bx bx-show"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    Aucun signalement rattaché à ce type.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
