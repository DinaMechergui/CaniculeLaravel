<?php

namespace App\Services;

use App\Models\PointFraicheur;
use App\Models\Reservation;
use App\Notifications\PlaceLiberee;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

/**
 * Logique métier du module « Réservations ».
 *
 * Regroupe le calcul de capacité, l'anti-surréservation (transaction +
 * verrou), la gestion de la liste d'attente, la génération des créneaux et
 * les règles métier (durée, délai minimum, quota et chevauchement par e-mail).
 */
class ReservationService
{
    /** Première heure d'ouverture d'un créneau (08:00). */
    public const HEURE_OUVERTURE = 8;

    /** Heure de fermeture (le dernier créneau se termine à 20:00). */
    public const HEURE_FERMETURE = 20;

    /** Délai minimum, en minutes, entre la réservation et le début du créneau. */
    public const DELAI_MIN_MINUTES = 30;

    /** Durée maximale d'un créneau, en heures. */
    public const DUREE_MAX_HEURES = 3;

    /** Nombre maximal de réservations actives par e-mail et par jour. */
    public const MAX_RESERVATIONS_PAR_JOUR = 2;

    /**
     * Statuts consommant réellement la capacité d'accueil du point.
     *
     * La liste d'attente ne consomme pas de place.
     */
    public const STATUTS_CONSOMMATEURS = [
        Reservation::STATUT_EN_ATTENTE,
        Reservation::STATUT_CONFIRMEE,
    ];

    /**
     * Statuts considérés comme « actifs » pour le quota et le chevauchement.
     */
    public const STATUTS_ACTIFS = [
        Reservation::STATUT_EN_ATTENTE,
        Reservation::STATUT_CONFIRMEE,
        Reservation::STATUT_LISTE_ATTENTE,
    ];

    /**
     * Libellés lisibles des niveaux d'affluence.
     */
    public const AFFLUENCES = [
        'libre' => 'Libre',
        'modere' => 'Modérée',
        'presque_complet' => 'Presque complet',
        'complet' => 'Complet',
    ];

    /**
     * Nombre de personnes déjà réservées sur un créneau (chevauchement).
     *
     * Deux créneaux se chevauchent lorsque : début demandé < fin existante
     * ET fin demandée > début existant.
     */
    public function placesUtilisees(
        PointFraicheur $point,
        string $date,
        string $debut,
        string $fin,
        ?int $exceptId = null
    ): int {
        return (int) Reservation::query()
            ->where('point_fraicheur_id', $point->id)
            ->whereIn('statut', self::STATUTS_CONSOMMATEURS)
            ->whereDate('date_reservation', $date)
            ->where('heure_debut', '<', $this->hhmm($fin))
            ->where('heure_fin', '>', $this->hhmm($debut))
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
            ->sum('nombre_personnes');
    }

    /**
     * Places restantes sur un créneau (jamais négatif).
     */
    public function placesRestantes(
        PointFraicheur $point,
        string $date,
        string $debut,
        string $fin,
        ?int $exceptId = null
    ): int {
        return max(0, (int) $point->capacite - $this->placesUtilisees($point, $date, $debut, $fin, $exceptId));
    }

    /**
     * Créneaux horaires d'une journée (08:00 → 20:00) avec taux d'occupation
     * et niveau d'affluence. Utilisé par l'endpoint JSON et le front office.
     *
     * @return list<array<string, mixed>>
     */
    public function creneauxDisponibles(PointFraicheur $point, string $date): array
    {
        $capacite = max(0, (int) $point->capacite);
        $creneaux = [];

        for ($heure = self::HEURE_OUVERTURE; $heure < self::HEURE_FERMETURE; $heure++) {
            $debut = sprintf('%02d:00', $heure);
            $fin = sprintf('%02d:00', $heure + 1);

            $utilisees = $this->placesUtilisees($point, $date, $debut, $fin);
            $restantes = max(0, $capacite - $utilisees);
            $affluence = $this->niveauAffluence($utilisees, $capacite);

            $creneaux[] = [
                'debut' => $debut,
                'fin' => $fin,
                'libelle' => $debut . ' - ' . $fin,
                'utilisees' => $utilisees,
                'restantes' => $restantes,
                'capacite' => $capacite,
                'affluence' => $affluence,
                'affluence_label' => self::AFFLUENCES[$affluence] ?? $affluence,
                'disponible' => $restantes > 0,
            ];
        }

        return $creneaux;
    }

    /**
     * Détermine le niveau d'affluence à partir du taux d'occupation.
     */
    public function niveauAffluence(int $utilisees, int $capacite): string
    {
        if ($capacite <= 0) {
            return 'complet';
        }

        $ratio = $utilisees / $capacite;

        return match (true) {
            $ratio >= 1.0 => 'complet',
            $ratio >= 0.8 => 'presque_complet',
            $ratio >= 0.5 => 'modere',
            default => 'libre',
        };
    }

