<?php

namespace App\Http\Requests\Front;

use App\Models\PointFraicheur;
use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation de la création d'une réservation depuis le front office
 * (le client réserve un créneau sur la fiche d'un point de fraîcheur).
 */
class StoreReservationRequest extends FormRequest
{
    /**
     * Ouvert à tous : le client n'a pas forcément de compte.
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
            // Le contrôle de capacité restant est effectué dans la règle ci-dessous.
            'nombre_personnes' => ['required', 'integer', 'min:1', 'max:50', $this->capaciteDisponible()],
            'date_reservation' => ['required', 'date', 'after_or_equal:today'],
            'heure_debut' => ['required', 'date_format:H:i'],
            'heure_fin' => ['required', 'date_format:H:i', 'after:heure_debut'],
            'besoins_specifiques' => ['nullable', 'string', 'max:500', $this->sansSpam()],
            // Case à cocher : accepter d'être placé sur liste d'attente si le
            // créneau est complet (désactive alors le contrôle de capacité).
            'liste_attente' => ['nullable', 'boolean'],
            // Pot de miel (honeypot) : ce champ est masqué et doit rester vide.
            // Un robot qui le remplit est rejeté.
            'website' => ['nullable', 'max:0'],
        ];
    }

    /**
     * Règle : les places restantes sur le créneau doivent suffire.
     *
     * On additionne les personnes déjà réservées (réservations consommant la
     * capacité du point, même jour, sur un créneau qui chevauche) et on
     * vérifie que le total ne dépasse pas la capacité d'accueil du point.
     * Si le client accepte la liste d'attente, le contrôle est ignoré : la
     * réservation sera enregistrée avec le statut « liste_attente ».
     */
    private function capaciteDisponible(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if ($this->boolean('liste_attente')) {
                return;
            }

            $point = PointFraicheur::find($this->input('point_fraicheur_id'));
            $date = $this->input('date_reservation');
            $debut = $this->input('heure_debut');
            $fin = $this->input('heure_fin');

            // Les autres règles signaleront déjà ces champs manquants.
            if ($point === null || empty($date) || empty($debut) || empty($fin)) {
                return;
            }

            $dejaReservees = Reservation::query()
                ->whereIn('statut', [
                    Reservation::STATUT_EN_ATTENTE,
                    Reservation::STATUT_CONFIRMEE,
                ])
                ->where('point_fraicheur_id', $point->id)
                ->whereDate('date_reservation', $date)
                // Chevauchement de créneaux : début demandé < fin existante
                // et fin demandée > début existant.
                ->where('heure_debut', '<', substr($fin, 0, 5))
                ->where('heure_fin', '>', substr($debut, 0, 5))
                ->sum('nombre_personnes');

            $restantes = $point->capacite - $dejaReservees;

            if ((int) $value > $restantes) {
                $fail($restantes > 0
                    ? "Il ne reste que {$restantes} place(s) sur ce créneau."
                    : 'Ce créneau est complet, veuillez en choisir un autre ou rejoindre la liste d\'attente.');
            }
        };
    }

    /**
     * Règle : rejeter un message contenant du contenu indésirable (spam).
     */
    private function sansSpam(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (app(\App\Services\SpamDetectionService::class)->estSpam(is_string($value) ? $value : null)) {
                $fail('Votre message a été identifié comme indésirable.');
            }
        };
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
            'nom.required' => 'Votre nom est obligatoire.',
            'nom.max' => 'Votre nom ne doit pas dépasser 100 caractères.',
            'email.required' => 'Votre adresse e-mail est obligatoire.',
            'email.email' => 'Votre adresse e-mail n\'est pas valide.',
            'email.max' => 'Votre adresse e-mail ne doit pas dépasser 150 caractères.',
            'telephone.max' => 'Le téléphone ne doit pas dépasser 20 caractères.',
            'nombre_personnes.required' => 'Le nombre de personnes est obligatoire.',
            'nombre_personnes.integer' => 'Le nombre de personnes doit être un nombre entier.',
            'nombre_personnes.min' => 'Le nombre de personnes doit être au minimum de 1.',
            'nombre_personnes.max' => 'Le nombre de personnes ne doit pas dépasser 50.',
            'date_reservation.required' => 'La date de réservation est obligatoire.',
            'date_reservation.date' => 'La date de réservation n\'est pas valide.',
            'date_reservation.after_or_equal' => 'La date de réservation doit être aujourd\'hui ou une date future.',
            'heure_debut.required' => 'L\'heure de début est obligatoire.',
            'heure_debut.date_format' => 'L\'heure de début doit être au format HH:MM.',
            'heure_fin.required' => 'L\'heure de fin est obligatoire.',
            'heure_fin.date_format' => 'L\'heure de fin doit être au format HH:MM.',
            'heure_fin.after' => 'L\'heure de fin doit être postérieure à l\'heure de début.',
            'besoins_specifiques.max' => 'Les besoins spécifiques ne doivent pas dépasser 500 caractères.',
            'website.max' => 'Votre requête a été rejetée.',
        ];
    }
}
