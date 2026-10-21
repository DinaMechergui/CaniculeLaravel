<?php

namespace Database\Seeders;

use App\Models\CategorieConseil;
use App\Models\Conseil;
use Illuminate\Database\Seeder;

class ConseilSeeder extends Seeder
{
    public function run(): void
    {
        // Paragraphes « Pour aller plus loin », par catégorie.
        // Un conseil en reçoit 0, 2 ou 3 : cela fait varier sa longueur
        // et donc son temps de lecture (environ 1, 2 ou 3 minutes).
        $approfondissements = [
            'Énergie' => [
                'Pendant une canicule, la demande d\'électricité grimpe en fin d\'après-midi et en début de soirée, quand chacun rentre chez soi, allume la climatisation et prépare le repas. C\'est à ce moment que le réseau est le plus sollicité et que les coupures sont les plus probables. En décalant certains usages, comme la lessive, le repassage ou la recharge des appareils, vers le matin ou tard dans la nuit, vous aidez à aplatir cette courbe de consommation. Ce geste paraît minime, mais multiplié par des centaines de foyers dans un même quartier, il peut éviter une surcharge locale du réseau et il allège aussi votre facture.',
                'Rafraîchir un logement ne demande pas toujours de climatiser. La nuit, ouvrez grand les fenêtres opposées pour créer un courant d\'air et évacuer la chaleur accumulée, puis refermez tout dès le matin avant que l\'air extérieur ne se réchauffe. Accrocher un drap humide devant une fenêtre ouverte, poser un récipient d\'eau fraîche devant un ventilateur ou fermer les portes des pièces inutilisées permet aussi de gagner quelques degrés. Si votre logement dispose d\'une terrasse ou d\'un balcon, des plantes et un store extérieur font une vraie différence, car ils arrêtent le soleil avant qu\'il n\'atteigne la vitre.',
                'Une fois le courant revenu après une coupure, ne rebranchez pas tout en même temps. Laissez d\'abord le réseau se stabiliser quelques minutes, puis remettez les appareils en marche un par un, en commençant par le réfrigérateur et le congélateur. Vérifiez que rien ne dégage d\'odeur de brûlé ou de chaleur anormale. Profitez-en pour recharger vos téléphones, vos lampes et vos batteries externes, au cas où une nouvelle coupure surviendrait dans la journée. Si une coupure se prolonge anormalement ou se répète souvent, signalez-la à votre fournisseur d\'électricité et prévenez vos voisins, notamment les personnes âgées ou malades.',
            ],
            'Hydratation' => [
                'Les besoins en eau varient selon l\'âge, l\'activité et la température, mais on recommande en général au moins un litre et demi d\'eau par jour, et davantage pendant une canicule. Buvez par petites quantités, souvent, plutôt que de grands verres d\'un coup. Une eau fraîche, mais pas glacée, est plus facile à boire en quantité. Un bon repère est la couleur des urines : claires, elles indiquent une bonne hydratation, foncées, elles signalent qu\'il faut boire davantage. Les personnes qui suivent un régime limitant les liquides, ou qui souffrent d\'une maladie du cœur ou des reins, doivent demander conseil à leur médecin.',
                'Certaines personnes sont plus vulnérables à la chaleur : les personnes âgées, les bébés et les jeunes enfants, les femmes enceintes, les personnes atteintes de maladies chroniques et celles qui travaillent dehors. Certains médicaments peuvent aussi aggraver les effets de la chaleur : demandez conseil à votre pharmacien. Prenez des nouvelles de vos proches et de vos voisins isolés au moins une fois par jour. Si quelqu\'un paraît confus, très fatigué ou refuse de boire, mettez-le au frais, faites-le boire par petites gorgées et appelez un médecin ou les secours sans attendre.',
                'Organiser sa journée autour de la chaleur permet d\'éviter bien des malaises. Faites vos courses et vos démarches tôt le matin ou en fin de soirée, restez à l\'intérieur aux heures les plus chaudes et choisissez des vêtements clairs, légers et amples, en coton ou en lin. Une douche tiède ou un linge humide sur la nuque et les poignets rafraîchit rapidement. Si votre logement reste trop chaud, passez quelques heures dans un lieu frais et public, comme une bibliothèque, un centre commercial ou un parc ombragé. Ne laissez jamais un enfant ni un animal dans une voiture à l\'arrêt, même fenêtres entrouvertes.',
            ],
            'Équipements' => [
                'Les appareils électroniques souffrent de la chaleur comme de l\'instabilité du courant. Placez ordinateurs, consoles et box internet dans un endroit aéré, loin du soleil direct, et ne les recouvrez jamais pendant qu\'ils fonctionnent. Une multiprise parasurtenseur de qualité protège contre les pics de tension, mais elle s\'use avec le temps : remplacez-la si elle a déjà subi plusieurs fortes surtensions. En cas d\'orage ou de coupure annoncée, le plus sûr reste de débrancher les appareils les plus précieux, et d\'enregistrer régulièrement vos documents importants pour ne rien perdre.',
                'Pendant une coupure, le réfrigérateur est votre principal souci. Fermé, il garde le froid environ quatre heures, et un congélateur plein jusqu\'à une journée et demie. Un thermomètre à l\'intérieur permet de vérifier que la température reste en dessous de 5 °C. Au-delà de quelques heures sans froid, jetez les produits sensibles comme la viande, le poisson, les produits laitiers et les plats cuisinés. Placer des bouteilles d\'eau congelées dans le réfrigérateur ou le congélateur aide à prolonger le froid et vous servira aussi à vous rafraîchir.',
                'La sécurité passe avant tout lors d\'une coupure. Préférez les lampes de poche et les lampes à piles aux bougies, qui provoquent de nombreux incendies domestiques. Si vous utilisez un groupe électrogène, installez-le à l\'extérieur, loin des portes et des fenêtres, car ses gaz contiennent du monoxyde de carbone, invisible et mortel. Ne le branchez jamais sur une prise du logement sans dispositif adapté, au risque de renvoyer du courant dans le réseau et de blesser un technicien. Évitez de surcharger les rallonges et vérifiez qu\'elles ne chauffent pas.',
            ],
        ];

        // Pour chaque catégorie : [titre, contenu, nombre de paragraphes ajoutés].
        $conseils = [
            'Énergie' => [
                ['Régler la climatisation à 26 °C', 'Un réglage à 26 °C est un bon compromis entre confort et consommation. Chaque degré en moins augmente sensiblement la facture et la charge sur le réseau électrique pendant les pics de chaleur.', 0],
                ['Fermer volets et rideaux en journée', 'Gardez les volets et les rideaux fermés côté soleil pendant la journée. Vous limitez l\'entrée de chaleur et vous réduisez le besoin de climatiser. Aérez ensuite le soir et la nuit, quand l\'air est plus frais.', 2],
                ['Éviter les gros appareils aux heures de pointe', 'Lave-linge, lave-vaisselle et fer à repasser consomment beaucoup. Utilisez-les tôt le matin ou tard le soir, en dehors des heures de forte demande, pour soulager le réseau et éviter les coupures.', 3],
                ['Éteindre les appareils en veille', 'Télévision, console, box internet et chargeurs consomment même éteints. Branchez-les sur une multiprise avec interrupteur et coupez-la quand vous ne les utilisez pas.', 0],
                ['Se préparer à une coupure de courant', 'Gardez une lampe de poche, des piles, une batterie externe chargée et une radio à portée de main. Notez les numéros utiles sur papier, car votre téléphone peut manquer de batterie.', 3],
                ['Pendant une coupure : débrancher les appareils', 'Débranchez les appareils sensibles dès que le courant saute. Au retour, une surtension peut les endommager. Rebranchez-les un par un après quelques minutes, une fois le courant stable.', 2],
                ['Utiliser un ventilateur intelligemment', 'Un ventilateur rafraîchit la peau, pas la pièce : éteignez-le quand vous quittez la pièce. Placez-le près d\'une fenêtre ouverte le soir pour faire entrer l\'air frais.', 0],
                ['Choisir des ampoules LED', 'Les ampoules LED consomment jusqu\'à cinq fois moins que les anciennes et dégagent très peu de chaleur. C\'est un changement simple qui réduit la consommation et la température du logement.', 2],
            ],
            'Hydratation' => [
                ['Boire régulièrement, sans attendre la soif', 'Quand la soif apparaît, le corps est déjà en début de déshydratation. Buvez de l\'eau tout au long de la journée, par petites quantités, surtout pendant les épisodes de forte chaleur.', 3],
                ['Limiter le café, l\'alcool et les boissons sucrées', 'Ces boissons favorisent la perte d\'eau ou n\'hydratent pas bien. Privilégiez l\'eau, éventuellement fraîche mais pas glacée, et les tisanes froides sans sucre.', 0],
                ['Protéger les personnes âgées', 'Les personnes âgées ressentent moins la soif et supportent moins bien la chaleur. Proposez-leur à boire régulièrement, rendez-leur visite ou appelez-les au moins une fois par jour pendant la canicule.', 2],
                ['Protéger les bébés et les jeunes enfants', 'Les enfants se déshydratent vite. Proposez-leur à boire souvent, habillez-les légèrement et ne les laissez jamais seuls dans une voiture, même pour quelques minutes.', 2],
                ['Manger léger et riche en eau', 'Pastèque, melon, concombre, tomate et salades contiennent beaucoup d\'eau. Fractionnez les repas et évitez les plats lourds et gras qui fatiguent l\'organisme par forte chaleur.', 0],
                ['Reconnaître les signes de déshydratation', 'Bouche sèche, urines foncées, maux de tête, vertiges et grande fatigue sont des signaux d\'alerte. Mettez-vous à l\'ombre, buvez par petites gorgées et reposez-vous.', 3],
                ['Que faire en cas de coup de chaleur', 'Fièvre élevée, peau chaude et rouge, confusion ou malaise sont des signes graves. Appelez immédiatement le 190 (SAMU), mettez la personne au frais, humidifiez sa peau et ventilez-la en attendant les secours.', 3],
                ['Adapter ses activités à la chaleur', 'Évitez le sport et les efforts physiques entre 11 h et 16 h. Si vous devez sortir, portez des vêtements clairs et amples, un chapeau, et emportez de l\'eau.', 0],
            ],
            'Équipements' => [
                ['Protéger les appareils sensibles avec un parasurtenseur', 'Ordinateurs, téléviseurs et box se protègent des surtensions avec une multiprise parasurtenseur. Elle absorbe les pics de tension qui suivent souvent le retour du courant.', 2],
                ['Ne pas ouvrir le réfrigérateur pendant une coupure', 'Un réfrigérateur fermé garde le froid plusieurs heures, et un congélateur plein bien plus longtemps. Ouvrez-le le moins possible et jetez les aliments douteux après une longue coupure.', 3],
                ['Entretenir son climatiseur', 'Nettoyez les filtres chaque mois pendant la saison chaude. Un filtre propre améliore le refroidissement, réduit la consommation et évite la diffusion de poussière et de bactéries.', 0],
                ['Recharger ses appareils à l\'avance', 'Quand une canicule ou un orage est annoncé, rechargez téléphones, batteries externes et lampes. Gardez une batterie de secours chargée pour les urgences.', 0],
                ['Utiliser un groupe électrogène en sécurité', 'Placez-le toujours à l\'extérieur, loin des fenêtres : les gaz d\'échappement contiennent du monoxyde de carbone, un gaz mortel. Ne le branchez jamais directement sur le réseau de la maison.', 3],
                ['Éviter les rallonges surchargées', 'Plusieurs appareils puissants sur une même prise ou une rallonge peuvent faire chauffer le câble et provoquer un incendie. Répartissez les appareils sur plusieurs prises.', 2],
                ['Ne pas laisser d\'appareils dans une voiture chaude', 'Téléphones, batteries et bouteilles de gaz ne supportent pas la chaleur d\'un véhicule garé au soleil. Ils peuvent se détériorer, voire prendre feu ou exploser.', 0],
                ['Préparer une trousse de secours pour les coupures', 'Rassemblez dans une boîte une lampe, des piles, une radio, de l\'eau, des bougies avec allumettes, un chargeur externe et vos papiers importants, pour la retrouver facilement dans le noir.', 2],
            ],
        ];

        foreach ($conseils as $nomCategorie => $liste) {
            $categorie = CategorieConseil::where('nom', $nomCategorie)->first();

            foreach ($liste as [$titre, $contenu, $nbParagraphes]) {
                // On ajoute 0, 2 ou 3 paragraphes pour varier la longueur.
                if ($nbParagraphes > 0) {
                    $extras = array_slice($approfondissements[$nomCategorie], 0, $nbParagraphes);
                    $contenu .= "\n\n" . implode("\n\n", $extras);
                }

                Conseil::firstOrCreate(
                    [
                        'categorie_conseil_id' => $categorie->id,
                        'titre' => $titre,
                    ],
                    ['contenu' => $contenu]
                );
            }
        }
    }
}
