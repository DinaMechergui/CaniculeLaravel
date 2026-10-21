@php
    $isEdit = isset($bulletinAlerte);
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

<div class="alert alert-primary d-flex align-items-center mb-4" role="alert">
    <i class="bx bx-info-circle fs-4 me-2"></i>
    <div class="small">
        <strong>Règles métier :</strong>
        <ul class="mb-0 ps-3 mt-1">
            <li>Lorsqu'un bulletin est enregistré avec l'évolution <strong>« Levée de l'alerte »</strong>, l'alerte parente est automatiquement marquée comme <strong>terminée</strong> et sa date de fin est mise à jour.</li>
            <li>En cas d'<strong>aggravation</strong>, le niveau de vigilance sélectionné doit être supérieur ou égal à celui du bulletin précédent.</li>
        </ul>
    </div>
</div>

<div class="row g-3">
    {{-- Alerte parente --}}
    <div class="col-md-6">
        <label for="alerte_id" class="form-label fw-semibold">Alerte concernée <span class="text-danger">*</span></label>
        <select name="alerte_id" id="alerte_id" class="form-select @error('alerte_id') is-invalid @enderror" required>
            <option value="">Sélectionner une alerte...</option>
            @foreach ($alertes as $alt)
                <option value="{{ $alt->id }}" @selected(old('alerte_id', $bulletinAlerte->alerte_id ?? $selectedAlerteId ?? '') == $alt->id)>
                    {{ $alt->titre }} ({{ $alt->zone_concernee }}) — {{ $alt->statut === 'en_cours' ? '🟡 En cours' : '⚪ Terminée' }}
                </option>
            @endforeach
        </select>
        @error('alerte_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Niveau de vigilance --}}
    <div class="col-md-3">
        <label for="niveau" class="form-label fw-semibold">Niveau de vigilance <span class="text-danger">*</span></label>
        <select name="niveau" id="niveau" class="form-select @error('niveau') is-invalid @enderror" required>
            <option value="">Choisir un niveau...</option>
            <option value="vert" @selected(old('niveau', $bulletinAlerte->niveau ?? '') === 'vert') class="text-success fw-bold">🟢 Vert (Normal)</option>
            <option value="jaune" @selected(old('niveau', $bulletinAlerte->niveau ?? '') === 'jaune') class="text-info fw-bold">🟡 Jaune (Soyez attentif)</option>
            <option value="orange" @selected(old('niveau', $bulletinAlerte->niveau ?? '') === 'orange') class="text-warning fw-bold">🟠 Orange (Soyez très vigilant)</option>
            <option value="rouge" @selected(old('niveau', $bulletinAlerte->niveau ?? '') === 'rouge') class="text-danger fw-bold">🔴 Rouge (Vigilance absolue)</option>
        </select>
        @error('niveau')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Évolution de la situation --}}
    <div class="col-md-3">
        <label for="evolution" class="form-label fw-semibold">Évolution de la situation <span class="text-danger">*</span></label>
        <select name="evolution" id="evolution" class="form-select @error('evolution') is-invalid @enderror" required>
            <option value="">Sélectionner l'évolution...</option>
            <option value="aggravation" @selected(old('evolution', $bulletinAlerte->evolution ?? '') === 'aggravation')>↗ Aggravation</option>
            <option value="stable" @selected(old('evolution', $bulletinAlerte->evolution ?? '') === 'stable')>→ Stable</option>
            <option value="amelioration" @selected(old('evolution', $bulletinAlerte->evolution ?? '') === 'amelioration')>↘ Amélioration</option>
            <option value="levee" @selected(old('evolution', $bulletinAlerte->evolution ?? '') === 'levee')>✓ Levée de l'alerte</option>
        </select>
        @error('evolution')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Date et heure de publication --}}
    <div class="col-md-6">
        <label for="date_publication" class="form-label fw-semibold">Date et heure de publication officielle <span class="text-danger">*</span></label>
        <input type="datetime-local"
               name="date_publication"
               id="date_publication"
               class="form-control @error('date_publication') is-invalid @enderror"
               value="{{ old('date_publication', isset($bulletinAlerte->date_publication) ? $bulletinAlerte->date_publication->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
               max="{{ now()->format('Y-m-d\TH:i') }}"
               required>
        <div class="form-text">Doit être antérieure ou égale à l'heure actuelle.</div>
        @error('date_publication')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Température maximale (optionnelle) --}}
    <div class="col-md-6">
        <label for="temperature_max" class="form-label fw-semibold">Température maximale prévue (°C)</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bx bxs-thermometer"></i></span>
            <input type="number"
                   name="temperature_max"
                   id="temperature_max"
                   class="form-control @error('temperature_max') is-invalid @enderror"
                   value="{{ old('temperature_max', $bulletinAlerte->temperature_max ?? '') }}"
                   min="0"
                   max="60"
                   placeholder="Ex : 44">
            <span class="input-group-text">°C</span>
        </div>
        <div class="form-text">Pertinent notamment pour les épisodes caniculaires ou orageux (0 à 60°C).</div>
        @error('temperature_max')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    {{-- Message officiel --}}
    <div class="col-12">
        <label for="message" class="form-label fw-semibold">Message officiel du bulletin <span class="text-danger">*</span></label>
        <textarea name="message"
                  id="message"
                  rows="4"
                  class="form-control @error('message') is-invalid @enderror"
                  placeholder="Diagnostic météo, constats sur le terrain, prévisions et chronologie attendue..."
                  required>{{ old('message', $bulletinAlerte->message ?? '') }}</textarea>
        @error('message')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Conduite à tenir --}}
    <div class="col-12">
        <label for="conduite_a_tenir" class="form-label fw-semibold">Conduite à tenir & Conseils immédiats à la population</label>
        <textarea name="conduite_a_tenir"
                  id="conduite_a_tenir"
                  rows="3"
                  class="form-control @error('conduite_a_tenir') is-invalid @enderror"
                  placeholder="Mesures de prudence, hydratation, évitement des déplacements, numéros d'urgence utiles...">{{ old('conduite_a_tenir', $bulletinAlerte->conduite_a_tenir ?? '') }}</textarea>
        @error('conduite_a_tenir')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mt-4 d-flex justify-content-end gap-2">
    <a href="{{ route('admin.bulletins-alerte.index') }}" class="btn btn-outline-secondary">
        <i class="bx bx-arrow-back me-1"></i> Annuler
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="bx bx-save me-1"></i> {{ $isEdit ? 'Mettre à jour le bulletin' : 'Publier le bulletin' }}
    </button>
</div>
