<form method="POST" action="{{ $action }}" novalidate>
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label" for="coupure_id">Coupure <span class="text-danger">*</span></label>
            <select class="form-select @error('coupure_id') is-invalid @enderror" id="coupure_id" name="coupure_id" required>
                <option value="">Sélectionnez une coupure</option>
                @foreach ($coupures as $coupure)
                    <option value="{{ $coupure->id }}" @selected((string) old('coupure_id', $intervention->coupure_id) === (string) $coupure->id)>
                        #{{ $coupure->id }} — {{ $coupure->quartier?->nom }} ({{ $coupure->date_debut->format('d/m/Y H:i') }})
                    </option>
                @endforeach
            </select>
            @error('coupure_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6"><x-form-input name="equipe" label="Équipe" :value="$intervention->equipe" required /></div>
        <div class="col-md-6"><x-form-input name="technicien" label="Technicien" :value="$intervention->technicien" required /></div>
        <div class="col-md-6"><x-form-input name="date_intervention" type="datetime-local" label="Date de l’intervention" :value="$intervention->date_intervention?->format('Y-m-d\\TH:i')" required /></div>
        <div class="col-md-3"><x-form-input name="duree_estimee" type="number" label="Durée estimée (minutes)" :value="$intervention->duree_estimee" required min="1" /></div>
        <div class="col-md-3">
            <label class="form-label" for="statut">Statut <span class="text-danger">*</span></label>
            <select class="form-select @error('statut') is-invalid @enderror" id="statut" name="statut" required>
                @foreach (['prevue' => 'Prévue', 'en_cours' => 'En cours', 'terminee' => 'Terminée'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('statut', $intervention->statut) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('statut')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
            <label class="form-label" for="rapport">Rapport technique <span class="text-muted">(obligatoire si terminée)</span></label>
            <textarea class="form-control @error('rapport') is-invalid @enderror" id="rapport" name="rapport" rows="4">{{ old('rapport', $intervention->rapport) }}</textarea>
            @error('rapport')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="d-flex gap-2 mt-4">
        <button class="btn btn-primary" type="submit"><i class="bx bx-save me-1"></i> Enregistrer</button>
        <a href="{{ route('admin.interventions.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
