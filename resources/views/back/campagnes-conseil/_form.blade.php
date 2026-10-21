<form method="POST" action="{{ $action }}" novalidate>
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    {{-- Conseil diffusé (relation belongsTo) --}}
    <x-form-select
        name="conseil_id"
        label="Conseil"
        :value="$campagne->conseil_id"
        placeholder="Sélectionnez un conseil..."
    >
        @foreach ($conseils as $conseil)
            <option value="{{ $conseil->id }}"
                @selected((int) old('conseil_id', $campagne->conseil_id) === $conseil->id)>
                {{ $conseil->titre }}
            </option>
        @endforeach
    </x-form-select>

    <x-form-input
        name="titre"
        label="Titre de la campagne"
        :value="$campagne->titre"
        placeholder="Ex. Restez hydratés pendant la canicule"
    />

    <x-form-select
        name="public_cible"
        label="Public cible"
        :value="$campagne->public_cible"
        placeholder="Sélectionnez un public..."
    >
        @foreach (\App\Models\CampagneConseil::PUBLICS as $valeur => $libelle)
            <option value="{{ $valeur }}"
                @selected(old('public_cible', $campagne->public_cible) === $valeur)>
                {{ $libelle }}
            </option>
        @endforeach
    </x-form-select>

    <x-form-select
        name="canal"
        label="Canal de diffusion"
        :value="$campagne->canal"
        placeholder="Sélectionnez un canal..."
    >
        @foreach (\App\Models\CampagneConseil::CANAUX as $valeur => $libelle)
            <option value="{{ $valeur }}"
                @selected(old('canal', $campagne->canal) === $valeur)>
                {{ $libelle }}
            </option>
        @endforeach
    </x-form-select>

    {{-- Dates : aucune contrainte HTML, tout est contrôlé côté serveur --}}
    <div class="row">
        <div class="col-md-6">
            <div class="ve-form-group app-form-group">
                <label for="date_debut">Date de début</label>
                <input
                    type="date"
                    name="date_debut"
                    id="date_debut"
                    class="form-control @error('date_debut') is-invalid @enderror"
                    value="{{ old('date_debut', $campagne->date_debut?->format('Y-m-d')) }}"
                >
                @error('date_debut')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="ve-form-group app-form-group">
                <label for="date_fin">Date de fin <span class="text-muted">(facultative)</span></label>
                <input
                    type="date"
                    name="date_fin"
                    id="date_fin"
                    class="form-control @error('date_fin') is-invalid @enderror"
                    value="{{ old('date_fin', $campagne->date_fin?->format('Y-m-d')) }}"
                >
                @error('date_fin')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <x-form-select
        name="statut"
        label="Statut"
        :value="$campagne->statut"
        placeholder="Sélectionnez un statut..."
    >
        @foreach (\App\Models\CampagneConseil::STATUTS as $valeur => $libelle)
            <option value="{{ $valeur }}"
                @selected(old('statut', $campagne->statut) === $valeur)>
                {{ $libelle }}
            </option>
        @endforeach
    </x-form-select>

    {{-- Message de diffusion --}}
    <div class="ve-form-group app-form-group">
        <label for="message">Message de diffusion <span class="text-muted">(facultatif, max 1 000 caractères)</span></label>
        <textarea name="message" id="message" rows="3"
                  class="form-control @error('message') is-invalid @enderror"
                  placeholder="Message de la campagne...">{{ old('message', $campagne->message) }}</textarea>
        @error('message')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="card mb-3"><div class="card-header">Assistance IA (Anthropic)</div><div class="card-body">
        <div class="d-flex flex-wrap gap-2 mb-2">
            <button type="button" class="btn btn-outline-primary" data-campagne-ai="rediger">Rédiger avec l’IA</button>
            <button type="button" class="btn btn-outline-secondary" data-campagne-ai="reformuler">Reformuler pour ce public</button>
            <button type="button" class="btn btn-outline-secondary" data-campagne-ai="traduire">Traduire</button>
            <button type="button" class="btn btn-outline-info" data-campagne-ai="diffusion">Suggérer une diffusion</button>
            <button type="button" class="btn btn-outline-warning" data-campagne-ai="qualite">Contrôler le message</button>
        </div>
        <select id="campagne-ai-langue" class="form-select mb-2"><option value="ar">Arabe</option><option value="en">Anglais</option></select>
        <div id="campagne-ai-feedback" role="status" aria-live="polite"></div>
        <div class="d-flex align-items-start gap-2 mt-2"><div id="campagne-ai-translation" class="alert alert-info d-none mb-0 flex-grow-1" dir="ltr"></div><button type="button" id="campagne-ai-copy" class="btn btn-outline-secondary d-none">Copier</button></div>
    </div></div>

    {{-- Activation automatique --}}
    <div class="form-check mb-3">
        <input type="hidden" name="activation_auto" value="0">
        <input type="checkbox" name="activation_auto" value="1"
               class="form-check-input" id="activation_auto"
               @checked(old('activation_auto', $campagne->activation_auto))>
        <label class="form-check-label" for="activation_auto">
            Activation automatique à la date de début
        </label>
        @error('activation_auto')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i> Enregistrer
        </button>
        <a href="{{ route('admin.campagnes-conseil.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>

