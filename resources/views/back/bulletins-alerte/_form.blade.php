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

{{-- Assistant Rédactionnel IA (Option 1) --}}
<div class="card border border-primary border-opacity-25 bg-primary bg-opacity-10 mb-4 shadow-sm" id="ai-bulletin-card">
    <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary text-white p-2 rounded-circle">
                    <i class="bx bx-bot fs-5"></i>
                </span>
                <div>
                    <h6 class="mb-0 fw-bold text-primary">Assistant Rédactionnel Météo IA</h6>
                    <small class="text-muted">Générez automatiquement le niveau de vigilance, l'évolution, le diagnostic et la conduite à tenir</small>
                </div>
            </div>
            <span class="badge bg-white text-primary border shadow-xs" id="ai-provider-badge" style="display:none;"></span>
        </div>

        <div class="row g-2 align-items-center mt-1">
            <div class="col-md-9">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bx bx-message-rounded-dots text-primary"></i></span>
                    <input type="text"
                           id="ai-keywords"
                           class="form-control bg-white"
                           placeholder="Mots-clés ou contexte (ex: Grand Tunis, 45°C, sirocco violent, risque incendie... ou laissez vide pour suggestion automatique)" />
                </div>
            </div>
            <div class="col-md-3 d-grid">
                <button type="button" class="btn btn-primary" id="btn-ai-generate-bulletin">
                    <span id="btn-ai-spinner" class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                    <i class="bx bx-sparkles me-1" id="btn-ai-icon"></i>
                    <span id="btn-ai-text">Rédiger avec l'IA</span>
                </button>
            </div>
        </div>
        <div id="ai-feedback" class="small mt-2" style="display:none;"></div>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnAi = document.getElementById('btn-ai-generate-bulletin');
        const spinner = document.getElementById('btn-ai-spinner');
        const icon = document.getElementById('btn-ai-icon');
        const text = document.getElementById('btn-ai-text');
        const feedback = document.getElementById('ai-feedback');
        const badge = document.getElementById('ai-provider-badge');

        if (!btnAi) return;

        btnAi.addEventListener('click', async function () {
            const alerteSelect = document.getElementById('alerte_id');
            const alerteId = alerteSelect ? alerteSelect.value : '';
            const keywords = document.getElementById('ai-keywords').value;

            if (!alerteId) {
                alert('Veuillez d\'abord sélectionner une alerte concernée ci-dessous.');
                alerteSelect.focus();
                return;
            }

            // UI Loading
            btnAi.disabled = true;
            spinner.classList.remove('d-none');
            icon.classList.add('d-none');
            text.textContent = 'Génération IA...';
            feedback.style.display = 'none';

            try {
                const response = await fetch("{{ route('admin.bulletins-alerte.ia.generer') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        alerte_id: alerteId,
                        keywords: keywords
                    })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Erreur lors de la génération IA.');
                }

                // Pré-remplissage des champs
                const niveauSelect = document.getElementById('niveau');
                const evolutionSelect = document.getElementById('evolution');
                const tempInput = document.getElementById('temperature_max');
                const messageTextarea = document.getElementById('message');
                const conduiteTextarea = document.getElementById('conduite_a_tenir');

                if (niveauSelect && data.niveau) niveauSelect.value = data.niveau;
                if (evolutionSelect && data.evolution) evolutionSelect.value = data.evolution;
                if (tempInput && data.temperature_max) tempInput.value = data.temperature_max;
                if (messageTextarea && data.message) messageTextarea.value = data.message;
                if (conduiteTextarea && data.conduite_a_tenir) conduiteTextarea.value = data.conduite_a_tenir;

                // Affichage du provider
                if (badge && data.provider) {
                    badge.textContent = '✨ ' + data.provider;
                    badge.style.display = 'inline-block';
                }

                // Message de succès
                feedback.className = 'small mt-2 alert alert-success py-1 px-2 d-flex align-items-center';
                feedback.innerHTML = '<i class="bx bx-check-circle me-1"></i> Bulletin généré avec succès par l\'IA ! Vous pouvez ajuster les champs avant de publier.';
                feedback.style.display = 'flex';

                // Effet visuel sur les champs remplis
                [niveauSelect, evolutionSelect, messageTextarea, conduiteTextarea].forEach(el => {
                    if (el) {
                        el.style.transition = 'background-color 0.4s';
                        el.style.backgroundColor = '#e8f4fd';
                        setTimeout(() => el.style.backgroundColor = '', 1500);
                    }
                });

            } catch (err) {
                feedback.className = 'small mt-2 alert alert-danger py-1 px-2 d-flex align-items-center';
                feedback.innerHTML = '<i class="bx bx-error me-1"></i> ' + err.message;
                feedback.style.display = 'flex';
            } finally {
                btnAi.disabled = false;
                spinner.classList.add('d-none');
                icon.classList.remove('d-none');
                text.textContent = 'Rédiger avec l\'IA';
            }
        });
    });
</script>
