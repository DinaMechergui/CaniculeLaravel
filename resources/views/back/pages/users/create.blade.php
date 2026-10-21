@extends('back.layouts.back')

@section('title', 'Nouvel utilisateur')

@section('content')
    <x-page-header title="Nouvel utilisateur" subtitle="Créer un compte dans le back office">
        @slot('actions')
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
            </a>
        @endslot
    </x-page-header>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.store') }}" novalidate>
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <x-form-input
                            name="name"
                            label="Nom complet"
                            :value="old('name')"
                            placeholder="Ex. Marie Dupont"
                            required
                            autocomplete="name"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form-input
                            name="email"
                            type="email"
                            label="Adresse e-mail"
                            :value="old('email')"
                            placeholder="exemple@domaine.fr"
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
                            label="Mot de passe"
                            placeholder="8 caractères minimum"
                            required
                            autocomplete="new-password"
                        />
                    </div>
                    <div class="col-md-6">
                        <x-form-input
                            name="password_confirmation"
                            type="password"
                            label="Confirmer le mot de passe"
                            placeholder="Retapez le mot de passe"
                            required
                            autocomplete="new-password"
                        />
                    </div>
                </div>

                <x-form-select name="role" label="Rôle" :value="old('role', 'user')" required>
                    <option value="user" @selected(old('role', 'user') === 'user')>Utilisateur</option>
                    <option value="admin" @selected(old('role') === 'admin')>Administrateur</option>
                </x-form-select>

                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i> Enregistrer
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
@endsection
