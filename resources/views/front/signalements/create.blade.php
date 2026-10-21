@extends('front.layouts.front')

@section('title', 'Soumettre un signalement')
@section('description', 'Signalez un incident, une coupure ou tout problème lié à la canicule dans votre quartier.')

@section('content')
    <section class="ve-section pf-section">
        <div class="container">
            {{-- Fil d'Ariane --}}
            <nav class="pf-breadcrumb" aria-label="Fil d'Ariane">
                <a href="{{ route('front.home') }}">Accueil</a>
                <span>/</span>
                <a href="{{ route('signalements.index') }}">Signalements</a>
                <span>/</span>
                <strong>Nouveau signalement</strong>
            </nav>

            <div class="ve-section-header text-center">
                <span class="ve-section-tag">Signalements</span>
                <h2>Soumettre un <span>signalement</span></h2>
                <p>Signalez un incident, une coupure ou tout problème lié à la canicule.</p>
            </div>

            <div style="max-width:680px;margin:0 auto;">
                <div class="pf-detail-card wow fadeInUp">
                    <form method="POST" action="{{ route('signalements.store') }}" novalidate>
                        @csrf

                        {{-- Champ caché requis par la validation (forcé à 'nouveau' par le contrôleur) --}}
                        <input type="hidden" name="statut" value="nouveau">

                        <div class="mb-3">
                            <label for="type_signalement_id" style="display:block;font-weight:600;margin-bottom:6px;">
                                Type de signalement <span style="color:red;">*</span>
                            </label>
                            <select
                                id="type_signalement_id"
                                name="type_signalement_id"
                                style="width:100%;padding:10px 14px;border:1px solid #ddd;border-radius:6px;font-size:1rem;"
                                required
                            >
                                <option value="">-- Sélectionner un type --</option>
                                @foreach ($typesSignalement as $type)
                                    <option
                                        value="{{ $type->id }}"
                                        data-diagnostic="{{ in_array($type->libelle, ['Coupure observée', 'Panne'], true) ? '1' : '0' }}"
                                        @selected(old('type_signalement_id') == $type->id)
                                    >
                                        {{ $type->libelle }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type_signalement_id')
                                <p style="color:red;font-size:0.875rem;margin-top:4px;">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" style="display:block;font-weight:600;margin-bottom:6px;">
                                Description <span style="color:red;">*</span>
                            </label>
                            <textarea
                                id="description"
                                name="description"
                                rows="6"
                                maxlength="1000"
                                placeholder="Décrivez l'incident en détail..."
                                style="width:100%;padding:10px 14px;border:1px solid #ddd;border-radius:6px;font-size:1rem;resize:vertical;"
                                required
                            >{{ old('description') }}</textarea>
                            @error('description')
                                <p style="color:red;font-size:0.875rem;margin-top:4px;">{{ $message }}</p>
                            @enderror
                            <small style="color:#666;">Maximum 1000 caractères.</small>
                        </div>

                        {{-- ============================================================
                             Section Diagnostic de la panne (facultatif)
                        ============================================================ --}}
                        <div class="mb-3" style="border:1px dashed #ccc;border-radius:8px;padding:18px;background:#fafafa;">
                            <label style="display:flex;align-items:center;gap:8px;font-weight:600;margin-bottom:0;cursor:pointer;">
                                <input type="checkbox" name="avec_diagnostic" id="avec_diagnostic" value="1" @checked(old('avec_diagnostic'))>
                                <i class="fa fa-wrench" aria-hidden="true"></i>
                                Ajouter un diagnostic de la panne <span style="font-weight:400;color:#777;">(facultatif)</span>
                            </label>
                            <p style="font-size:.875rem;color:#666;margin:6px 0 0;">
                                Répondez à quelques questions : cela aide nos équipes à identifier la cause et le niveau de danger.
                            </p>

                            <div id="bloc-diagnostic" style="margin-top:16px;{{ old('avec_diagnostic') ? '' : 'display:none;' }}">
                                <div class="mb-3">
                                    <label for="zone_touchee" style="display:block;font-weight:600;margin-bottom:6px;">
                                        Qui est touché ? <span style="color:red;">*</span>
                                    </label>
                                    <select id="zone_touchee" name="zone_touchee"
                                            style="width:100%;padding:10px 14px;border:1px solid #ddd;border-radius:6px;font-size:1rem;">
                                        <option value="">-- Sélectionner --</option>
                                        @foreach (\App\Models\Diagnostic::ZONES as $key => $label)
                                            <option value="{{ $key }}" @selected(old('zone_touchee') === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('zone_touchee')
                                        <p style="color:red;font-size:0.875rem;margin-top:4px;">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div style="display:flex;flex-wrap:wrap;gap:12px;">
                                    <div class="mb-3" style="flex:1 1 200px;">
                                        <label for="heure_panne" style="display:block;font-weight:600;margin-bottom:6px;">
                                            Heure de la panne <span style="color:red;">*</span>
                                        </label>
                                        <input type="time" id="heure_panne" name="heure_panne"
                                               value="{{ old('heure_panne', now()->format('H:i')) }}"
                                               required
                                               style="width:100%;padding:10px 14px;border:1px solid #ddd;border-radius:6px;font-size:1rem;">
                                        @error('heure_panne')
                                            <p style="color:red;font-size:0.875rem;margin-top:4px;">{{ $message }}</p>
                                        @enderror
                                        <small style="color:#888;font-size:0.8rem;">Format 24 h &mdash; ex : 14:35</small>
                                    </div>
                                    <div class="mb-3" style="flex:1 1 200px;">
                                        <label for="temperature" style="display:block;font-weight:600;margin-bottom:6px;">
                                            Température extérieure (°C)
                                        </label>
                                        <input type="number" id="temperature" name="temperature" step="0.1"
                                               value="{{ old('temperature') }}" placeholder="Ex : 43"
                                               style="width:100%;padding:10px 14px;border:1px solid #ddd;border-radius:6px;font-size:1rem;">
                                        @error('temperature')
                                            <p style="color:red;font-size:0.875rem;margin-top:4px;">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <p style="font-weight:600;margin-bottom:6px;">Cochez ce que vous avez constaté :</p>
                                @foreach (\App\Models\Diagnostic::SYMPTOMES as $champ => $label)
                                    <label style="display:flex;align-items:center;gap:8px;font-weight:400;margin-bottom:6px;cursor:pointer;">
                                        <input type="checkbox" name="{{ $champ }}" value="1" @checked(old($champ))>
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="pf-detail-actions" style="text-align:left;">
                            <button type="submit" class="ve-btn-primary">
                                <i class="fa fa-paper-plane" aria-hidden="true"></i> Envoyer le signalement
                            </button>
                            <a href="{{ route('signalements.index') }}" class="ve-btn-ghost">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    // Affiche / masque le bloc diagnostic.
    const caseDiagnostic = document.getElementById('avec_diagnostic');
    const blocDiagnostic = document.getElementById('bloc-diagnostic');

    function majBlocDiagnostic() {
        blocDiagnostic.style.display = caseDiagnostic.checked ? '' : 'none';
    }

    caseDiagnostic.addEventListener('change', majBlocDiagnostic);

    // Propose automatiquement le diagnostic pour les types « Coupure observée » et « Panne ».
    document.getElementById('type_signalement_id').addEventListener('change', function () {
        const option = this.options[this.selectedIndex];
        caseDiagnostic.checked = option.dataset.diagnostic === '1';
        majBlocDiagnostic();
    });
</script>
@endpush
