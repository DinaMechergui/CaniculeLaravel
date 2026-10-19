@extends('back.layouts.back')

@section('title', 'Diagnostic #' . $diagnostic->id)

@section('content')
    <x-page-header :title="'Diagnostic #' . $diagnostic->id" subtitle="Détail du diagnostic de panne">
        @slot('actions')
            <a href="{{ route('admin.diagnostics.edit', $diagnostic) }}" class="btn btn-primary">
                <i class="bx bx-edit me-1"></i> Modifier
            </a>
            <form method="POST"
                  action="{{ route('admin.diagnostics.destroy', $diagnostic) }}"
                  class="d-inline"
                  onsubmit="return confirm('Supprimer définitivement ce diagnostic ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bx bx-trash me-1"></i> Supprimer
                </button>
            </form>
            <a href="{{ route('admin.diagnostics.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour
            </a>
        @endslot
    </x-page-header>

    <div class="row">
        {{-- Symptômes --}}
        <div class="col-md-7 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bx bx-list-check me-1"></i> Symptômes</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Signalement</dt>
                        <dd class="col-sm-7">
                            @if ($diagnostic->signalement)
                                <a href="{{ route('admin.signalements.show', $diagnostic->signalement) }}">
                                    #{{ $diagnostic->signalement->id }}
                                    ({{ $diagnostic->signalement->typeSignalement?->libelle ?? '—' }})
                                </a>
                            @else
                                —
                            @endif
                        </dd>

                        <dt class="col-sm-5">Zone touchée</dt>
                        <dd class="col-sm-7">{{ \App\Models\Diagnostic::ZONES[$diagnostic->zone_touchee] ?? $diagnostic->zone_touchee }}</dd>

                        <dt class="col-sm-5">Heure de la panne</dt>
                        <dd class="col-sm-7">{{ $diagnostic->heure_panne }}</dd>

                        <dt class="col-sm-5">Température</dt>
                        <dd class="col-sm-7">{{ $diagnostic->temperature !== null ? $diagnostic->temperature . ' °C' : '—' }}</dd>

                        @foreach (\App\Models\Diagnostic::SYMPTOMES as $champ => $label)
                            <dt class="col-sm-5 fw-normal">{{ $label }}</dt>
                            <dd class="col-sm-7">
                                @if ($diagnostic->$champ)
                                    <span class="badge bg-label-danger">Oui</span>
                                @else
                                    <span class="badge bg-label-secondary">Non</span>
                                @endif
                            </dd>
                        @endforeach
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-5 mb-4">
            {{-- Résultat IA --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bx bx-brain me-1"></i> Analyse IA</h5>
                </div>
                <div class="card-body">
                    @if ($diagnostic->estAnalyse())
                        <p class="mb-2">
                            <strong>Cause probable :</strong>
                            {{ \App\Models\Diagnostic::CAUSES[$diagnostic->cause_predite] ?? $diagnostic->cause_predite }}
                        </p>
                        <p class="mb-2">
                            <strong>Confiance :</strong> {{ round($diagnostic->confiance * 100) }} %
                        </p>
                        <p class="mb-0">
                            <strong>Danger :</strong>
                            @include('back.diagnostics._badge-danger', ['niveau' => $diagnostic->niveau_danger])
                        </p>
                    @else
                        <p class="text-muted mb-0">
                            <i class="bx bx-time-five me-1"></i>
                            Ce diagnostic n'a pas encore été analysé par le modèle IA.
                        </p>
                    @endif
                </div>
            </div>

            {{-- Confirmation admin --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bx bx-check-shield me-1"></i> Confirmation</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Cause réelle :</strong>
                        {{ $diagnostic->cause_reelle ? \App\Models\Diagnostic::CAUSES[$diagnostic->cause_reelle] : 'Pas encore confirmée' }}
                    </p>
                    @if ($diagnostic->estAnalyse() && $diagnostic->cause_reelle)
                        @if ($diagnostic->cause_predite === $diagnostic->cause_reelle)
                            <span class="badge bg-success"><i class="bx bx-check me-1"></i> L'IA avait raison</span>
                        @else
                            <span class="badge bg-warning text-dark"><i class="bx bx-x me-1"></i> L'IA s'est trompée</span>
                        @endif
                    @endif
                    <p class="mt-3 mb-0"><strong>Remarque :</strong> {{ $diagnostic->remarque ?: '—' }}</p>
                    <hr>
                    <small class="text-muted">
                        Créé le {{ $diagnostic->created_at->format('d/m/Y à H:i') }} —
                        modifié le {{ $diagnostic->updated_at->format('d/m/Y à H:i') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
@endsection