    /**
     * Vérifie les règles métier et lève une ValidationException (messages en
     * français) si une règle n'est pas respectée.
     *
     * @param array<string, mixed> $donnees
     *
     * @throws ValidationException
     */
    public function verifierRegles(array $donnees): void
    {
        $debut = Carbon::parse($donnees['date_reservation'] . ' ' . $this->hhmm($donnees['heure_debut']));
        $fin = Carbon::parse($donnees['date_reservation'] . ' ' . $this->hhmm($donnees['heure_fin']));
        $erreurs = [];

        // Durée maximale du créneau.
        if (abs($debut->diffInMinutes($fin)) > self::DUREE_MAX_HEURES * 60) {
            $erreurs['heure_fin'] = 'Un créneau ne peut pas dépasser ' . self::DUREE_MAX_HEURES . ' heures.';
        }

        // Délai minimum de réservation (uniquement pour le jour même).
        if ($debut->isSameDay(now()) && $debut->lt(now()->addMinutes(self::DELAI_MIN_MINUTES))) {
            $erreurs['heure_debut'] = 'Le créneau doit être réservé au moins '
                . self::DELAI_MIN_MINUTES . ' minutes à l\'avance.';
        }

        $email = $donnees['email'] ?? null;

        if (! empty($email)) {
            $activesDuJour = Reservation::query()
                ->where('email', $email)
                ->whereDate('date_reservation', $donnees['date_reservation'])
                ->whereIn('statut', self::STATUTS_ACTIFS)
                ->get();

            // Quota journalier.
            if ($activesDuJour->count() >= self::MAX_RESERVATIONS_PAR_JOUR) {
                $erreurs['nombre_personnes'] = 'Vous ne pouvez pas avoir plus de '
                    . self::MAX_RESERVATIONS_PAR_JOUR . ' réservations le même jour.';
            }

            // Chevauchement horaire pour le même e-mail.
            $chevauche = $activesDuJour->first(
                fn (Reservation $r) => $r->heure_debut_courte < $this->hhmm($donnees['heure_fin'])
                    && $r->heure_fin_courte > $this->hhmm($donnees['heure_debut'])
            );

            if ($chevauche !== null) {
                $erreurs['heure_debut'] = 'Vous avez déjà une réservation sur ce créneau ('
                    . $chevauche->heure_debut_courte . ' – ' . $chevauche->heure_fin_courte . ').';
            }
        }

        if (! empty($erreurs)) {
            throw ValidationException::withMessages($erreurs);
        }
    }

    /**
     * Crée une réservation de façon atomique.
     *
     * Le point est verrouillé (lockForUpdate) pendant la transaction afin
     * d'éviter toute surréservation en cas de requêtes simultanées. Si le
     * créneau est complet et que le client accepte la liste d'attente, la
     * réservation est créée avec le statut « liste_attente ».
     *
     * @param array<string, mixed> $donnees
     *
     * @throws ValidationException
     */
    public function creer(array $donnees): Reservation
    {
        $donnees['heure_debut'] = $this->hhmm($donnees['heure_debut']);
        $donnees['heure_fin'] = $this->hhmm($donnees['heure_fin']);

        $this->verifierRegles($donnees);

        $accepteListeAttente = (bool) ($donnees['liste_attente'] ?? false);
        unset($donnees['liste_attente']);

        return DB::transaction(function () use ($donnees, $accepteListeAttente) {
            $point = PointFraicheur::query()
                ->lockForUpdate()
                ->findOrFail($donnees['point_fraicheur_id']);

            $restantes = $this->placesRestantes(
                $point,
                $donnees['date_reservation'],
                $donnees['heure_debut'],
                $donnees['heure_fin']
            );

            if ((int) $donnees['nombre_personnes'] > $restantes) {
                if (! $accepteListeAttente) {
                    throw ValidationException::withMessages([
                        'nombre_personnes' => $restantes > 0
                            ? "Il ne reste que {$restantes} place(s) sur ce créneau."
                            : 'Ce créneau est complet. Vous pouvez néanmoins rejoindre la liste d\'attente.',
                    ]);
                }

                $donnees['statut'] = Reservation::STATUT_LISTE_ATTENTE;
            }

            $donnees['statut'] ??= Reservation::STATUT_EN_ATTENTE;

            return Reservation::create($donnees);
        });
    }

    /**
     * Annule une réservation puis tente de promouvoir la liste d'attente.
     */
    public function annuler(Reservation $reservation): void
    {
        if ($reservation->statut === Reservation::STATUT_ANNULEE) {
            return;
        }

        $reservation->update([
            'statut' => Reservation::STATUT_ANNULEE,
            'annulee_le' => now(),
        ]);

        $this->promouvoirListeAttente($reservation);
    }

    /**
     * Promeut la première réservation en liste d'attente qui peut désormais
     * tenir dans les places libérées par $reservation. Notifie le client.
     */
    public function promouvoirListeAttente(Reservation $reservation): ?Reservation
    {
        $point = $reservation->pointFraicheur;

        if ($point === null) {
            return null;
        }

        $date = $reservation->date_reservation?->toDateString();
        $debut = $reservation->heure_debut_courte;
        $fin = $reservation->heure_fin_courte;

        $restantes = $this->placesRestantes($point, $date, $debut, $fin);

        if ($restantes <= 0) {
            return null;
        }

        $candidat = Reservation::query()
            ->where('point_fraicheur_id', $point->id)
            ->where('statut', Reservation::STATUT_LISTE_ATTENTE)
            ->whereDate('date_reservation', $date)
            ->where('heure_debut', '<', $fin)
            ->where('heure_fin', '>', $debut)
            ->where('nombre_personnes', '<=', $restantes)
            ->orderBy('created_at')
            ->first();

        if ($candidat === null) {
            return null;
        }

        $candidat->update([
            'statut' => Reservation::STATUT_CONFIRMEE,
            'confirmee_le' => now(),
        ]);

        Notification::route('mail', $candidat->email)->notify(new PlaceLiberee($candidat));

        return $candidat;
    }

    /**
     * Normalise une heure au format « HH:MM ».
     */
    private function hhmm(string $heure): string
    {
        return substr($heure, 0, 5);
    }
}
