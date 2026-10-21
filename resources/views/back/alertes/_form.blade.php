@php
    $isEdit = isset($alerte);
@endphp

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible mb-4" role="alert">
        <h6 class="alert-heading d-flex align-items-center mb-1">
            <i class="bx bx-error-circle me-2"></i>Veuillez corriger les erreurs ci-dessous :
        </h6>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
@endif

<div class="row g-3">
    {{-- Titre --}}
    <div class="col-md-8">
        <label for="titre" class="form-label fw-semibold">Titre de l'alerte <span class="text-danger">*</span></label>
        <input type="text"
               name="titre"
               id="titre"
               class="form-control @error('titre') is-invalid @enderror"
               value="{{ old('titre', $alerte->titre ?? '') }}"
               maxlength="150"
               placeholder="Ex : Vague de canicule extrême et sirocco violent"
               required>
        <div class="form-text">Titre court et clair décrivant l'événement (max 150 caractères).</div>
        @error('titre')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Type d'aléa --}}
    <div class="col-md-4">
        <label for="type" class="form-label fw-semibold">Type d'aléa / risque <span class="text-danger">*</span></label>
        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
            <option value="">Sélectionner un type...</option>
            @foreach (\App\Models\Alerte::TYPES as $key => $label)
                <option value="{{ $key }}" @selected(old('type', $alerte->type ?? '') === $key)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Zone concernée --}}
    <div class="col-md-6">
        <label for="zone_concernee" class="form-label fw-semibold">Zone géographique concernée <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="bx bx-map-pin"></i></span>
            <input type="text"
                   name="zone_concernee"
                   id="zone_concernee"
                   class="form-control @error('zone_concernee') is-invalid @enderror"
                   value="{{ old('zone_concernee', $alerte->zone_concernee ?? '') }}"
                   maxlength="150"
                   placeholder="Ex : Grand Tunis, Gouvernorat de Kairouan, Sahel"
                   required>
        </div>
        @error('zone_concernee')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    {{-- Source / Organisme --}}
    <div class="col-md-6">
        <label for="source" class="form-label fw-semibold">Organisme émetteur (source)</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bx bx-buildings"></i></span>
            <input type="text"
                   name="source"
                   id="source"
                   class="form-control @error('source') is-invalid @enderror"
                   value="{{ old('source', $alerte->source ?? 'Institut National de la Météorologie (INM)') }}"
                   maxlength="100"
                   placeholder="Ex : INM, Protection Civile, Ministère de la Santé">
        </div>
        @error('source')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    {{-- Date de début --}}
    <div class="col-md-4">
        <label for="date_debut" class="form-label fw-semibold">Date et heure de début <span class="text-danger">*</span></label>
        <input type="datetime-local"
               name="date_debut"
               id="date_debut"
               class="form-control @error('date_debut') is-invalid @enderror"
               value="{{ old('date_debut', isset($alerte->date_debut) ? $alerte->date_debut->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
               required>
        @error('date_debut')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Date de fin --}}
    <div class="col-md-4">
        <label for="date_fin" class="form-label fw-semibold">Date et heure de fin (optionnelle)</label>
        <input type="datetime-local"
               name="date_fin"
               id="date_fin"
               class="form-control @error('date_fin') is-invalid @enderror"
               value="{{ old('date_fin', isset($alerte->date_fin) ? $alerte->date_fin->format('Y-m-d\TH:i') : '') }}">
        <div class="form-text">Remplie automatiquement lors de la levée de l'alerte.</div>
        @error('date_fin')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Statut --}}
    <div class="col-md-4">
        <label for="statut" class="form-label fw-semibold">Statut actuel <span class="text-danger">*</span></label>
        <select name="statut" id="statut" class="form-select @error('statut') is-invalid @enderror" required>
            <option value="en_cours" @selected(old('statut', $alerte->statut ?? 'en_cours') === 'en_cours')>
                🟡 En cours (Active)
            </option>
            <option value="terminee" @selected(old('statut', $alerte->statut ?? '') === 'terminee')>
                ⚪ Terminée (Levée / Archivée)
            </option>
        </select>
        @error('statut')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Description --}}
    <div class="col-12">
        <label for="description" class="form-label fw-semibold">Description globale de l'événement à risque <span class="text-danger">*</span></label>
        <textarea name="description"
                  id="description"
                  rows="4"
                  class="form-control @error('description') is-invalid @enderror"
                  placeholder="Contexte météorologique, causes du risque, portée attendue..."
                  required>{{ old('description', $alerte->description ?? '') }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mt-4 d-flex justify-content-end gap-2">
    <a href="{{ route('admin.alertes.index') }}" class="btn btn-outline-secondary">
        <i class="bx bx-arrow-back me-1"></i> Annuler
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="bx bx-save me-1"></i> {{ $isEdit ? 'Mettre à jour l\'alerte' : 'Enregistrer l\'alerte' }}
    </button>
</div>
