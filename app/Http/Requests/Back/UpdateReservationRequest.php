<?php

namespace App\Http\Requests\Back;

use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation de la modification d'une réservation (back office).
 *
 * Contrairement à la création, la date peut être passée : l'administrateur
 * doit pouvoir corriger une réservation historique (statut, notes...).
 */
class UpdateReservationRequest extends FormRequest
{
    /**
     * Les routes sont déjà protégées par le middleware « admin ».
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'point_fraicheur_id' => ['required', 'exists:points_fraicheur,id'],
            'nom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'telephone' => ['nullable', 'string', 'max:20'],
            'nombre_personnes' => ['required', 'integer', 'min:1', 'max:10'],
            'date_reservation' => ['required', 'date'],
            'heure_debut' => ['required', 'date_format:H:i'],
            'heure_fin' => ['required', 'date_format:H:i', 'after:heure_debut'],
            'statut' => ['required', Rule::in(array_keys(Reservation::STATUTS))],
            'besoins_specifiques' => ['nullable', 'string', 'max:500'],
            'notes_admin' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Messages d'erreur en français.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'point_fraicheur_id.required' => 'Le point de fraîcheur est obligatoire.',
            'point_fraicheur_id.exists' => 'Le point de fraîcheur sélectionné n\'existe pas.',
            'nom.required' => 'Le nom du client est obligatoire.',
            'nom.max' => 'Le nom du client ne doit pas dépasser 100 caractères.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'email.max' => 'L\'adresse e-mail ne doit pas dépasser 150 caractères.',
            'telephone.max' => 'Le téléphone ne doit pas dépasser 20 caractères.',
            'nombre_personnes.required' => 'Le nombre de personnes est obligatoire.',
            'nombre_personnes.integer' => 'Le nombre de personnes doit être un nombre entier.',
            'nombre_personnes.min' => 'Le nombre de personnes doit être au minimum de 1.',
            'nombre_personnes.max' => 'Le nombre de personnes ne doit pas dépasser 10.',
            'date_reservation.required' => 'La date de réservation est obligatoire.',
            'date_reservation.date' => 'La date de réservation n\'est pas valide.',
            'heure_debut.required' => 'L\'heure de début est obligatoire.',
            'heure_debut.date_format' => 'L\'heure de début doit être au format HH:MM.',
            'heure_fin.required' => 'L\'heure de fin est obligatoire.',
            'heure_fin.date_format' => 'L\'heure de fin doit être au format HH:MM.',
            'heure_fin.after' => 'L\'heure de fin doit être postérieure à l\'heure de début.',
            'statut.required' => 'Le statut est obligatoire.',
            'statut.in' => 'Le statut sélectionné est invalide.',
            'besoins_specifiques.max' => 'Les besoins spécifiques ne doivent pas dépasser 500 caractères.',
            'notes_admin.max' => 'Les notes internes ne doivent pas dépasser 2 000 caractères.',
        ];
    }
}
