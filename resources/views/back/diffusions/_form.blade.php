@php
    $isEdit = isset($diffusion);
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
    {{-- Bulletin d'alerte lié --}}
    <div class="col-md-6">
        <label for="bulletin_alerte_id" class="form-label fw-semibold">Bulletin d'alerte à diffuser <span class="text-danger">*</span></label>
        <select name="bulletin_alerte_id" id="bulletin_alerte_id" class="form-select @error('bulletin_alerte_id') is-invalid @enderror" required>
            <option value="">Sélectionner un bulletin officiel...</option>
            @foreach ($bulletins as $b)
                <option value="{{ $b->id }}" @selected(old('bulletin_alerte_id', $diffusion->bulletin_alerte_id ?? $selectedBulletinId ?? '') == $b->id)>
                    [{{ strtoupper($b->niveau) }}] {{ $b->alerte->titre }} — Bulletin du {{ $b->date_publication->format('d/m/Y H:i') }} ({{ $b->evolution_label }})
                </option>
            @endforeach
        </select>
        @error('bulletin_alerte_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Canal de diffusion --}}
    <div class="col-md-3">
        <label for="canal" class="form-label fw-semibold">Canal de transmission <span class="text-danger">*</span></label>
        <select name="canal" id="canal" class="form-select @error('canal') is-invalid @enderror" required>
            <option value="">Sélectionner un canal...</option>
            @foreach (\App\Models\Diffusion::CANAUX as $key => $label)
                <option value="{{ $key }}" @selected(old('canal', $diffusion->canal ?? '') === $key)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('canal')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Statut d'envoi --}}
    <div class="col-md-3">
        <label for="statut" class="form-label fw-semibold">Statut de la diffusion <span class="text-danger">*</span></label>
        <select name="statut" id="statut" class="form-select @error('statut') is-invalid @enderror" required>
            @foreach (\App\Models\Diffusion::STATUTS as $key => $label)
                <option value="{{ $key }}" @selected(old('statut', $diffusion->statut ?? 'planifiee') === $key)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('statut')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Destinataires estimés --}}
    <div class="col-md-6">
        <label for="destinataires_estimes" class="form-label fw-semibold">Nombre de destinataires estimés <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text"><i class="bx bx-group"></i></span>
            <input type="number"
                   name="destinataires_estimes"
                   id="destinataires_estimes"
                   class="form-control @error('destinataires_estimes') is-invalid @enderror"
                   value="{{ old('destinataires_estimes', $diffusion->destinataires_estimes ?? 5000) }}"
                   min="0"
                   placeholder="Ex : 25000"
                   required>
            <span class="input-group-text">personnes</span>
        </div>
        <div class="form-text">Nombre d'habitants ou d'abonnés ciblés par cette diffusion.</div>
        @error('destinataires_estimes')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    {{-- Date d'envoi --}}
    <div class="col-md-6">
        <label for="date_envoi" class="form-label fw-semibold">Date et heure d'envoi / programmation <span class="text-danger">*</span></label>
        <input type="datetime-local"
               name="date_envoi"
               id="date_envoi"
               class="form-control @error('date_envoi') is-invalid @enderror"
               value="{{ old('date_envoi', isset($diffusion->date_envoi) ? $diffusion->date_envoi->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
               required>
        @error('date_envoi')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Contenu du message court --}}
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-2">
            <label for="contenu" class="form-label fw-semibold mb-0">
                Message court transmis à la population <span class="text-danger">*</span>
            </label>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 d-flex align-items-center" id="btn-ai-adapt-diffusion">
                    <span id="btn-diff-spinner" class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                    <i class="bx bx-bot me-1" id="btn-diff-icon"></i>
                    <span id="btn-diff-text">⚡ Adapter au canal via l'IA</span>
                </button>
                <span class="badge bg-label-secondary" id="char-counter">0 / 160 caractères</span>
            </div>
        </div>
        <textarea name="contenu"
                  id="contenu"
                  rows="3"
                  maxlength="160"
                  class="form-control @error('contenu') is-invalid @enderror"
                  placeholder="Ex : [ALERTE INM] Vigilance Rouge canicule à Kairouan. Évitez les sorties de 11h à 17h. Hydratez-vous. Urgences : 198."
                  required>{{ old('contenu', $diffusion->contenu ?? '') }}</textarea>
        <div class="d-flex justify-content-between align-items-center mt-1">
            <div class="form-text">Format adapté pour les SMS, bandeaux d'affichage et notifications push (160 caractères max).</div>
            <div id="ai-diff-feedback" class="small fw-semibold text-primary" style="display:none;"></div>
        </div>
        @error('contenu')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="mt-4 d-flex justify-content-end gap-2">
    <a href="{{ route('admin.diffusions.index') }}" class="btn btn-outline-secondary">
        <i class="bx bx-arrow-back me-1"></i> Annuler
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="bx bx-save me-1"></i> {{ $isEdit ? 'Mettre à jour la diffusion' : 'Enregistrer la diffusion' }}
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const textarea = document.getElementById('contenu');
        const counter = document.getElementById('char-counter');
        const btnAi = document.getElementById('btn-ai-adapt-diffusion');
        const spinner = document.getElementById('btn-diff-spinner');
        const icon = document.getElementById('btn-diff-icon');
        const text = document.getElementById('btn-diff-text');
        const feedback = document.getElementById('ai-diff-feedback');

        function updateCount() {
            const length = textarea.value.length;
            counter.textContent = length + ' / 160 caractères';
            if (length > 140) {
                counter.className = 'badge bg-label-warning';
            } else if (length === 160) {
                counter.className = 'badge bg-label-danger';
            } else {
                counter.className = 'badge bg-label-secondary';
            }
        }

        if (textarea && counter) {
            textarea.addEventListener('input', updateCount);
            updateCount();
        }

        if (btnAi) {
            btnAi.addEventListener('click', async function () {
                const bulletinSelect = document.getElementById('bulletin_alerte_id');
                const canalSelect = document.getElementById('canal');
                const bulletinId = bulletinSelect ? bulletinSelect.value : '';
                const canal = canalSelect ? canalSelect.value : '';

                if (!bulletinId) {
                    alert('Veuillez d\'abord sélectionner le bulletin d\'alerte à diffuser ci-dessus.');
                    bulletinSelect.focus();
                    return;
                }

                if (!canal) {
                    alert('Veuillez sélectionner un canal de transmission ci-dessus.');
                    canalSelect.focus();
                    return;
                }

                btnAi.disabled = true;
                spinner.classList.remove('d-none');
                icon.classList.add('d-none');
                text.textContent = 'Adaptation IA...';
                feedback.style.display = 'none';

                try {
                    const response = await fetch("{{ route('admin.diffusions.ia.adapter') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            bulletin_alerte_id: bulletinId,
                            canal: canal
                        })
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Erreur lors de l\'adaptation IA.');
                    }

                    if (textarea && data.contenu) {
                        textarea.value = data.contenu;
                        updateCount();
                        textarea.style.transition = 'background-color 0.4s';
                        textarea.style.backgroundColor = '#e8f4fd';
                        setTimeout(() => textarea.style.backgroundColor = '', 1500);
                    }

                    const destinatairesInput = document.getElementById('destinataires_estimes');
                    if (destinatairesInput && data.destinataires_estimes) {
                        destinatairesInput.value = data.destinataires_estimes;
                    }

                    feedback.innerHTML = '✨ Adapté pour ' + (data.canal_label || canal) + ' (' + (data.provider || 'IA') + ')';
                    feedback.style.display = 'block';

                } catch (err) {
                    alert('Erreur : ' + err.message);
                } finally {
                    btnAi.disabled = false;
                    spinner.classList.add('d-none');
                    icon.classList.remove('d-none');
                    text.textContent = '⚡ Adapter au canal via l\'IA';
                }
            });
        }
    });
</script>
