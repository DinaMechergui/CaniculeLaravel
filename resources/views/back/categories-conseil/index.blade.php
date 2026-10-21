@extends('back.layouts.back')

@section('title', 'Catégories de conseils')

@section('content')
    <x-page-header title="Catégories de conseils" :subtitle="'Liste des catégories (' . $categories->total() . ')'">
        @slot('actions')
            <a href="{{ route('admin.categories-conseil.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Nouvelle catégorie
            </a>
        @endslot
    </x-page-header>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>Description</th>
                            <th>Conseils</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $categorie)
                            <tr>
                                <td>{{ $categorie->id }}</td>
                                <td>
                                    <a href="{{ route('admin.categories-conseil.show', $categorie) }}" class="fw-semibold">
                                        {{ $categorie->nom }}
                                    </a>
                                </td>
                                <td>{{ \Illuminate\Support\Str::limit($categorie->description, 60) }}</td>
                                <td><span class="badge app-badge-user">{{ $categorie->conseils_count }}</span></td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('admin.categories-conseil.show', $categorie) }}"
                                           class="btn btn-sm btn-outline-secondary" title="Voir">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('admin.categories-conseil.edit', $categorie) }}"
                                           class="btn btn-sm btn-outline-primary" title="Modifier">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <form method="POST"
                                              action="{{ route('admin.categories-conseil.destroy', $categorie) }}"
                                              onsubmit="return confirm('Confirmer la suppression de cette catégorie ?');">
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
                                <td colspan="5" class="text-center text-muted py-4">Aucune catégorie.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-pagination :paginator="$categories" variant="bootstrap-5" />
@endsection
