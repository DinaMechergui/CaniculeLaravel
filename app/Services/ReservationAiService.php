<?php

namespace App\Services;

use App\Models\PointFraicheur;
use App\Models\Reservation;
use Illuminate\Support\Collection;

/**
 * Intelligence artificielle du module « Réservations ».
 *
 * Regroupe toutes les tâches IA autour des réservations :
 *
 *  - analyse des besoins spécifiques (étiquettes `tags_ia` + priorité + explication) ;
 *  - score de risque de non-présentation ;
 *  - assistant de réservation en langage naturel ;
 *  - personnalisation de l'e-mail de confirmation ;
 *  - rédaction assistée d'une réponse à un visiteur.
 *
 * Chaque méthode possède un SECOURS LOCAL déterministe : l'application reste
 * pleinement fonctionnelle même sans clé d'IA ou sans réseau.
 */
class ReservationAiService
{
    /**
     * Étiquettes de besoins reconnues par le module.
     */
    public const TAGS = [
        'mobilite_reduite',
        'enfant_en_bas_age',
        'personne_agee',
        'risque_medical',
        'femme_enceinte',
        'animal_accompagnant',
        'allergie',
        'aucun',
    ];

    /**
     * Hiérarchie des priorités (plus le rang est élevé, plus c'est prioritaire).
     */
    private const RANGS_PRIORITE = [
        Reservation::PRIORITE_NORMALE => 1,
        Reservation::PRIORITE_HAUTE   => 2,
        Reservation::PRIORITE_URGENTE => 3,
    ];

    public function __construct(private readonly AiService $ia)
    {
    }

    /*
    |--------------------------------------------------------------------------
    | Analyse des besoins et priorité
    |--------------------------------------------------------------------------
    */

    /**
     * Analyse complète d'une réservation : étiquettes, priorité, explication
     * et score de risque, puis enregistrement.
     *
     * La priorité n'est jamais abaissée (un ajustement manuel est respecté).
     */
    public function analyser(Reservation $reservation): void
    {
        $analyse = $this->analyserBesoins($reservation);

        $reservation->tags_ia = $analyse['tags'];
        $reservation->explication_ia = $analyse['explication'];
        $reservation->score_risque_no_show = $this->scoreNoShow($reservation);
        $reservation->priorite = $this->prioriteLaPlusHaute($reservation->priorite, $analyse['priorite']);

        $reservation->save();
    }

    /**
     * Détermine les étiquettes, la priorité et une explication à partir des
     * besoins spécifiques (IA si disponible, sinon analyse locale).
     *
     * @return array{tags: list<string>, priorite: string, explication: string}
     */
    public function analyserBesoins(Reservation $reservation): array
    {
        $besoins = trim((string) $reservation->besoins_specifiques);

        if ($besoins === '') {
            return [
                'tags' => ['aucun'],
                'priorite' => Reservation::PRIORITE_NORMALE,
                'explication' => 'Aucun besoin spécifique signalé.',
            ];
        }

        $secours = $this->analyseLocale($besoins);

        $systeme = "Tu es un agent d'accueil dans un point de fraîcheur pendant une canicule en Tunisie. "
            . 'Tu analyses les besoins spécifiques exprimés par un visiteur pour adapter son accueil. '
            . 'Réponds en JSON strict avec les champs « tags » (tableau), « priorite » et « explication ». '
            . 'Valeurs autorisées pour tags : ' . implode(', ', self::TAGS) . '. '
            . 'Valeurs autorisées pour priorite : normale, haute, urgente. '
            . 'utilise « haute » pour une personne âgée, une mobilité réduite, une femme enceinte ou un risque médical, '
            . '« urgente » en cas de danger immédiat (détresse respiratoire, urgence vitale).';

        $prompt = "Besoins spécifiques du visiteur : \"{$besoins}\". "
            . "Nombre de personnes : {$reservation->nombre_personnes}.";

        $reponse = $this->ia->json($systeme, $prompt, $secours);

        $tags = $this->normaliserTags($reponse['tags'] ?? $secours['tags']);

        $priorite = in_array($reponse['priorite'] ?? '', array_keys(self::RANGS_PRIORITE), true)
            ? $reponse['priorite']
            : $secours['priorite'];

        $explication = trim((string) ($reponse['explication'] ?? ''));

        if ($explication === '') {
            $explication = $secours['explication'];
        }

        return [
            'tags' => $tags,
            'priorite' => $priorite,
            'explication' => mb_substr($explication, 0, 500),
        ];
    }

