{{-- Formulaire : changement du mot de passe --}}
<h2>Mot de <span>passe</span></h2>
<p>Utilisez au moins 8 caractères.</p>

<form method="POST" action="{{ route('password.update') }}" class="ve-contact-form" novalidate>
    @csrf
    @method('PUT')

    <div class="ve-form-group app-form-group">
        <label for="current_password">Mot de passe actuel <span class="app-required">*</span></label>
        <input
            type="password"
            name="current_password"
            id="current_password"
            class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
            required
            autocomplete="current-password"
        >
        @error('current_password', 'updatePassword')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="ve-form-group app-form-group">
        <label for="password">Nouveau mot de passe <span class="app-required">*</span></label>
        <input
            type="password"
            name="password"
            id="password"
            class="form-control @error('password', 'updatePassword') is-invalid @enderror"
            required
            autocomplete="new-password"
        >
        @error('password', 'updatePassword')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="ve-form-group app-form-group">
        <label for="password_confirmation">Confirmer le mot de passe <span class="app-required">*</span></label>
        <input
            type="password"
            name="password_confirmation"
            id="password_confirmation"
            class="form-control"
            required
            autocomplete="new-password"
        >
    </div>

    <button type="submit" class="ve-btn-primary">Mettre à jour le mot de passe</button>
</form>
