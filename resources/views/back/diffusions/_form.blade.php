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
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="contenu" class="form-label fw-semibold mb-0">
                Message court transmis à la population <span class="text-danger">*</span>
            </label>
            <span class="badge bg-label-secondary" id="char-counter">0 / 160 caractères</span>
        </div>
        <textarea name="contenu"
                  id="contenu"
                  rows="3"
                  maxlength="160"
                  class="form-control @error('contenu') is-invalid @enderror"
                  placeholder="Ex : [ALERTE INM] Vigilance Rouge canicule à Kairouan. Évitez les sorties de 11h à 17h. Hydratez-vous. Urgences : 198."
                  required>{{ old('contenu', $diffusion->contenu ?? '') }}</textarea>
        <div class="form-text">Format adapté pour les SMS, bandeaux d'affichage et notifications push (160 caractères max).</div>
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
    });
</script>
