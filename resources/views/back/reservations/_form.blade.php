{{--
    Formulaire partagé (création / modification) d'une réservation.

    Variables attendues :
      $reservation — modèle Reservation (neuf ou existant)
      $points      — collection PointFraicheur pour la liste déroulante
      $action      — URL de traitement du formulaire
      $method      — POST (création) ou PUT (modification)
--}}
<form method="POST" action="{{ $action }}">
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div class="row">
        <div class="col-md-6">
            <x-form-select
                name="point_fraicheur_id"
                label="Point de fraîcheur"
                :value="$reservation->point_fraicheur_id"
                placeholder="Sélectionnez un point..."
                required
            >
                @foreach ($points as $point)
                    <option value="{{ $point->id }}" @selected((int) old('point_fraicheur_id', $reservation->point_fraicheur_id) === $point->id)>
                        {{ $point->nom }}
                    </option>
                @endforeach
            </x-form-select>
        </div>
        <div class="col-md-6">
            <x-form-input
                name="nom"
                label="Nom du client"
                :value="$reservation->nom"
                placeholder="Ex. Amina Ben Salah"
                required
            />
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <x-form-input
                name="email"
                type="email"
                label="Adresse e-mail"
                :value="$reservation->email"
                placeholder="Ex. amina@example.com"
                required
            />
        </div>
        <div class="col-md-6">
            <x-form-input
                name="telephone"
                label="Téléphone"
                :value="$reservation->telephone"
                placeholder="Ex. 20 123 456"
            />
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <x-form-input
                name="nombre_personnes"
                type="number"
                label="Nombre de personnes"
                :value="$reservation->nombre_personnes ?? 1"
                placeholder="Ex. 2"
                required
            />
        </div>
        <div class="col-md-3">
            <x-form-input
                name="date_reservation"
                type="date"
                label="Date"
                :value="$reservation->date_reservation?->format('Y-m-d')"
                required
            />
        </div>
        <div class="col-md-3">
            <x-form-input
                name="heure_debut"
                type="time"
                label="Heure de début"
                :value="$reservation->heure_debut_courte"
                required
            />
        </div>
        <div class="col-md-3">
            <x-form-input
                name="heure_fin"
                type="time"
                label="Heure de fin"
                :value="$reservation->heure_fin_courte"
                required
            />
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <x-form-select name="statut" label="Statut" :value="$reservation->statut" required>
                @foreach (\App\Models\Reservation::STATUTS as $valeur => $libelle)
                    <option value="{{ $valeur }}" @selected(old('statut', $reservation->statut ?? 'en_attente') === $valeur)>
                        {{ $libelle }}
                    </option>
                @endforeach
            </x-form-select>
        </div>
        <div class="col-md-6 d-flex align-items-end">
            @if ($reservation->exists)
                <div class="ve-form-group app-form-group w-100">
                    <label>Code de réservation</label>
                    <input type="text" class="form-control" value="{{ $reservation->code }}" readonly>
                    <small class="text-muted">Le code est généré automatiquement et ne peut pas être modifié.</small>
                </div>
            @else
                <div class="alert alert-info mb-0 w-100">
                    <i class="bx bx-info-circle me-1"></i> Un code de réservation sera généré automatiquement.
                </div>
            @endif
        </div>
    </div>

    <div class="ve-form-group app-form-group">
        <label for="besoins_specifiques">Besoins spécifiques</label>
        <textarea
            name="besoins_specifiques"
            id="besoins_specifiques"
            rows="3"
            class="form-control @error('besoins_specifiques') is-invalid @enderror"
            placeholder="PMR, enfants en bas âge, personne âgée, problème de santé..."
        >{{ old('besoins_specifiques', $reservation->besoins_specifiques) }}</textarea>
        @error('besoins_specifiques')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="ve-form-group app-form-group">
        <label for="notes_admin">Notes internes</label>
        <textarea
            name="notes_admin"
            id="notes_admin"
            rows="3"
            class="form-control @error('notes_admin') is-invalid @enderror"
            placeholder="Notes visibles uniquement par l'administrateur..."
        >{{ old('notes_admin', $reservation->notes_admin) }}</textarea>
        @error('notes_admin')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i> Enregistrer
        </button>
        <a href="{{ route('admin.reservations.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
