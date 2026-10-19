{{-- Formulaire partagé pour MesurePrevention --}}
@csrf

<div class="row g-3">
    {{-- Alerte météo liée --}}
    <div class="col-md-6">
        <label for="alerte_meteo_id" class="form-label">Alerte météo associée <span class="text-danger">*</span></label>
        <select
            class="form-select @error('alerte_meteo_id') is-invalid @enderror"
            id="alerte_meteo_id"
            name="alerte_meteo_id"
            required
        >
            <option value="">-- Sélectionner une alerte météo --</option>
            @foreach ($alertesMeteo as $alerte)
                @php
                    $couleur = $alerte->niveauAlerte->couleur ?? 'gris';
                    $niveauNum = $alerte->niveauAlerte->niveau ?? '-';
                @endphp
                <option
                    value="{{ $alerte->id }}"
                    data-couleur="{{ $couleur }}"
                    data-niveau="{{ $niveauNum }}"
                    {{ old('alerte_meteo_id', $mesurePrevention->alerte_meteo_id ?? '') == $alerte->id ? 'selected' : '' }}
                >
                    [{{ strtoupper($couleur) }} - N{{ $niveauNum }}] {{ $alerte->titre }}
                </option>
            @endforeach
        </select>
        @error('alerte_meteo_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        {{-- Badge dynamique d'indication du niveau de l'alerte --}}
        <div class="mt-2 d-flex align-items-center gap-2">
            <small class="text-muted">Niveau de vigilance lié :</small>
            <span id="badge-alerte-couleur" class="badge bg-secondary">Sélectionnez une alerte</span>
        </div>
    </div>

    {{-- Titre de la mesure --}}
    <div class="col-md-6">
        <label for="titre" class="form-label">Titre de la mesure de prévention <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control @error('titre') is-invalid @enderror"
            id="titre"
            name="titre"
            value="{{ old('titre', $mesurePrevention->titre ?? '') }}"
            placeholder="Ex: Distribution d'eau minérale aux points de fraîcheur"
            maxlength="150"
            required
        >
        @error('titre')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Type de mesure --}}
    <div class="col-md-4">
        <label for="type" class="form-label">Type de mesure <span class="text-danger">*</span></label>
        <select
            class="form-select @error('type') is-invalid @enderror"
            id="type"
            name="type"
            required
        >
            <option value="">-- Sélectionner un type --</option>
            @foreach ($types as $key => $label)
                <option value="{{ $key }}" {{ old('type', $mesurePrevention->type ?? '') === $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Statut de la mesure --}}
    <div class="col-md-4">
        <label for="statut" class="form-label">Statut <span class="text-danger">*</span></label>
        <select
            class="form-select @error('statut') is-invalid @enderror"
            id="statut"
            name="statut"
            required
        >
            <option value="">-- Sélectionner un statut --</option>
            @foreach ($statuts as $key => $label)
                <option value="{{ $key }}" {{ old('statut', $mesurePrevention->statut ?? '') === $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('statut')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Date et heure de début --}}
    <div class="col-md-4">
        <label for="date_debut" class="form-label">Date et heure de début <span class="text-danger">*</span></label>
        <input
            type="datetime-local"
            class="form-control @error('date_debut') is-invalid @enderror"
            id="date_debut"
            name="date_debut"
            value="{{ old('date_debut', isset($mesurePrevention->date_debut) ? $mesurePrevention->date_debut->format('Y-m-d\TH:i') : '') }}"
            required
        >
        @error('date_debut')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Responsable / Organisme chargé --}}
    <div class="col-md-12">
        <label for="responsable" class="form-label">Responsable / Entité en charge <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control @error('responsable') is-invalid @enderror"
            id="responsable"
            name="responsable"
            value="{{ old('responsable', $mesurePrevention->responsable ?? '') }}"
            placeholder="Ex: Protection Civile Tunisienne (ONPC), Croissant Rouge, Municipalité de Tunis..."
            maxlength="100"
            required
        >
        @error('responsable')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Description détaillée --}}
    <div class="col-md-12">
        <label for="description" class="form-label">Description détaillée de la mesure <span class="text-danger">*</span></label>
        <textarea
            class="form-control @error('description') is-invalid @enderror"
            id="description"
            name="description"
            rows="5"
            placeholder="Détails logistiques, zones prioritaires d'intervention, consignes pour les agents et le public..."
            required
        >{{ old('description', $mesurePrevention->description ?? '') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button type="submit" class="btn btn-primary">
        <i class="bx bx-save me-1"></i> {{ isset($isEdit) && $isEdit ? 'Mettre à jour' : 'Enregistrer' }}
    </button>
    <a href="{{ route('admin.mesures-prevention.index') }}" class="btn btn-outline-secondary">
        Annuler
    </a>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAlerte = document.getElementById('alerte_meteo_id');
        const badgeAlerte = document.getElementById('badge-alerte-couleur');

        function updateAlerteBadge() {
            const selected = selectAlerte.options[selectAlerte.selectedIndex];
            if (!selected || !selected.value) {
                badgeAlerte.className = 'badge bg-secondary';
                badgeAlerte.textContent = 'Sélectionnez une alerte';
                badgeAlerte.removeAttribute('style');
                return;
            }

            const couleur = selected.getAttribute('data-couleur');
            const niveau = selected.getAttribute('data-niveau');
            let bgClass = 'bg-secondary';
            let style = '';

            switch (couleur) {
                case 'vert':
                    bgClass = 'badge bg-success';
                    break;
                case 'jaune':
                    bgClass = 'badge bg-warning text-dark';
                    break;
                case 'orange':
                    bgClass = 'badge text-white';
                    style = 'background-color: #fd7e14 !important;';
                    break;
                case 'rouge':
                    bgClass = 'badge bg-danger';
                    break;
            }

            badgeAlerte.className = bgClass;
            badgeAlerte.setAttribute('style', style);
            badgeAlerte.textContent = `Niveau ${niveau} - ${couleur.toUpperCase()}`;
        }

        selectAlerte.addEventListener('change', updateAlerteBadge);
        updateAlerteBadge();
    });
</script>
@endpush
