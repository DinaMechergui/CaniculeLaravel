{{-- Formulaire : suppression du compte --}}
<h2>Supprimer <span>mon compte</span></h2>
<p>
    Cette action est <strong>définitive</strong>. Vos données seront supprimées
    de manière irréversible.
</p>

<form method="POST" action="{{ route('profile.destroy') }}" class="ve-contact-form"
      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.');" novalidate>
    @csrf
    @method('DELETE')

    <div class="ve-form-group app-form-group">
        <label for="password_delete">Mot de passe <span class="app-required">*</span></label>
        <input
            type="password"
            name="password"
            id="password_delete"
            class="form-control @error('password', 'userDeletion') is-invalid @enderror"
            placeholder="Saisissez votre mot de passe pour confirmer"
            required
            autocomplete="current-password"
        >
        @error('password', 'userDeletion')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="ve-btn-danger">Supprimer définitivement</button>
</form>
