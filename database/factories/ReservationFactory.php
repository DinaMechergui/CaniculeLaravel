<?php

namespace Database\Factories;

use App\Models\PointFraicheur;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Nom du modèle généré.
     *
     * @var class-string<Reservation>
     */
    protected $model = Reservation::class;

    /**
     * Besoins spécifiques réalistes (pour tester l'analyse par l'IA).
     *
     * @var list<string>
     */
    private const BESOINS = [
        'Personne à mobilité réduite, accès fauteuil roulant souhaité.',
        'Un enfant en bas âge (poussette).',
        'Personne âgée de 82 ans, prévoir un endroit ombragé.',
        'Problème de santé : insuffisance respiratoire, éviter le plein soleil.',
        'Groupe familial avec deux jeunes enfants.',
        'Vient avec un petit chien en laisse.',
        'Femme enceinte, besoin d\'un lieu calme et assis.',
    ];

    /**
     * Garantit qu'un code est attribué, même hors événements de modèle, et
     * réaligne les horodatages de statut sur la valeur finale du statut.
     *
     * Le DatabaseSeeder utilise le trait WithoutModelEvents : l'événement
     * « creating » du modèle n'est donc pas déclenché pendant le seeding. Le
     * code est ici généré au moment du « make », ce qui couvre ce cas (et les
     * tests) tout en laissant le modèle gérer la génération en usage normal.
     *
     * Le réalignement évite une incohérence fréquente : si le statut est
     * surchargé par l'appelant (ex. « confirmee »), les champs dérivés
     * (check_in_le, annulee_le...) doivent être remis en cohérence.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Reservation $reservation) {
            if (empty($reservation->code)) {
                $reservation->code = Reservation::genererCode();
            }

            $statut = $reservation->statut;
            $reserve = [$reservation->confirmee_le, $reservation->annulee_le, $reservation->check_in_le];

            $reservation->confirmee_le = in_array($statut, [Reservation::STATUT_CONFIRMEE, Reservation::STATUT_TERMINEE], true)
                ? ($reserve[0] ?? now())
                : null;
            $reservation->annulee_le = $statut === Reservation::STATUT_ANNULEE
                ? ($reserve[1] ?? now())
                : null;
            $reservation->check_in_le = $statut === Reservation::STATUT_TERMINEE
                ? ($reserve[2] ?? now())
                : null;
        });
    }

    /**
     * Définition des valeurs par défaut d'une réservation.
     *
     * Les statuts restent cohérents avec la date : passé → terminée / no-show /
     * annulée ; aujourd'hui → en attente / confirmée / terminée ; futur →
     * en attente / confirmée / liste d'attente.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Créneau réparti des 30 derniers jours aux 14 prochains jours.
        $decalageJours = fake()->numberBetween(-30, 14);
        $date = now()->addDays($decalageJours)->startOfDay();

        // Créneaux par pas d'une heure entre 08:00 et 20:00 (durée 1 à 2 h).
        $heureDebut = fake()->numberBetween(8, 18);
        $heureFin = min($heureDebut + fake()->numberBetween(1, 2), 20);

        $statut = $this->statutPour($decalageJours);

        return [
            'point_fraicheur_id' => PointFraicheur::factory(),
            'nom' => fake()->name(),
            'email' => fake()->safeEmail(),
            'telephone' => fake()->optional(0.7)->numerify('## ### ###'),
            'nombre_personnes' => fake()->numberBetween(1, 10),
            'date_reservation' => $date->toDateString(),
            'heure_debut' => sprintf('%02d:00', $heureDebut),
            'heure_fin' => sprintf('%02d:00', $heureFin),
            'statut' => $statut,
            'besoins_specifiques' => fake()->optional(0.6)->randomElement(self::BESOINS),
            'confirmee_le' => in_array($statut, [Reservation::STATUT_CONFIRMEE, Reservation::STATUT_TERMINEE], true)
                ? $date->copy()->subDay()->setTime(9, 0)
                : null,
            'annulee_le' => $statut === Reservation::STATUT_ANNULEE
                ? $date->copy()->subDay()->setTime(10, 30)
                : null,
            'check_in_le' => $statut === Reservation::STATUT_TERMINEE
                ? $date->copy()->setTime($heureDebut, 0)
                : null,
        ];
    }

    /**
     * Choisit un statut cohérent avec l'ancienneté du créneau.
     */
    private function statutPour(int $decalageJours): string
    {
        if ($decalageJours < 0) {
            return fake()->randomElement([
                Reservation::STATUT_TERMINEE,
                Reservation::STATUT_TERMINEE,
                Reservation::STATUT_NO_SHOW,
                Reservation::STATUT_ANNULEE,
            ]);
        }

        if ($decalageJours === 0) {
            return fake()->randomElement([
                Reservation::STATUT_CONFIRMEE,
                Reservation::STATUT_EN_ATTENTE,
                Reservation::STATUT_TERMINEE,
            ]);
        }

        return fake()->randomElement([
            Reservation::STATUT_EN_ATTENTE,
            Reservation::STATUT_CONFIRMEE,
            Reservation::STATUT_CONFIRMEE,
            Reservation::STATUT_LISTE_ATTENTE,
        ]);
    }
}
