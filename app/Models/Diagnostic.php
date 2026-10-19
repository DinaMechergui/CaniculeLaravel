<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Diagnostic d'une panne / coupure signalée.
 *
 * Contient les symptômes déclarés, les prédictions du modèle IA
 * et la cause réelle confirmée par l'administrateur.
 */
class Diagnostic extends Model
{
    /** @use HasFactory<\Database\Factories\DiagnosticFactory> */
    use HasFactory;

    /**
     * Nom de la table dans la base de données.
     */
    protected $table = 'diagnostics';

    /**
     * Zones touchées par la panne.
     */
    public const ZONES = [
        'logement' => 'Mon logement uniquement',
        'immeuble' => 'Tout l\'immeuble',
        'rue'      => 'Toute la rue',
        'quartier' => 'Tout le quartier',
    ];

    /**
     * Causes possibles (prédites par l'IA ou confirmées par l'admin).
     */
    public const CAUSES = [
        'disjoncteur_domestique' => 'Disjoncteur domestique',
        'surcharge_reseau'       => 'Surcharge du réseau',
        'panne_transformateur'   => 'Panne de transformateur',
        'cable_endommage'        => 'Câble endommagé',
        'delestage_planifie'     => 'Délestage planifié',
        'panne_equipement'       => 'Panne d\'équipement',
    ];

    /**
     * Niveaux de danger (prédits par l'IA).
     */
    public const DANGERS = [
        'faible' => 'Faible',
        'moyen'  => 'Moyen',
        'eleve'  => 'Élevé',
    ];

    /**
     * Symptômes booléens et leur libellé (utilisé par les formulaires et les vues).
     */
    public const SYMPTOMES = [
        'disjoncteur_saute'     => 'Le disjoncteur a sauté',
        'baisse_tension_avant'  => 'Les lumières faiblissaient avant la panne',
        'odeur_brule'           => 'Odeur de brûlé ou bruit anormal',
        'etincelles'            => 'Étincelles ou câbles tombés',
        'recurrent'             => 'La panne se répète souvent',
        'personnes_vulnerables' => 'Personnes vulnérables présentes (âgées, bébés, malades)',
    ];

    /**
     * Les colonnes IA (cause_predite, confiance, niveau_danger) ne sont pas
     * remplissables en masse : seul le modèle IA doit les écrire.
     */
    protected $fillable = [
        'signalement_id',
        'zone_touchee',
        'disjoncteur_saute',
        'baisse_tension_avant',
        'odeur_brule',
        'etincelles',
        'recurrent',
        'personnes_vulnerables',
        'heure_panne',
        'temperature',
        'cause_reelle',
        'remarque',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'disjoncteur_saute'     => 'boolean',
            'baisse_tension_avant'  => 'boolean',
            'odeur_brule'           => 'boolean',
            'etincelles'            => 'boolean',
            'recurrent'             => 'boolean',
            'personnes_vulnerables' => 'boolean',
            'heure_panne'           => 'string',
            'temperature'           => 'float',
            'confiance'             => 'float',
        ];
    }

    /**
     * Signalement diagnostiqué (1:1 inverse).
     */
    public function signalement(): BelongsTo
    {
        return $this->belongsTo(Signalement::class);
    }

    /**
     * Indique si le modèle IA a déjà analysé ce diagnostic.
     */
    public function estAnalyse(): bool
    {
        return $this->cause_predite !== null;
    }
}
