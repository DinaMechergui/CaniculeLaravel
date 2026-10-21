<form method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST') @method($method) @endif
    <div class="row">
        <div class="col-md-6"><x-form-select name="quartier_id" label="Quartier" :value="$coupure->quartier_id" placeholder="Sélectionnez un quartier..." required>@foreach ($quartiers as $quartier)<option value="{{ $quartier->id }}" @selected((int) old('quartier_id', $coupure->quartier_id) === $quartier->id)>{{ $quartier->nom }} ({{ $quartier->ville }})</option>@endforeach</x-form-select></div>
        <div class="col-md-3"><x-form-select name="type" label="Type" :value="$coupure->type" placeholder="Sélectionnez..." required>@foreach (['delestage' => 'Délestage', 'panne' => 'Panne', 'surcharge' => 'Surcharge'] as $value => $label)<option value="{{ $value }}" @selected(old('type', $coupure->type) === $value)>{{ $label }}</option>@endforeach</x-form-select></div>
        <div class="col-md-3"><x-form-select name="statut" label="Statut" :value="$coupure->statut ?: 'prevue'" required>@foreach (['prevue' => 'Prévue', 'en_cours' => 'En cours', 'terminee' => 'Terminée'] as $value => $label)<option value="{{ $value }}" @selected(old('statut', $coupure->statut ?: 'prevue') === $value)>{{ $label }}</option>@endforeach</x-form-select></div>
    </div>
    <div class="row"><div class="col-md-6"><x-form-input name="date_debut" type="datetime-local" label="Date et heure de début" :value="$coupure->date_debut?->format('Y-m-d\\TH:i')" required /></div><div class="col-md-6"><x-form-input name="date_fin" type="datetime-local" label="Date et heure de fin" :value="$coupure->date_fin?->format('Y-m-d\\TH:i')" /></div></div>
    <div class="mb-3"><label class="form-label" for="description">Description</label><textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $coupure->description) }}</textarea>@error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    @include('back.partials.location-picker', [
        'latitude' => $coupure->latitude,
        'longitude' => $coupure->longitude,
        'label' => 'Position précise de la coupure (facultative)',
    ])
    <div class="d-flex gap-2 mt-3"><button class="btn btn-primary" type="submit"><i class="bx bx-save me-1"></i> Enregistrer</button><a href="{{ route('admin.coupures.index') }}" class="btn btn-outline-secondary">Annuler</a></div>
</form>