    /**
     * Analyse locale (secours) : détection par mots-clés et priorité associée.
     *
     * @return array{tags: list<string>, priorite: string, explication: string}
     */
    protected function analyseLocale(string $besoins): array
    {
        $texte = mb_strtolower($besoins);
        $tags = [];

        $mots = [
            'mobilite_reduite'    => ['mobilité', 'mobilite', 'fauteuil', 'handicap', 'pmr', 'cannes', 'béquille', 'bequille'],
            'enfant_en_bas_age'   => ['bébé', 'bebe', 'nourrisson', 'poussette', 'jeune enfant'],
            'personne_agee'       => ['âgé', 'agee', 'âgée', 'aîné', 'aine', 'senior', 'personne âgée', 'personne agee'],
            'risque_medical'      => ['diabète', 'diabete', 'cardiaque', 'asthme', 'respiratoire', 'oxygène', 'oxygen', 'insuffisance', 'hypertension', 'fragile'],
            'femme_enceinte'      => ['enceinte', 'grossesse', 'femme enceinte'],
            'animal_accompagnant' => ['animal', 'chien', 'chat'],
            'allergie'            => ['allergie', 'allergique'],
        ];

        foreach ($mots as $tag => $motsCles) {
            foreach ($motsCles as $motCle) {
                if (str_contains($texte, $motCle)) {
                    $tags[] = $tag;
                    break;
                }
            }
        }

        if ($tags === []) {
            return [
                'tags' => ['aucun'],
                'priorite' => Reservation::PRIORITE_NORMALE,
                'explication' => 'Aucun besoin particulier détecté dans le message.',
            ];
        }

        $priorite = Reservation::PRIORITE_NORMALE;

        if (array_intersect($tags, ['risque_medical', 'personne_agee', 'femme_enceinte', 'mobilite_reduite']) !== []) {
            $priorite = Reservation::PRIORITE_HAUTE;
        }

        if (str_contains($texte, 'urgence') || str_contains($texte, 'insuffisance respiratoire')
            || str_contains($texte, 'oxygène') || str_contains($texte, 'detresse') || str_contains($texte, 'détresse')) {
            $priorite = Reservation::PRIORITE_URGENTE;
        }

        $libelles = collect($tags)
            ->map(fn (string $tag): string => str_replace('_', ' ', $tag))
            ->implode(', ');

        return [
            'tags' => $tags,
            'priorite' => $priorite,
            'explication' => "Besoins détectés : {$libelles}. Priorité {$priorite}.",
        ];
    }

    /**
     * Filtre les étiquettes sur le vocabulaire autorisé.
     *
     * @param  mixed  $tags
     * @return list<string>
     */
    protected function normaliserTags(mixed $tags): array
    {
        if (! is_array($tags)) {
            return ['aucun'];
        }

        $propre = array_values(array_unique(array_filter(
            array_map('strval', $tags),
            fn (string $tag): bool => in_array($tag, self::TAGS, true)
        )));

        // « aucun » est exclusif.
        if (count($propre) > 1) {
            $propre = array_values(array_diff($propre, ['aucun']));
        }

        return $propre === [] ? ['aucun'] : $propre;
    }

    /**
     * Retourne la priorité la plus élevée entre l'actuelle et la nouvelle.
     */
    protected function prioriteLaPlusHaute(?string $actuelle, ?string $nouvelle): string
    {
        $actuelle = $actuelle ?? Reservation::PRIORITE_NORMALE;
        $nouvelle = $nouvelle ?? Reservation::PRIORITE_NORMALE;

        $rangActuel = self::RANGS_PRIORITE[$actuelle] ?? 1;
        $rangNouveau = self::RANGS_PRIORITE[$nouvelle] ?? 1;

        return $rangNouveau > $rangActuel ? $nouvelle : $actuelle;
    }

    /*
    |--------------------------------------------------------------------------
    | Score de risque de non-présentation
    |--------------------------------------------------------------------------
    */