@push('scripts')
<script>
document.querySelectorAll('[data-campagne-ai]').forEach((button) => button.addEventListener('click', async () => {
    const feedback = document.getElementById('campagne-ai-feedback');
    const form = button.closest('form');
    const action = button.dataset.campagneAi;
    const data = { action };
    const value = (name) => form.querySelector(`[name="${name}"]`)?.value ?? '';
    if (action === 'rediger' || action === 'diffusion') { data.conseil_id = value('conseil_id'); data.canal = value('canal'); }
    if (action === 'reformuler') { data.message = value('message'); data.public_cible = value('public_cible'); }
    if (action === 'traduire') { data.message = value('message'); data.langue = document.getElementById('campagne-ai-langue').value; }
    if (action === 'qualite') data.message = value('message');
    button.disabled = true; feedback.textContent = 'Génération en cours…';
    try {
        const response = await fetch('{{ route('admin.campagnes-conseil.ia') }}', { method: 'POST', headers: {'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':form.querySelector('[name="_token"]').value}, body: JSON.stringify(data) });
        const result = await response.json();
        if (!response.ok) throw new Error(result.message || Object.values(result.errors || {}).flat().join(' '));
        if (result.titre) form.querySelector('[name="titre"]').value = result.titre;
        if (result.message) form.querySelector('[name="message"]').value = result.message;
        if (result.public_cible) form.querySelector('[name="public_cible"]').value = result.public_cible;
        if (result.canal) form.querySelector('[name="canal"]').value = result.canal;
        if (result.date_debut) form.querySelector('[name="date_debut"]').value = result.date_debut;
        if (result.date_fin) form.querySelector('[name="date_fin"]').value = result.date_fin;
        const translation = document.getElementById('campagne-ai-translation');
        if (result.traduction) { translation.textContent = result.traduction; translation.dir = data.langue === 'ar' ? 'rtl' : 'ltr'; translation.classList.remove('d-none'); document.getElementById('campagne-ai-copy').classList.remove('d-none'); }
        feedback.textContent = result.score !== undefined ? `Qualité : ${result.score}/10 — ${(result.remarques || []).join(' · ')}` : 'Suggestion prête. Relisez-la avant de l’enregistrer.';
    } catch (error) { feedback.textContent = error.message || 'Le service IA est indisponible.'; }
    finally { button.disabled = false; }
}));
document.getElementById('campagne-ai-copy').addEventListener('click',async()=>{const text=document.getElementById('campagne-ai-translation').textContent;try{await navigator.clipboard.writeText(text);document.getElementById('campagne-ai-feedback').textContent='Traduction copiée.';}catch{document.getElementById('campagne-ai-feedback').textContent='Copie impossible dans ce navigateur.';}});
</script>
@endpush
