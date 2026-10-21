@extends('back.layouts.back')

@section('title', 'Détail du signalement')

@section('content')
    <x-page-header :title="$signalement->typeSignalement?->libelle ? 'Signalement : ' . $signalement->typeSignalement->libelle : 'Détail du signalement'" subtitle="Détail du signalement">
        @slot('actions')
            <a href="{{ route('admin.signalements.edit', $signalement) }}" class="btn btn-primary">
                <i class="bx bx-edit me-1"></i> Modifier
            </a>
            <a href="{{ route('admin.signalements.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        @endslot
    </x-page-header>

    <div class="row">
        <div class="col-md-8">
            {{-- Détails du signalement --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations du signalement</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">

                        <dt class="col-sm-4">Type</dt>
                        <dd class="col-sm-8">
                            <a href="{{ route('admin.types-signalement.show', $signalement->typeSignalement) }}">
                                {{ $signalement->typeSignalement?->libelle ?? 'N/A' }}
                            </a>
                        </dd>

                        <dt class="col-sm-4">Auteur</dt>
                        <dd class="col-sm-8">{{ $signalement->user?->name ?? 'N/A' }}</dd>

                        <dt class="col-sm-4">Statut</dt>
                        <dd class="col-sm-8">
                            @php
                                $badgeClass = match($signalement->statut) {
                                    'nouveau'  => 'badge bg-info',
                                    'en_cours' => 'badge bg-warning text-dark',
                                    'traite'   => 'badge bg-success',
                                    default    => 'badge bg-secondary',
                                };
                                $statuts = \App\Models\Signalement::STATUTS;
                            @endphp
                            <span class="{{ $badgeClass }}">
                                {{ $statuts[$signalement->statut] ?? $signalement->statut }}
                            </span>
                        </dd>

                        <dt class="col-sm-4">Priorité IA</dt>
                        <dd class="col-sm-8">
                            @php
                                $prioClass = match($signalement->priorite) {
                                    'faible'  => 'badge bg-secondary',
                                    'moyenne' => 'badge bg-warning text-dark',
                                    'urgente' => 'badge bg-danger',
                                    default   => 'badge bg-secondary',
                                };
                                $priorites = \App\Models\Signalement::PRIORITES;
                            @endphp
                            <span class="{{ $prioClass }}">
                                {{ strtoupper($priorites[$signalement->priorite] ?? $signalement->priorite) }}
                            </span>
                        </dd>

                        <dt class="col-sm-4">Créé le</dt>
                        <dd class="col-sm-8">{{ $signalement->created_at->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-4">Modifié le</dt>
                        <dd class="col-sm-8">{{ $signalement->updated_at->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>

            {{-- Description --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Description</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $signalement->description }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            {{-- Actions rapides --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('admin.signalements.edit', $signalement) }}" class="btn btn-primary">
                        <i class="bx bx-edit me-1"></i> Modifier ce signalement
                    </a>
                    <form method="POST"
                          action="{{ route('admin.signalements.destroy', $signalement) }}"
                          onsubmit="return confirm('Confirmer la suppression de ce signalement ?');" novalidate>
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bx bx-trash me-1"></i> Supprimer
                        </button>
                    </form>
                </div>
            </div>

            {{-- Diagnostic de la panne (1:1) --}}
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bx bx-wrench me-1"></i> Diagnostic</h5>
                </div>
                <div class="card-body">
                    @if ($signalement->diagnostic)
                        <p class="mb-2">
                            <strong>Zone :</strong>
                            {{ \App\Models\Diagnostic::ZONES[$signalement->diagnostic->zone_touchee] ?? $signalement->diagnostic->zone_touchee }}
                        </p>
                        <p class="mb-3">
                            <strong>Danger (IA) :</strong>
                            @include('back.diagnostics._badge-danger', ['niveau' => $signalement->diagnostic->niveau_danger])
                        </p>
                        <a href="{{ route('admin.diagnostics.show', $signalement->diagnostic) }}" class="btn btn-outline-primary w-100">
                            <i class="bx bx-show me-1"></i> Voir le diagnostic
                        </a>
                    @else
                        <p class="text-muted">Aucun diagnostic pour ce signalement.</p>
                        <a href="{{ route('admin.diagnostics.create', ['signalement' => $signalement->id]) }}" class="btn btn-outline-primary w-100">
                            <i class="bx bx-plus me-1"></i> Ajouter un diagnostic
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
