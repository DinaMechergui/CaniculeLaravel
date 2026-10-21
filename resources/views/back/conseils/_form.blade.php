<form method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    {{-- Liste déroulante des catégories (relation belongsTo) --}}
    <x-form-select
        name="categorie_conseil_id"
        label="Catégorie"
        :value="$conseil->categorie_conseil_id"
        placeholder="Sélectionnez une catégorie..."
        required
    >
        @foreach ($categories as $categorie)
            <option value="{{ $categorie->id }}"
                @selected((int) old('categorie_conseil_id', $conseil->categorie_conseil_id) === $categorie->id)>
                {{ $categorie->nom }}
            </option>
        @endforeach
    </x-form-select>

    <x-form-input
        name="titre"
        label="Titre"
        :value="$conseil->titre"
        placeholder="Ex. Fermer les volets en journée"
        required
    />

    {{-- Zone de texte pour le contenu, avec le bouton IA --}}
    <div class="ve-form-group app-form-group">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="contenu" class="mb-0">Contenu <span class="app-required">*</span></label>
            <button type="button" id="btn-ia" class="btn btn-sm btn-outline-primary">
                <i class="bx bx-bot me-1"></i> Rédiger avec l'IA
            </button>
        </div>

        <textarea
            name="contenu"
            id="contenu"
            rows="8"
            class="form-control @error('contenu') is-invalid @enderror"
            required
        >{{ old('contenu', $conseil->contenu) }}</textarea>
        @error('contenu')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        {{-- Message d'état de la génération (succès ou erreur) --}}
        <div id="ia-message" class="small mt-1"></div>
    </div>

    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i> Enregistrer
        </button>
        <a href="{{ route('admin.conseils.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>

@push('scripts')
<script>
    (function () {
        const bouton = document.getElementById('btn-ia');
        const message = document.getElementById('ia-message');
        const contenu = document.getElementById('contenu');
        const titre = document.getElementById('titre');
        const categorie = document.getElementById('categorie_conseil_id');
        const token = document.querySelector('input[name="_token"]').value;

        function afficher(texte, erreur) {
            message.textContent = texte;
            message.className = 'small mt-1 ' + (erreur ? 'text-danger' : 'text-success');
        }

        bouton.addEventListener('click', async function () {
            if (!titre.value.trim()) {
                afficher('Saisissez d\'abord un titre pour le conseil.', true);
                titre.focus();
                return;
            }

            // Si du texte existe déjà, on demande confirmation avant de le remplacer.
            if (contenu.value.trim() && !confirm('Remplacer le contenu actuel par un texte généré ?')) {
                return;
            }

            bouton.disabled = true;
            bouton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Rédaction en cours...';
            afficher('', false);

            try {
                const reponse = await fetch('{{ route('admin.conseils.generer') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify({
                        titre: titre.value,
                        categorie: categorie.selectedIndex > 0
                            ? categorie.options[categorie.selectedIndex].text.trim()
                            : '',
                    }),
                });

                const donnees = await reponse.json();

                if (!reponse.ok) {
                    afficher(donnees.message || 'La génération a échoué.', true);
                } else {
                    contenu.value = donnees.contenu;
                    afficher('Texte généré par IA : relisez-le et corrigez-le avant d\'enregistrer.', false);
                }
            } catch (e) {
                afficher('Impossible de joindre le serveur.', true);
            } finally {
                bouton.disabled = false;
                bouton.innerHTML = '<i class="bx bx-bot me-1"></i> Rédiger avec l\'IA';
            }
        });
    })();
</script>
@endpush
