<?php

namespace App\Services;

/**
 * Détection simple et déterministe de contenu indésirable (spam).
 *
 * Objectif : bloquer les messages promotionnels ou robots déposés dans les
 * champs libres (besoins spécifiques, assistant), sans dépendre d'une IA ni
 * d'un service externe.
 */
class SpamDetectionService
{
    /**
     * Mots-clés typiques des contenus indésirables.
     *
     * @var list<string>
     */
    private const MOTS_CLES = [
        'viagra', 'cialis', 'casino', 'pornograph', 'porn', 'crypto', 'bitcoin',
        'escort', 'free money', 'click here', 'cliquez ici', 'gagner de l\'argent',
        'seo services', 'blackjack', 'betting', 'paris sportifs',
    ];

    /**
     * Indique si le texte fourni ressemble à du spam.
     */
    public function estSpam(?string $texte): bool
    {
        $texte = trim((string) $texte);

        if ($texte === '') {
            return false;
        }

        $bas = mb_strtolower($texte);

        // Beaucoup de liens.
        if (preg_match_all('#https?://|www\.#i', $bas) >= 2) {
            return true;
        }

        // Balises de forum (souvent utilisées par les robots SEO).
        if (preg_match('#\[url=|\[link=|\[/url\]#i', $texte) === 1) {
            return true;
        }

        // Mots-clés indésirables.
        foreach (self::MOTS_CLES as $motCle) {
            if (str_contains($bas, $motCle)) {
                return true;
            }
        }

        // Excès de majuscules (cri / spam).
        $lettres = preg_replace('/[^a-zA-Z]/', '', $texte) ?? '';
        if (strlen($lettres) > 20) {
            $majuscules = preg_replace('/[^A-Z]/', '', $lettres) ?? '';
            if (strlen($majuscules) / max(strlen($lettres), 1) > 0.6) {
                return true;
            }
        }

        // Caractère répété de manière anormale (ex. « aaaaaaaaa »).
        if (preg_match('/(.)\1{6,}/u', $texte) === 1) {
            return true;
        }

        return false;
    }
}
