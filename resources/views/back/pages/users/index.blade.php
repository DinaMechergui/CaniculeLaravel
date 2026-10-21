@extends('back.layouts.back')

@section('title', 'Utilisateurs')

@section('content')
    <x-page-header title="Utilisateurs" :subtitle="'Liste des comptes (' . $users->total() . ')'">
        @slot('actions')
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Nouvel utilisateur
            </a>
        @endslot
    </x-page-header>

    {{-- Recherche --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users.index') }}" class="d-flex flex-wrap gap-2 align-items-end" novalidate>
                <div class="app-search-bar">
                    <label class="form-label" for="search">Rechercher</label>
                    <input
                        type="text"
                        class="form-control"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Nom ou adresse e-mail..."
                    >
                </div>
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bx bx-search me-1"></i> Rechercher
                </button>
                @if ($search)
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Liste des utilisateurs --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Nom</th>
                            <th>E-mail</th>
                            <th>Rôle</th>
                            <th>Inscrit le</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>
                                    <a href="{{ route('admin.users.show', $user) }}" class="fw-semibold">
                                        {{ $user->name }}
                                    </a>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge {{ $user->isAdmin() ? 'app-badge-admin' : 'app-badge-user' }}">
                                        {{ $user->isAdmin() ? 'Administrateur' : 'Utilisateur' }}
                                    </span>
                                </td>
                                <td>{{ $user->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('admin.users.show', $user) }}"
                                           class="btn btn-sm btn-outline-secondary"
                                           title="Voir">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Modifier">
                                            <i class="bx bx-edit"></i>
                                        </a>

                                        {{-- Un administrateur ne peut pas se supprimer lui-même --}}
                                        @if (! auth()->user()->is($user))
                                            <form method="POST"
                                                  action="{{ route('admin.users.destroy', $user) }}"
                                                  onsubmit="return confirm('Confirmer la suppression de cet utilisateur ?');" novalidate>
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    Aucun utilisateur ne correspond à votre recherche.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <x-pagination :paginator="$users" variant="bootstrap-5" />
@endsection