    /**
     * Calcule un score de risque de non-présentation entre 0 (fiable) et 100.
     *
     * Heuristique déterministe : historique de non-présentations du même
     * e-mail, adresse jetable, réservation de dernière minute, week-end,
     * groupe important et inscription sur liste d'attente.
     */
    public function scoreNoShow(Reservation $reservation): float
    {
        $score = 20.0;
        $email = mb_strtolower((string) $reservation->email);

        if ($email !== '') {
            $nonPresentations = Reservation::query()
                ->where('email', $reservation->email)
                ->where('statut', Reservation::STATUT_NO_SHOW)
                ->whereKeyNot($reservation->getKey())
                ->count();

            $score += min($nonPresentations * 20, 50);

            $domaine = (string) strrchr($email, '@');

            foreach (['yopmail', 'mailinator', 'tempmail', 'guerrillamail', '10minutemail', 'trashmail', 'jetable'] as $jetable) {
                if (str_contains($domaine, $jetable)) {
                    $score += 20;
                    break;
                }
            }
        }

        if ($reservation->date_reservation !== null) {
            $debut = $reservation->date_reservation->copy()->setTimeFromTimeString($reservation->heure_debut_courte);
            $heures = now()->diffInHours($debut, false);

            if ($heures >= 0 && $heures < 4) {
                $score += 10;
            }

            if ($reservation->date_reservation->isWeekend()) {
                $score += 5;
            }
        }

        if ((int) $reservation->nombre_personnes >= 4) {
            $score += 5;
        }

        if ($reservation->statut === Reservation::STATUT_LISTE_ATTENTE) {
            $score += 10;
        }

        return round(min(max($score, 0.0), 100.0), 2);
    }

    /*
    |--------------------------------------------------------------------------
    | Assistant de réservation en langage naturel
    |--------------------------------------------------------------------------
    */

    /**
     * Interprète une demande en langage naturel et renvoie les champs du
     * formulaire de réservation pré-remplis.
     *
     * @return array{
     *     intention: string,
     *     point_fraicheur_id: int|null,
     *     nombre_personnes: int|null,
     *     date_reservation: string|null,
     *     heure_debut: string|null,
     *     heure_fin: string|null,
     *     message: string
     * }
     */
    public function assistantReservation(string $message): array
    {
        $points = PointFraicheur::query()->get(['id', 'nom', 'adresse']);
        $secours = $this->assistantLocal($message, $points);

        $listePoints = $points->map(
            fn (PointFraicheur $point): string => "id {$point->id} : {$point->nom} ({$point->adresse})"
        )->implode("\n");

        $systeme = "Tu es l'assistant de réservation d'une application de lutte contre la canicule en Tunisie. "
            . "À partir du message d'un visiteur, tu extrais les informations de réservation. "
            . 'Réponds en JSON strict avec les champs : « intention » (reservation ou information), '
            . '« point_fraicheur_id » (entier de la liste, ou null), « nombre_personnes » (entier ou null), '
            . '« date » (AAAA-MM-JJ, ou null), « heure_debut » (HH:MM, ou null), « heure_fin » (HH:MM, ou null) '
            . 'et « reponse » (phrase courte et courtoise en français). '
            . "N'invente jamais de point : utilise uniquement un id de la liste fournie.";

        $prompt = 'Date du jour : ' . now()->toDateString() . ".\n"
            . "Points de fraîcheur disponibles :\n{$listePoints}\n\n"
            . "Message du visiteur : \"{$message}\"";

        $reponse = $this->ia->json($systeme, $prompt, $secours);

        return $this->normaliserAssistant($reponse, $secours, $points);
    }

