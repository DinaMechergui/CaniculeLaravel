@extends('back.layouts.back')

@section('title', 'Détail d\'une catégorie')

@section('content')
    <x-page-header :title="$categorie->nom" :subtitle="'Catégorie #' . $categorie->id">
        @slot('actions')
            <a href="{{ route('admin.categories-conseil.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
            <a href="{{ route('admin.categories-conseil.edit', $categorie) }}" class="btn btn-primary">
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
                        <div class="col-sm-4 text-muted">Nom</div>
                        <div class="col-sm-8">{{ $categorie->nom }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Description</div>
                        <div class="col-sm-8">{{ $categorie->description ?? '—' }}</div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4 text-muted">Créée le</div>
                        <div class="col-sm-8">{{ $categorie->created_at?->format('d/m/Y H:i') }}</div>
                    </div>

                    <hr>

                    <form method="POST"
                          action="{{ route('admin.categories-conseil.destroy', $categorie) }}"
                          onsubmit="return confirm('Confirmer la suppression de cette catégorie ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bx bx-trash me-1"></i> Supprimer cette catégorie
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Conseils ({{ $conseils->count() }})</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Titre</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($conseils as $conseil)
                                    <tr>
                                        <td>{{ $conseil->id }}</td>
<td>
    <a href="{{ route('admin.conseils.show', $conseil) }}">{{ $conseil->titre }}</a>
</td>                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-4">
                                            Aucun conseil dans cette catégorie.
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
