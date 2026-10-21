{{--
    Formulaire partagé (création / modification) d'un point de fraîcheur.

    Variables attendues :
      $pointFraicheur — modèle PointFraicheur (neuf ou existant)
      $types          — collection TypePoint pour la liste déroulante
      $action         — URL de traitement du formulaire
      $method         — POST (création) ou PUT (modification)
--}}
<form method="POST" action="{{ $action }}" novalidate>
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div class="row">
        <div class="col-md-6">
            <x-form-select
                name="type_point_id"
                label="Type de point"
                :value="$pointFraicheur->type_point_id"
                placeholder="Sélectionnez un type..."
                required
            >
                @foreach ($types as $type)
                    <option value="{{ $type->id }}" @selected((int) old('type_point_id', $pointFraicheur->type_point_id) === $type->id)>
                        {{ $type->nom }}
                    </option>
                @endforeach
            </x-form-select>
        </div>
        <div class="col-md-6">
            <x-form-input
                name="nom"
                label="Nom"
                :value="$pointFraicheur->nom"
                placeholder="Ex. Parc du Belvédère"
                required
            />
        </div>
    </div>

    <x-form-input
        name="adresse"
        label="Adresse"
        :value="$pointFraicheur->adresse"
        placeholder="Rue, code postal, ville"
        required
    />

    <div class="row">
        <div class="col-md-6">
            <x-form-input
                name="latitude"
                type="text"
                label="Latitude"
                :value="$pointFraicheur->latitude"
                placeholder="Ex. 36.8064910"
                required
            />
        </div>
        <div class="col-md-6">
            <x-form-input
                name="longitude"
                type="text"
                label="Longitude"
                :value="$pointFraicheur->longitude"
                placeholder="Ex. 10.1815316"
                required
            />
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <x-form-input
                name="horaires"
                label="Horaires"
                :value="$pointFraicheur->horaires"
                placeholder="Ex. 08:00 - 20:00"
            />
        </div>
        <div class="col-md-4">
            <x-form-input
                name="capacite"
                type="number"
                label="Capacité (personnes)"
                :value="old('capacite', $pointFraicheur->capacite ?? 50)"
                placeholder="Ex. 50"
                required
            />
        </div>
        <div class="col-md-4">
            <div class="ve-form-group app-form-group">
                <label for="accessible">Accessibilité</label>
                <div class="form-check">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="accessible"
                        id="accessible"
                        value="1"
                        @checked(old('accessible', $pointFraicheur->accessible ?? true))
                    >
                    <label class="form-check-label" for="accessible">Accessible au public</label>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i> Enregistrer
        </button>
        <a href="{{ route('admin.points-fraicheur.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
