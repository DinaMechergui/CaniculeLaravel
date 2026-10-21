{{--
    Formulaire partagé (création / modification) d'un type de point.

    Variables attendues :
      $typePoint — modèle TypePoint (neuf ou existant)
      $action    — URL de traitement du formulaire
      $method    — POST (création) ou PUT (modification)
--}}
<form method="POST" action="{{ $action }}" novalidate>
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div class="row">
        <div class="col-md-6">
            <x-form-input
                name="nom"
                label="Nom"
                :value="$typePoint->nom"
                placeholder="Ex. Parc"
                required
            />
        </div>
        <div class="col-md-6">
            <x-form-input
                name="icone"
                label="Icône (classe Font Awesome)"
                :value="$typePoint->icone"
                placeholder="Ex. fa fa-tree"
            />
        </div>
    </div>

    <x-form-input
        name="description"
        label="Description"
        :value="$typePoint->description"
        placeholder="Description facultative du type de point..."
    />

    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i> Enregistrer
        </button>
        <a href="{{ route('admin.types-point.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
