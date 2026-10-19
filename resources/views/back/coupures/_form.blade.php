<form method="POST" action="{{ $action }}" id="form-coupure">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <x-form-select name="quartier_id" label="Quartier" :value="$coupure->quartier_id" placeholder="Sélectionnez un quartier..." required>
                @foreach ($quartiers as $quartier)
                    <option value="{{ $quartier->id }}" @selected((int) old('quartier_id', $coupure->quartier_id) === $quartier->id)>
                        {{ $quartier->nom }} ({{ $quartier->ville }})
                    </option>
                @endforeach
            </x-form-select>
        </div>

        <div class="col-md-3">
            <x-form-select name="type" label="Type de coupure" :value="$coupure->type" placeholder="Sélectionnez..." required>
                @foreach (['delestage' => 'Délestage', 'panne' => 'Panne', 'surcharge' => 'Surcharge'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('type', $coupure->type) === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </x-form-select>
        </div>

        <div class="col-md-3">
            <x-form-select name="statut" label="Statut" :value="$coupure->statut ?: 'prevue'" required>
                @foreach (['prevue' => 'Prévue', 'en_cours' => 'En cours', 'terminee' => 'Terminée'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('statut', $coupure->statut ?: 'prevue') === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </x-form-select>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <x-form-input name="date_debut" type="datetime-local" label="Date et heure de début" :value="$coupure->date_debut?->format('Y-m-d\\TH:i')" required />
        </div>

        <div class="col-md-6">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="date_fin" class="form-label mb-0">Date et heure de fin</label>
                <button type="button" id="btn-predire-ia" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 0.8rem;">
                    <i class="bx bx-bulb me-1"></i> Prédire la fin par IA
                </button>
            </div>
            <input
                type="datetime-local"
                class="form-control @error('date_fin') is-invalid @enderror"
                id="date_fin"
                name="date_fin"
                value="{{ old('date_fin', $coupure->date_fin?->format('Y-m-d\\TH:i')) }}"
            >
            @error('date_fin')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            {{-- Zone d'affichage du résultat de la prédiction IA --}}
            <div id="prediction-result" class="mt-2" style="display: none;"></div>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label" for="description">Description & Cause</label>
        <textarea
            class="form-control @error('description') is-invalid @enderror"
            id="description"
            name="description"
            rows="3"
            placeholder="Ex : Incident sur transformateur moyenne tension suite forte canicule..."
        >{{ old('description', $coupure->description) }}</textarea>
        @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="d-flex gap-2 mt-4">
        <button class="btn btn-primary" type="submit">
            <i class="bx bx-save me-1"></i> Enregistrer
        </button>
        <a href="{{ route('admin.coupures.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnPredire = document.getElementById('btn-predire-ia');
    const inputDateDebut = document.querySelector('input[name="date_debut"]');
    const inputDateFin = document.querySelector('input[name="date_fin"]');
    const selectType = document.querySelector('select[name="type"]');
    const selectQuartier = document.querySelector('select[name="quartier_id"]');
    const textDescription = document.querySelector('textarea[name="description"]');
    const resultContainer = document.getElementById('prediction-result');
    const token = document.querySelector('input[name="_token"]')?.value;

    if (!btnPredire) return;

    btnPredire.addEventListener('click', async function () {
        const type = selectType ? selectType.value : '';
        if (!type) {
            resultContainer.style.display = 'block';
            resultContainer.innerHTML = '<div class="alert alert-warning py-2 px-3 small mb-0"><i class="bx bx-error me-1"></i> Veuillez d\'abord sélectionner un type de coupure (Délestage, Panne ou Surcharge).</div>';
            if (selectType) selectType.focus();
            return;
        }

        const dateDebut = (inputDateDebut && inputDateDebut.value)
            ? inputDateDebut.value
            : new Date().toISOString().slice(0, 16);

        if (inputDateDebut && !inputDateDebut.value) {
            inputDateDebut.value = dateDebut;
        }

        btnPredire.disabled = true;
        btnPredire.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Calcul IA...';
        resultContainer.style.display = 'block';
        resultContainer.innerHTML = '<div class="alert alert-info py-2 px-3 small mb-0"><i class="bx bx-loader-alt bx-spin me-1"></i> Analyse historique et calcul prédictif STEG par IA en cours...</div>';

        try {
            const response = await fetch('{{ route('admin.coupures.predire-fin') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                },
                body: JSON.stringify({
                    quartier_id: selectQuartier ? (selectQuartier.value || null) : null,
                    type: type,
                    date_debut: dateDebut,
                    description: textDescription ? (textDescription.value || null) : null,
                }),
            });

            const data = await response.json();

            if (response.ok && data.date_fin_iso) {
                // Remplissage automatique du champ date_fin
                if (inputDateFin) {
                    inputDateFin.value = data.date_fin_iso;
                }

                // Affichage d'un badge explicatif enrichi
                resultContainer.innerHTML = `
                    <div class="alert alert-success border-success py-2 px-3 small mb-0">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <strong class="text-success">
                                <i class="bx bx-check-circle me-1"></i> Fin estimée : ${data.date_fin_affichage} (~${data.duree_formatee})
                            </strong>
                            <span class="badge bg-label-success">${data.confiance}</span>
                        </div>
                        <div class="text-muted">${data.explication}</div>
                        <div class="mt-1 font-monospace text-secondary" style="font-size: 0.75rem;">Source : ${data.source}</div>
                    </div>
                `;
            } else {
                resultContainer.innerHTML = `<div class="alert alert-danger py-2 px-3 small mb-0">Impossible d'obtenir une prédiction : ${data.message || 'Erreur inconnue'}</div>`;
            }
        } catch (e) {
            resultContainer.innerHTML = '<div class="alert alert-danger py-2 px-3 small mb-0">Erreur de communication avec le serveur.</div>';
        } finally {
            btnPredire.disabled = false;
            btnPredire.innerHTML = '<i class="bx bx-bulb me-1"></i> Prédire la fin par IA';
        }
    });
});
</script>
@endpush