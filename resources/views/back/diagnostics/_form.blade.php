{{--
    Formulaire partagé (création / modification) d'un diagnostic.

    Variables attendues :
      $diagnostic   — modèle Diagnostic (neuf ou existant)
      $signalements — signalements sans diagnostic (+ celui du diagnostic modifié)
      $action       — URL de traitement du formulaire
      $method       — POST (création) ou PUT (modification)
--}}
<form method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    {{-- Signalement associé --}}
    <div class="mb-3">
        <label for="signalement_id" class="form-label fw-semibold">
            Signalement <span class="text-danger">*</span>
        </label>
        <select
            name="signalement_id"
            id="signalement_id"
            class="form-select @error('signalement_id') is-invalid @enderror"
            required
        >
            <option value="">— Sélectionnez un signalement —</option>
            @foreach ($signalements as $signalement)
                <option
                    value="{{ $signalement->id }}"
                    @selected((int) old('signalement_id', $diagnostic->signalement_id) === $signalement->id)
                >
                    #{{ $signalement->id }}
                    — {{ $signalement->typeSignalement?->libelle ?? 'Signalement' }}
                    ({{ Str::limit($signalement->description, 50) }})
                </option>
            @endforeach
        </select>
        @error('signalement_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">Seuls les signalements sans diagnostic sont proposés.</small>
    </div>

    <h6 class="mt-4 mb-3 text-uppercase text-muted">Symptômes</h6>

    <div class="row">
        {{-- Zone touchée --}}
        <div class="col-md-4 mb-3">
            <label for="zone_touchee" class="form-label fw-semibold">
                Zone touchée <span class="text-danger">*</span>
            </label>
            <select
                name="zone_touchee"
                id="zone_touchee"
                class="form-select @error('zone_touchee') is-invalid @enderror"
                required
            >
                <option value="">— Sélectionnez —</option>
                @foreach (\App\Models\Diagnostic::ZONES as $key => $label)
                    <option value="{{ $key }}" @selected(old('zone_touchee', $diagnostic->zone_touchee) === $key)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('zone_touchee')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Heure de la panne --}}
        <div class="col-md-4 mb-3">
            <label for="heure_panne" class="form-label fw-semibold">
                Heure de la panne <span class="text-danger">*</span>
            </label>
            <input
                type="time"
                name="heure_panne"
                id="heure_panne"
                class="form-control @error('heure_panne') is-invalid @enderror"
                value="{{ old('heure_panne', $diagnostic->heure_panne) }}"
                required
            >
            @error('heure_panne')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="text-muted">Format 24 h — ex : 14:35</small>
        </div>

        {{-- Température --}}
        <div class="col-md-4 mb-3">
            <label for="temperature" class="form-label fw-semibold">Température extérieure (°C)</label>
            <input
                type="number"
                name="temperature"
                id="temperature"
                step="0.1"
                class="form-control @error('temperature') is-invalid @enderror"
                value="{{ old('temperature', $diagnostic->temperature) }}"
                placeholder="Ex : 43.5"
            >
            @error('temperature')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Symptômes oui / non --}}
    <div class="row mb-3">
        @foreach (\App\Models\Diagnostic::SYMPTOMES as $champ => $label)
            <div class="col-md-6">
                <div class="form-check mb-2">
                    <input
                        type="checkbox"
                        class="form-check-input"
                        name="{{ $champ }}"
                        id="{{ $champ }}"
                        value="1"
                        @checked(old($champ, $diagnostic->$champ))
                    >
                    <label class="form-check-label" for="{{ $champ }}">{{ $label }}</label>
                </div>
            </div>
        @endforeach
    </div>

    <h6 class="mt-4 mb-3 text-uppercase text-muted">Confirmation de l'administrateur</h6>

    {{-- Cause réelle --}}
    <div class="mb-3">
        <label for="cause_reelle" class="form-label fw-semibold">Cause réelle (après intervention)</label>
        <select
            name="cause_reelle"
            id="cause_reelle"
            class="form-select @error('cause_reelle') is-invalid @enderror"
        >
            <option value="">— Pas encore confirmée —</option>
            @foreach (\App\Models\Diagnostic::CAUSES as $key => $label)
                <option value="{{ $key }}" @selected(old('cause_reelle', $diagnostic->cause_reelle) === $key)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('cause_reelle')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="text-muted">Sert à vérifier et ré-entraîner le modèle IA.</small>
    </div>

    {{-- Remarque --}}
    <div class="mb-3">
        <label for="remarque" class="form-label fw-semibold">Remarque</label>
        <textarea
            name="remarque"
            id="remarque"
            rows="3"
            maxlength="1000"
            class="form-control @error('remarque') is-invalid @enderror"
            placeholder="Observations du technicien…"
        >{{ old('remarque', $diagnostic->remarque) }}</textarea>
        @error('remarque')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i> Enregistrer
        </button>
        <a href="{{ route('admin.diagnostics.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
