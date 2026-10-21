@extends('back.layouts.back')

@section('title', 'Détail d\'un conseil')

@section('content')
    <x-page-header :title="$conseil->titre" :subtitle="'Conseil #' . $conseil->id">
        @slot('actions')
            <a href="{{ route('admin.conseils.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
            <a href="{{ route('admin.conseils.edit', $conseil) }}" class="btn btn-primary">
                <i class="bx bx-edit me-1"></i> Modifier
            </a>
        @endslot
    </x-page-header>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Informations</h5></div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Catégorie</div>
                        <div class="col-sm-8">
                            <a href="{{ route('admin.categories-conseil.show', $conseil->categorie) }}">
                                {{ $conseil->categorie->nom }}
                            </a>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Titre</div>
                        <div class="col-sm-8">{{ $conseil->titre }}</div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4 text-muted">Créé le</div>
                        <div class="col-sm-8">{{ $conseil->created_at?->format('d/m/Y H:i') }}</div>
                    </div>

                    <hr>

                    <form method="POST"
                          action="{{ route('admin.conseils.destroy', $conseil) }}"
                          onsubmit="return confirm('Confirmer la suppression de ce conseil ?');" novalidate>
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bx bx-trash me-1"></i> Supprimer ce conseil
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Contenu</h5></div>
                <div class="card-body">
                    {!! nl2br(e($conseil->contenu)) !!}
                </div>
            </div>
        </div>
    </div>

    {{-- Campagnes associées --}}
    @if ($conseil->campagnes->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header fw-semibold">
                <i class="bx bx-broadcast me-1"></i> Campagnes associées ({{ $conseil->campagnes->count() }})
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr><th>Titre</th><th>Canal</th><th>Public</th><th>Période</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($conseil->campagnes as $campagne)
                            <tr>
                                <td><a href="{{ route('admin.campagnes-conseil.show', $campagne) }}">{{ $campagne->titre }}</a></td>
                                <td>{{ $campagne->canal_label }}</td>
                                <td>{{ $campagne->public_label }}</td>
                                <td>{{ $campagne->date_debut->format('d/m/Y') }} → {{ $campagne->date_fin?->format('d/m/Y') ?? 'Sans fin' }}</td>
                                <td><span class="badge bg-{{ $campagne->statut_badge }}">{{ $campagne->statut_label }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
