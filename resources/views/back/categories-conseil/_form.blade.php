<form method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <x-form-input
        name="nom"
        label="Nom"
        :value="$categorie->nom"
        placeholder="Ex. Hydratation"
        required
    />

    <x-form-input
        name="description"
        label="Description"
        :value="$categorie->description"
        placeholder="Description facultative..."
    />

    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i> Enregistrer
        </button>
        <a href="{{ route('admin.categories-conseil.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
