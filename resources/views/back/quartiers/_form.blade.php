<form method="POST" action="{{ $action }}" novalidate>
    @csrf
    @if (strtoupper($method) !== 'POST') @method($method) @endif
    <x-form-input name="nom" label="Nom" :value="$quartier->nom" placeholder="Ex. El Menzah" required />
    <div class="row">
        <div class="col-md-6"><x-form-input name="ville" label="Ville" :value="$quartier->ville" required /></div>
        <div class="col-md-6"><x-form-input name="code_postal" label="Code postal" :value="$quartier->code_postal" required /></div>
    </div>
    @include('back.partials.location-picker', [
        'latitude' => $quartier->latitude,
        'longitude' => $quartier->longitude,
        'label' => 'Centre géographique du quartier (facultatif)',
    ])
    <div class="d-flex gap-2 mt-3"><button class="btn btn-primary" type="submit"><i class="bx bx-save me-1"></i> Enregistrer</button><a href="{{ route('admin.quartiers.index') }}" class="btn btn-outline-secondary">Annuler</a></div>
</form>