@php
    $latitudeValue = old('latitude', $latitude ?? null);
    $longitudeValue = old('longitude', $longitude ?? null);
@endphp
@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
@endpush
<div class="mb-3 smartgrid-location-picker" data-location-picker>
    <h6 class="mb-2">{{ $label }}</h6>
    <p class="small text-muted">Aucune position n’est déduite automatiquement. Placez le marqueur uniquement si les coordonnées exactes sont connues.</p>
    <div id="location-picker-map" class="rounded border mb-3" style="height:280px" aria-label="Carte pour choisir une position"></div>
    <div class="row">
        <div class="col-md-6">
            <label class="form-label" for="latitude">Latitude</label>
            <input class="form-control @error('latitude') is-invalid @enderror" type="number" name="latitude" id="latitude" min="-90" max="90" step="0.0000001" value="{{ $latitudeValue }}">
            @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label" for="longitude">Longitude</label>
            <input class="form-control @error('longitude') is-invalid @enderror" type="number" name="longitude" id="longitude" min="-180" max="180" step="0.0000001" value="{{ $longitudeValue }}">
            @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <button class="btn btn-sm btn-outline-secondary" type="button" data-clear-location>Effacer la position</button>
</div>
@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="{{ asset('assets/js/location-picker.js') }}" defer></script>
@endpush
