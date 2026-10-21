{{--
    Formulaire partagé (création / modification) d'un signalement.

    Variables attendues :
      $signalement       — modèle Signalement (neuf ou existant)
      $typesSignalement  — collection TypeSignalement pour la liste déroulante
      $statuts           — tableau des statuts disponibles
      $action            — URL de traitement du formulaire
      $method            — POST (création) ou PUT (modification)
--}}
<form method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="type_signalement_id" class="form-label">Type de signalement <span class="text-danger">*</span></label>
            <select
                class="form-select @error('type_signalement_id') is-invalid @enderror"
                id="type_signalement_id"
                name="type_signalement_id"
                required
            >
                <option value="">-- Sélectionner un type --</option>
                @foreach ($typesSignalement as $type)
                    <option
                        value="{{ $type->id }}"
                        @selected(old('type_signalement_id', $signalement->type_signalement_id) == $type->id)
                    >
                        {{ $type->libelle }}
                    </option>
                @endforeach
            </select>
            @error('type_signalement_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="statut" class="form-label">Statut <span class="text-danger">*</span></label>
            <select
                class="form-select @error('statut') is-invalid @enderror"
                id="statut"
                name="statut"
                required
            >
                @foreach ($statuts as $key => $label)
                    <option
                        value="{{ $key }}"
                        @selected(old('statut', $signalement->statut ?? 'nouveau') === $key)
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('statut')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
        <textarea
            class="form-control @error('description') is-invalid @enderror"
            id="description"
            name="description"
            rows="5"
            maxlength="1000"
            placeholder="Décrivez le signalement en détail..."
            required
        >{{ old('description', $signalement->description) }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Maximum 1000 caractères.</div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i> Enregistrer
        </button>
        <a href="{{ route('admin.signalements.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
