<?php

namespace Database\Seeders;

use App\Models\CampagneConseil;
use App\Models\Conseil;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CampagneConseilSeeder extends Seeder
{
    public function run(): void
    {
        $conseils = Conseil::orderBy('id')->get();

        if ($conseils->isEmpty()) {
            return;
        }

        // [titre, public_cible, canal, statut, début (jours), fin (jours ou null)]
        $campagnes = [
            ['Restez hydratés pendant la canicule', 'tous', 'affichage', 'active', -5, 20],
            ['Prenez soin de nos aînés', 'personnes_agees', 'sms', 'active', -3, 15],
            ['Les enfants et la chaleur', 'enfants', 'reseaux_sociaux', 'active', -10, 10],
            ['Familles : organisez une journée au frais', 'familles', 'reseaux_sociaux', 'active', -2, 25],
            ['Économisons l\'énergie cet été', 'tous', 'affichage', 'active', -7, 30],
            ['Que faire en cas de coupure de courant ?', 'tous', 'sms', 'active', -1, null],
            ['Alerte canicule : les bons réflexes', 'tous', 'sms', 'terminee', -60, -30],
            ['Protégez vos appareils pendant les coupures', 'familles', 'affichage', 'terminee', -45, -15],
            ['Rafraîchir la maison sans climatisation', 'familles', 'reseaux_sociaux', 'brouillon', 5, 35],
            ['Aînés isolés : un appel peut tout changer', 'personnes_agees', 'affichage', 'brouillon', 10, 40],
        ];

        foreach ($campagnes as $i => [$titre, $public, $canal, $statut, $debut, $fin]) {
            CampagneConseil::firstOrCreate(
                ['titre' => $titre],
                [
                    'conseil_id' => $conseils[$i % $conseils->count()]->id,
                    'public_cible' => $public,
                    'canal' => $canal,
                    'statut' => $statut,
                    'date_debut' => Carbon::today()->addDays($debut)->toDateString(),
                    'date_fin' => $fin === null ? null : Carbon::today()->addDays($fin)->toDateString(),
                ]
            );
        }
    }
}