    /**
     * Analyse locale de secours (sans IA) de la demande en langage naturel.
     *
     * @param  Collection<int, PointFraicheur>  $points
     * @return array<string, mixed>
     */
    protected function assistantLocal(string $message, Collection $points): array
    {
        $texte = mb_strtolower($message);

        $intention = (str_contains($texte, 'reserv') || str_contains($texte, 'réserv')
            || str_contains($texte, 'créneau') || str_contains($texte, 'creneau')
            || str_contains($texte, 'place'))
            ? 'reservation'
            : 'information';

        $personnes = null;
        if (preg_match('/(\d{1,2})\s*(personne|place|adulte|enfant)/u', $texte, $correspondance) === 1) {
            $personnes = (int) $correspondance[1];
        } elseif (preg_match('/(?:nous sommes|on est)\s*(\d{1,2})/u', $texte, $correspondance) === 1) {
            $personnes = (int) $correspondance[1];
        }

        $date = null;
        if (str_contains($texte, 'après-demain') || str_contains($texte, 'apres-demain')) {
            $date = now()->addDays(2)->toDateString();
        } elseif (str_contains($texte, 'demain')) {
            $date = now()->addDay()->toDateString();
        } elseif (str_contains($texte, "aujourd'hui") || str_contains($texte, 'aujourd hui')) {
            $date = now()->toDateString();
        }

        $debut = null;
        $fin = null;

        if (preg_match('/(\d{1,2})\s*(?:h|:)\s*(\d{2})?/u', $texte, $correspondance) === 1) {
            $debut = str_pad((string) (int) $correspondance[1], 2, '0', STR_PAD_LEFT) . ':'
                . str_pad((string) (int) ($correspondance[2] ?? 0), 2, '0', STR_PAD_LEFT);
            $fin = $this->ajouterHeure($debut, 1);
        } elseif (str_contains($texte, 'matin')) {
            $debut = '09:00';
            $fin = '10:00';
        } elseif (str_contains($texte, 'midi')) {
            $debut = '12:00';
            $fin = '13:00';
        } elseif (str_contains($texte, 'après-midi') || str_contains($texte, 'apres-midi') || str_contains($texte, 'apres midi')) {
            $debut = '15:00';
            $fin = '16:00';
        } elseif (str_contains($texte, 'soir')) {
            $debut = '18:00';
            $fin = '19:00';
        }

        $pointId = null;
        foreach ($points as $point) {
            if (str_contains($texte, mb_strtolower($point->nom))) {
                $pointId = $point->id;
                break;
            }
        }

        return [
            'intention' => $intention,
            'point_fraicheur_id' => $pointId,
            'nombre_personnes' => $personnes,
            'date' => $date,
            'heure_debut' => $debut,
            'heure_fin' => $fin,
            'reponse' => $intention === 'reservation'
                ? 'J\'ai préparé votre demande : vérifiez et confirmez les informations.'
                : 'Je peux vous aider à trouver un point de fraîcheur et à réserver un créneau.',
        ];
    }

    /**
     * Valide et normalise la réponse (IA ou secours) pour le formulaire.
     *
     * @param  array<string, mixed>  $reponse
     * @param  array<string, mixed>  $secours
     * @param  Collection<int, PointFraicheur>  $points
     * @return array{
     *     intention: string,
     *     point_fraicheur_id: int|null,
     *     nombre_personnes: int|null,
     *     date_reservation: string|null,
     *     heure_debut: string|null,
     *     heure_fin: string|null,
     *     message: string
     * }
     */
    protected function normaliserAssistant(array $reponse, array $secours, Collection $points): array
    {
        $ids = $points->pluck('id')->all();

        $intention = in_array($reponse['intention'] ?? '', ['reservation', 'information'], true)
            ? $reponse['intention']
            : ($secours['intention'] ?? 'information');

        $pointId = $reponse['point_fraicheur_id'] ?? $secours['point_fraicheur_id'] ?? null;
        $pointId = (is_numeric($pointId) && in_array((int) $pointId, $ids, true)) ? (int) $pointId : null;

        $personnes = $reponse['nombre_personnes'] ?? $secours['nombre_personnes'] ?? null;
        $personnes = (is_numeric($personnes) && (int) $personnes >= 1 && (int) $personnes <= 50)
            ? (int) $personnes
            : null;

        $date = $reponse['date'] ?? $secours['date'] ?? null;
        $date = $this->dateValide(is_string($date) ? $date : null);

        $debut = $this->heureValide($reponse['heure_debut'] ?? $secours['heure_debut'] ?? null);
        $fin = $this->heureValide($reponse['heure_fin'] ?? $secours['heure_fin'] ?? null);

        if ($debut !== null && $fin === null) {
            $fin = $this->ajouterHeure($debut, 1);
        }

        if ($debut !== null && $fin !== null && $fin <= $debut) {
            $fin = $this->ajouterHeure($debut, 1);
        }

        $message = trim((string) ($reponse['reponse'] ?? $secours['reponse'] ?? ''));

        if ($message === '') {
            $message = 'Voici les informations extraites de votre demande.';
        }

        return [
            'intention' => $intention,
            'point_fraicheur_id' => $pointId,
            'nombre_personnes' => $personnes,
            'date_reservation' => $date,
            'heure_debut' => $debut,
            'heure_fin' => $fin,
            'message' => $message,
        ];
    }

