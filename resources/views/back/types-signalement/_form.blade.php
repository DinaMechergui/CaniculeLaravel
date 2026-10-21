{{--
    Formulaire partagé (création / modification) d'un type de signalement.

    Variables attendues :
      $typeSignalement — modèle TypeSignalement (neuf ou existant)
      $action          — URL de traitement du formulaire
      $method          — POST (création) ou PUT (modification)
--}}
<form method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div class="mb-3">
        <label for="libelle" class="form-label">Libellé <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control @error('libelle') is-invalid @enderror"
            id="libelle"
            name="libelle"
            value="{{ old('libelle', $typeSignalement->libelle) }}"
            maxlength="100"
            placeholder="Ex. Coupure observée"
            required
        >
        @error('libelle')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i> Enregistrer
        </button>
        <a href="{{ route('admin.types-signalement.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
