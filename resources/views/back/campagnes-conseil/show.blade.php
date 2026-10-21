@extends('back.layouts.back')

@section('title', 'Détail de la campagne')

@section('content')
    <x-page-header title="{{ $campagne->titre }}" subtitle="Détail de la campagne">
        @slot('actions')
            {{-- Bouton Activer (brouillon -> active) --}}
            @if ($campagne->statut === 'brouillon')
                <form method="POST" action="{{ route('admin.campagnes-conseil.activer', $campagne) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success"
                            onclick="return confirm('Activer cette campagne ?')">
                        <i class="bx bx-play me-1"></i> Activer
                    </button>
                </form>
            @endif

            {{-- Bouton Terminer (active -> terminee) --}}
            @if ($campagne->statut === 'active')
                <form method="POST" action="{{ route('admin.campagnes-conseil.terminer', $campagne) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-secondary"
                            onclick="return confirm('Marquer cette campagne comme terminée ?')">
                        <i class="bx bx-stop me-1"></i> Terminer
                    </button>
                </form>
            @endif

            {{-- Bouton Dupliquer --}}
            <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalDupliquer">
                <i class="bx bx-copy me-1"></i> Dupliquer
            </button>

            <a href="{{ route('admin.campagnes-conseil.edit', $campagne) }}" class="btn btn-primary">
                <i class="bx bx-edit me-1"></i> Modifier
            </a>
            <a href="{{ route('admin.campagnes-conseil.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour
            </a>
        @endslot
    </x-page-header>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header fw-semibold">Informations de la campagne</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Statut</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $campagne->statut_badge }}">{{ $campagne->statut_label }}</span>
                        </dd>

                        <dt class="col-sm-4">État calendrier</dt>
                        <dd class="col-sm-8">{{ $campagne->etat_calendrier }}</dd>

                        <dt class="col-sm-4">Public cible</dt>
                        <dd class="col-sm-8">{{ $campagne->public_label }}</dd>

                        <dt class="col-sm-4">Canal</dt>
                        <dd class="col-sm-8">{{ $campagne->canal_label }}</dd>

                        <dt class="col-sm-4">Date de début</dt>
                        <dd class="col-sm-8">{{ $campagne->date_debut->format('d/m/Y') }}</dd>

                        <dt class="col-sm-4">Date de fin</dt>
                        <dd class="col-sm-8">
                            {{ $campagne->date_fin ? $campagne->date_fin->format('d/m/Y') : 'Sans date de fin' }}
                        </dd>

                        @if ($campagne->duree_en_jours !== null)
                            <dt class="col-sm-4">Durée</dt>
                            <dd class="col-sm-8">{{ $campagne->duree_en_jours }} jour(s)</dd>
                        @endif

                        <dt class="col-sm-4">Activation auto</dt>
                        <dd class="col-sm-8">{{ $campagne->activation_auto ? 'Oui' : 'Non' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header fw-semibold">Conseil diffusé</div>
                <div class="card-body">
                    <h5 class="mb-1">
                        <a href="{{ route('admin.conseils.show', $campagne->conseil) }}">
                            {{ $campagne->conseil->titre }}
                        </a>
                    </h5>
                    <p class="text-muted mb-3">
                        Catégorie : {{ $campagne->conseil->categorie->nom }}
                    </p>
                    <p class="mb-0">{{ \Illuminate\Support\Str::limit($campagne->conseil->contenu, 300) }}</p>
                </div>
            </div>
        </div>
    </div>

    @if ($campagne->message)
        <div class="card mb-4">
            <div class="card-header fw-semibold">Message de diffusion</div>
            <div class="card-body">
                <p class="mb-0">{{ $campagne->message }}</p>
            </div>
        </div>
    @endif

    {{-- Modal de duplication --}}
    <div class="modal fade" id="modalDupliquer" tabindex="-1" aria-labelledby="modalDupliquerLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDupliquerLabel">Dupliquer cette campagne</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <form method="POST" action="{{ route('admin.campagnes-conseil.dupliquer', $campagne) }}" novalidate>
                    @csrf
                    <div class="modal-body">
                        <p>Une copie « Copie de {{ $campagne->titre }} » sera créée en statut brouillon.</p>
                        <div class="mb-3">
                            <label for="decalage_jours" class="form-label">
                                Décalage des dates (jours)
                            </label>
                            <input type="number" name="decalage_jours" id="decalage_jours"
                                   class="form-control" value="30">
                            @error('decalage_jours')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-copy me-1"></i> Créer la copie
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection