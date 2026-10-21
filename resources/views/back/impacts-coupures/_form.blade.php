<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label" for="coupure_id">Coupure <span class="text-danger">*</span></label>
            <select class="form-select @error('coupure_id') is-invalid @enderror" id="coupure_id" name="coupure_id" required>
                <option value="">Sélectionnez une coupure</option>
                @foreach ($coupures as $coupure)
                    <option value="{{ $coupure->id }}" @selected((string) old('coupure_id', $impact->coupure_id) === (string) $coupure->id)>
                        #{{ $coupure->id }} — {{ $coupure->quartier?->nom }} ({{ $coupure->date_debut->format('d/m/Y H:i') }})
                    </option>
                @endforeach
            </select>
            @error('coupure_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6"><x-form-input name="nom_structure" label="Nom de la structure" :value="$impact->nom_structure" required /></div>
        <div class="col-md-6">
            <label class="form-label" for="categorie">Catégorie <span class="text-danger">*</span></label>
            <select class="form-select @error('categorie') is-invalid @enderror" id="categorie" name="categorie" required>
                @foreach (['hopital' => 'Hôpital', 'ecole' => 'École', 'commerce' => 'Commerce', 'residence' => 'Résidence', 'autre' => 'Autre'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('categorie', $impact->categorie) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('categorie')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12"><x-form-input name="adresse" label="Adresse" :value="$impact->adresse" required /></div>
        <div class="col-12">
            @include('back.partials.location-picker', [
                'latitude' => $impact->latitude,
                'longitude' => $impact->longitude,
                'label' => 'Position de la structure affectée (facultative)',
            ])
        </div>
        <div class="col-md-4"><x-form-input name="personnes_affectees" type="number" label="Personnes affectées" :value="$impact->personnes_affectees" required min="0" /></div>
        <div class="col-md-4">
            <label class="form-label" for="niveau_gravite">Niveau de gravité <span class="text-danger">*</span></label>
            <select class="form-select @error('niveau_gravite') is-invalid @enderror" id="niveau_gravite" name="niveau_gravite" required>
                @foreach (['moyen' => 'Moyen', 'eleve' => 'Élevé', 'critique' => 'Critique'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('niveau_gravite', $impact->niveau_gravite) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('niveau_gravite')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4"><x-form-input name="date_signalement" type="datetime-local" label="Date du signalement" :value="$impact->date_signalement?->format('Y-m-d\\TH:i')" required /></div>
        <div class="col-md-6">
            <label class="form-label" for="statut">Statut <span class="text-danger">*</span></label>
            <select class="form-select @error('statut') is-invalid @enderror" id="statut" name="statut" required>
                @foreach (['en_traitement' => 'En traitement', 'resolu' => 'Résolu'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('statut', $impact->statut ?: 'en_traitement') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('statut')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label class="form-label" for="mesures_urgence">Mesures d’urgence</label>
            <textarea class="form-control @error('mesures_urgence') is-invalid @enderror" id="mesures_urgence" name="mesures_urgence" rows="3">{{ old('mesures_urgence', $impact->mesures_urgence) }}</textarea>
            @error('mesures_urgence')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label class="form-label" for="description">Description interne</label>
            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $impact->description) }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="d-flex gap-2 mt-4">
        <button class="btn btn-primary" type="submit"><i class="bx bx-save me-1"></i> Enregistrer</button>
        <a href="{{ route('admin.impacts-coupures.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
