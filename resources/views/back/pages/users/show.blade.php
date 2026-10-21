@extends('back.layouts.back')

@section('title', 'Détail d\'un utilisateur')

@section('content')
    <x-page-header :title="$user->name" :subtitle="'Détails du compte #' . $user->id">
        @slot('actions')
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                <i class="bx bx-edit me-1"></i> Modifier
            </a>
        @endslot
    </x-page-header>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-3 text-muted">Identifiant</div>
                        <div class="col-sm-9">{{ $user->id }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3 text-muted">Nom complet</div>
                        <div class="col-sm-9">{{ $user->name }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3 text-muted">Adresse e-mail</div>
                        <div class="col-sm-9">{{ $user->email }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3 text-muted">Rôle</div>
                        <div class="col-sm-9">
                            <span class="badge {{ $user->isAdmin() ? 'app-badge-admin' : 'app-badge-user' }}">
                                {{ $user->isAdmin() ? 'Administrateur' : 'Utilisateur' }}
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3 text-muted">E-mail vérifié</div>
                        <div class="col-sm-9">
                            {{ $user->email_verified_at?->format('d/m/Y H:i') ?? 'Non vérifié' }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-3 text-muted">Inscrit le</div>
                        <div class="col-sm-9">{{ $user->created_at?->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-primary">
                        <i class="bx bx-edit me-1"></i> Modifier cet utilisateur
                    </a>

                    @if (! auth()->user()->is($user))
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                              onsubmit="return confirm('Confirmer la suppression de cet utilisateur ?');" novalidate>
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bx bx-trash me-1"></i> Supprimer cet utilisateur
                            </button>
                        </form>
                    @else
                        <div class="alert alert-info mb-0" role="alert">
                            Vous ne pouvez pas supprimer votre propre compte.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
