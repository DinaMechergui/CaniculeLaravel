@extends('back.layouts.back')

@section('title', 'Modifier un utilisateur')

@section('content')
    <x-page-header :title="'Modifier : ' . $user->name" :subtitle="'Compte créé le ' . $user->created_at?->format('d/m/Y H:i')">
        @slot('actions')
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        @endslot
    </x-page-header>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" novalidate>
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <x-form-input
                            name="name"
                            label="Nom complet"
                            :value="$user->name"
                            required
                            autocomplete="name"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form-input
                            name="email"
                            type="email"
                            label="Adresse e-mail"
                            :value="$user->email"
                            required
                            autocomplete="email"
                        />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <x-form-input
                            name="password"
                            type="password"
                            label="Nouveau mot de passe"
                            placeholder="Laisser vide pour conserver l'actuel"
                            autocomplete="new-password"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form-input
                            name="password_confirmation"
                            type="password"
                            label="Confirmer le mot de passe"
                            placeholder="Laisser vide si inchangé"
                            autocomplete="new-password"
                        />
                    </div>
                </div>

                <x-form-select name="role" label="Rôle" :value="$user->role" required>
                    <option value="user" @selected($user->role === 'user')>Utilisateur</option>
                    <option value="admin" @selected($user->role === 'admin')>Administrateur</option>
                </x-form-select>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i> Mettre à jour
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
@endsection
