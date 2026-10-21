@extends('back.layouts.back')

@section('title', 'Détail d\'un type de point')

@section('content')
    <x-page-header :title="$typePoint->nom" :subtitle="'Type de point #' . $typePoint->id">
        @slot('actions')
            <a href="{{ route('admin.types-point.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
            <a href="{{ route('admin.types-point.edit', $typePoint) }}" class="btn btn-primary">
                <i class="bx bx-edit me-1"></i> Modifier
            </a>
        @endslot
    </x-page-header>

    <div class="row">
        {{-- Informations du type --}}
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Nom</div>
                        <div class="col-sm-8">{{ $typePoint->nom }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Description</div>
                        <div class="col-sm-8">{{ $typePoint->description ?? '—' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Icône</div>
                        <div class="col-sm-8"><code>{{ $typePoint->icone ?? '—' }}</code></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4 text-muted">Créé le</div>
                        <div class="col-sm-8">{{ $typePoint->created_at?->format('d/m/Y H:i') }}</div>
                    </div>

                    <hr>

                    <form method="POST"
                          action="{{ route('admin.types-point.destroy', $typePoint) }}"
                          onsubmit="return confirm('Confirmer la suppression de ce type de point ?');" novalidate>
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bx bx-trash me-1"></i> Supprimer ce type
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Points rattachés à ce type --}}
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Points de fraîcheur ({{ $points->count() }})</h5>
                    <a href="{{ route('admin.points-fraicheur.create') }}" class="btn btn-sm btn-primary">
                        <i class="bx bx-plus me-1"></i> Ajouter un point
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Nom</th>
                                    <th>Adresse</th>
                                    <th>Accessible</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($points as $point)
                                    <tr>
                                        <td>{{ $point->id }}</td>
                                        <td>
                                            <a href="{{ route('admin.points-fraicheur.show', $point) }}" class="fw-semibold">
                                                {{ $point->nom }}
                                            </a>
                                        </td>
                                        <td>{{ $point->adresse }}</td>
                                        <td>
                                            <span class="badge {{ $point->accessible ? 'app-badge-admin' : 'app-badge-user' }}">
                                                {{ $point->accessible ? 'Oui' : 'Non' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.points-fraicheur.edit', $point) }}"
                                               class="btn btn-sm btn-outline-primary" title="Modifier">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Aucun point de fraîcheur n'est rattaché à ce type.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