    /**
     * Formatte un entier en heure « HH:MM » si valide.
     */
    protected function heureValide(mixed $heure): ?string
    {
        if (! is_string($heure) || preg_match('/^(\d{1,2}):(\d{2})$/', trim($heure), $correspondance) !== 1) {
            return null;
        }

        $h = (int) $correspondance[1];
        $m = (int) $correspondance[2];

        if ($h < 0 || $h > 23 || $m < 0 || $m > 59) {
            return null;
        }

        return sprintf('%02d:%02d', $h, $m);
    }

    /**
     * Ajoute un nombre d'heures à une heure « HH:MM » (borné à 23:59).
     */
    protected function ajouterHeure(string $heure, int $heures): string
    {
        [$h, $m] = array_map('intval', explode(':', $heure));

        return sprintf('%02d:%02d', min($h + $heures, 23), $m);
    }

    /**
     * Valide une date « AAAA-MM-JJ » future ou du jour.
     */
    protected function dateValide(?string $date): ?string
    {
        if ($date === null || preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1) {
            return null;
        }

        return $date >= now()->toDateString() ? $date : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Personnalisation de l'e-mail et réponse assistée
    |--------------------------------------------------------------------------
    */

    /**
     * Phrase personnalisée à insérer dans l'e-mail de confirmation.
     */
    public function personnaliserEmail(Reservation $reservation): string
    {
        $point = $reservation->pointFraicheur?->nom ?? 'point de fraîcheur';
        $date = $reservation->date_reservation?->format('d/m/Y') ?? '';
        $debut = $reservation->heure_debut_courte;
        $fin = $reservation->heure_fin_courte;

        $secours = "Bonjour {$reservation->nom}, votre créneau au point de fraîcheur « {$point} » "
            . "du {$date} de {$debut} à {$fin} est bien réservé. Nous vous y accueillerons avec plaisir.";

        $tags = is_array($reservation->tags_ia) ? implode(', ', $reservation->tags_ia) : 'aucun';
        $systeme = "Tu rédiges une phrase chaleureuse et personnalisée (une à deux phrases, en français) "
            . "pour l'e-mail de confirmation d'une réservation dans un point de fraîcheur pendant une canicule. "
            . 'Ton courtois et rassurant. Réponds uniquement par la phrase.';
        $prompt = "Nom : {$reservation->nom}. Point : {$point}. Date : {$date}. Créneau : {$debut} à {$fin}. "
            . "Besoins détectés : {$tags}.";

        return $this->ia->texte($systeme, $prompt, $secours);
    }

    /**
     * Rédige une réponse assistée qu'un administrateur pourra envoyer au visiteur.
     */
    public function redigerReponse(Reservation $reservation): string
    {
        $point = $reservation->pointFraicheur?->nom ?? 'point de fraîcheur';
        $date = $reservation->date_reservation?->format('d/m/Y') ?? '';
        $statut = $reservation->statut_label;

        $secours = "Bonjour {$reservation->nom},\n\n"
            . "Nous avons bien reçu votre demande concernant le point de fraîcheur « {$point} » "
            . "pour le {$date} ({$statut}).\n\n"
            . "N'hésitez pas à nous préciser toute information utile (heure souhaitée, besoins particuliers) "
            . "et nous vous répondrons dans les plus brefs délais.\n\n"
            . "Cordialement,\nL'équipe Alerte Canicule";

        $systeme = "Tu rédiges un e-mail de réponse courtois et professionnel (en français) "
            . "au nom de l'équipe « Alerte Canicule » d'une application de lutte contre la canicule. "
            . 'Laisse des espaces à compléter par l\'administrateur. Réponds uniquement par le texte de l\'e-mail.';
        $prompt = "Destinataire : {$reservation->nom}. Point de fraîcheur : {$point}. "
            . "Date : {$date}. Statut de la réservation : {$statut}. "
            . "Besoins signalés : " . (string) $reservation->besoins_specifiques . '.';

        return $this->ia->texte($systeme, $prompt, $secours);
    }
}
