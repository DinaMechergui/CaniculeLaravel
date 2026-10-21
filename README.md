# Alerte Canicule — plateforme d'alertes (canicule & coupures de courant)

Application Laravel 12 (Blade, Breeze) en français pour diffuser les alertes de canicule
et les coupures de courant, avec un front office public et un back office réservé aux
administrateurs.

- **Front office** : template `VaultEdge` (Bootstrap 4.1.3).
- **Back office** : template `Sneat` (Bootstrap 5).
- **Module « Points de fraîcheur »** : `PointFraicheur` lié à `TypePoint` (consultation
  publique + CRUD back office).

## Prérequis

- PHP >= 8.2 (ex. XAMPP : `C:\xampp\php`)
- Composer 2.x
- MySQL 8 (ou MariaDB)
- Node.js >= 18 (uniquement pour reconstruire les assets, non requis au démarrage)

## Installation

```bash
composer install
cp .env.example .env        # sous Windows : Copy-Item .env.example .env
php artisan key:generate
```

1. Créer la base de données `alerte_canicule` (UTF-8).
2. Ajuster `DB_*` dans `.env` si nécessaire (hôte, utilisateur, mot de passe).
3. Lancer le schéma et les données de démonstration :

```bash
php artisan migrate:fresh --seed
```

4. Démarrer le serveur :

```bash
php artisan serve           # http://127.0.0.1:8000
```

Optionnel (reconstruction des assets, sans usage dans les layouts) :

```bash
npm install
npm run build
```

## Comptes créés par le seed

| Rôle           | E-mail              | Mot de passe |
|----------------|---------------------|--------------|
| Administrateur | `admin@example.com` | `password`   |
| Utilisateur    | `user@example.com`  | `password`   |

10 utilisateurs supplémentaires sont générés (données de démonstration).

Le module « Points de fraîcheur » est rempli avec **30 lieux réels de la
Grande Tunis** (11 parcs, 12 salles climatisées, 7 plages) relevés sur
OpenStreetMap (coordonnées et adresse via géocodage inverse Nominatim,
horaires lorsqu'ils sont renseignés sur OSM) : ceci rend le tri par distance
de la carte « Près de moi » représentatif de la réalité.

---

## Assistant IA (chatbot)

Route publique `GET /assistant` (entrée « Assistant IA » du menu) et point
d'entrée `POST /assistant` qui renvoie `{ "reponse": "…" }` en JSON.

- **Fournisseur** : OpenRouter (`/api/v1/chat/completions`), modèle
  `openai/gpt-4o-mini` par défaut, surchargeable via `OPENROUTER_MODEL`.
- **Configuration** : la clé n'est lue que dans `.env` (`OPENROUTER_API_KEY`),
  fichier ignoré par git ; seule la variable vide est présente dans
  `.env.example` — la clé n'est **jamais** commitée.
- **Contexte (RAG minimal)** : chaque requête envoie au modèle la liste des 30
  points (nom, type, adresse, coordonnées, horaires, accessibilité) issues de
  la base via Eloquent : les réponses s'appuient sur des données réelles.
- **Sécurité** : message validé (2 à 1000 caractères), historique limité à 12
  échanges, réponses insérées en `textContent` (aucune HTMLisation), messages
  d'erreur français (503 clé manquante, 502 panne du fournisseur).
- **Tests** : `tests/Feature/AssistantIaTest.php` (5 tests avec `Http::fake`).

---

## Arborescence (front office / back office séparés)

```
C:\laravel project\
│
├── app/                                    CODE PHP — 2 espaces distincts
│   ├── BackOffice/                         ══ BACK OFFICE (réservé aux admins)
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php     /admin — statistiques & activité
│   │   │   ├── UserController.php          CRUD /admin/users
│   │   │   ├── TypePointController.php     CRUD /admin/types-point
│   │   │   └── PointFraicheurController.php CRUD /admin/points-fraicheur
│   │   ├── Middleware/
│   │   │   └── AdminMiddleware.php         alias « admin » → 403 si non-admin
│   │   └── Requests/
│   │       ├── StoreUserRequest.php        validation création
│   │       ├── UpdateUserRequest.php       validation modification
│   │       ├── StoreTypePointRequest.php   validation création type
│   │       ├── UpdateTypePointRequest.php  validation modification type
│   │       ├── StorePointFraicheurRequest.php  validation création point
│   │       └── UpdatePointFraicheurRequest.php validation modification point
│   │
│   ├── FrontOffice/                        ══ FRONT OFFICE (public + espace user)
│   │   ├── Controllers/
│   │   │   ├── Auth/                       connexion, inscription, mots de passe
│   │   │   │   ├── AuthenticatedSessionController.php
│   │   │   │   ├── ConfirmablePasswordController.php
│   │   │   │   ├── EmailVerificationNotificationController.php
│   │   │   │   ├── EmailVerificationPromptController.php
│   │   │   │   ├── NewPasswordController.php
│   │   │   │   ├── PasswordController.php
│   │   │   │   ├── PasswordResetLinkController.php
│   │   │   │   ├── RegisteredUserController.php
│   │   │   │   └── VerifyEmailController.php
│   │   │   ├── HomeController.php          page d'accueil /
│   │   │   ├── AssistantController.php     assistant IA /assistant (chatbot)
│   │   │   ├── PointFraicheurController.php /points-fraicheur (public)
│   │   │   └── ProfileController.php       /profile
│   │   └── Requests/
│   │       ├── Auth/LoginRequest.php       validation connexion
│   │       └── ProfileUpdateRequest.php    validation profil
│   │
│   ├── Http/Controllers/Controller.php     contrôleur de base PARTAGÉ
│   ├── Models/                             modèles PARTAGÉS
│   │   ├── User.php                        rôle admin/user
│   │   ├── TypePoint.php                   type de point (1-N points)
│   │   └── PointFraicheur.php              point de fraîcheur (N-1 type)
│   ├── Providers/AppServiceProvider.php
│   └── Support/AuthRedirect.php            redirections post-login PARTAGÉES
│
├── bootstrap/app.php                       alias middleware « admin »
│
├── routes/                                 ROUTES — 1 fichier par espace
│   ├── web.php                             point d'entrée : charge front + back
│   ├── front.php                           FRONT : /, /profile, module, auth
│   ├── auth.php                            FRONT : login, register, mdp, vérif.
│   ├── back.php                            BACK : /admin, /admin/users, module
│   ├── console.php
│   └── channels.php
│
├── resources/views/
│   ├── front/                              ══ VUES DU FRONT OFFICE
│   │   ├── layouts/front.blade.php         gabarit général (sans @vite)
│   │   ├── partials/
│   │   │   ├── header.blade.php            barre de navigation
│   │   │   ├── footer.blade.php
│   │   │   └── flash-messages.blade.php
│   │   ├── pages/
│   │   │   └── home.blade.php              page d'accueil
│   │   ├── auth/
│   │   │   ├── login.blade.php
│   │   │   ├── register.blade.php
│   │   │   ├── forgot-password.blade.php
│   │   │   ├── reset-password.blade.php
│   │   │   ├── verify-email.blade.php
│   │   │   └── confirm-password.blade.php
│   │   ├── profile/
│   │   │   ├── edit.blade.php
│   │   │   └── partials/
│   │   │       ├── update-profile-information-form.blade.php
│   │   │       ├── update-password-form.blade.php
│   │   │       └── delete-user-form.blade.php
│   │   ├── assistant/
│   │   │   └── index.blade.php             chatbot IA (OpenRouter)
│   │   └── points-fraicheur/               MODULE : pages publiques
│   │       ├── index.blade.php             liste + filtre type + recherche
│   │       ├── carte.blade.php             carte Leaflet + « Près de moi »
│   │       └── show.blade.php              détail + lien OpenStreetMap
│   │
│   ├── back/                               ══ VUES DU BACK OFFICE
│   │   ├── layouts/back.blade.php          gabarit général (sans @vite)
│   │   ├── partials/
│   │   │   ├── sidebar.blade.php           menu latéral
│   │   │   ├── topbar.blade.php            barre supérieure
│   │   │   ├── flash-messages.blade.php
│   │   │   └── footer.blade.php
│   │   ├── pages/
│   │   │   ├── dashboard.blade.php         tableau de bord
│   │   │   └── users/
│   │   │       ├── index.blade.php         liste + recherche
│   │   │       ├── create.blade.php
│   │   │       ├── edit.blade.php
│   │   │       └── show.blade.php
│   │   ├── types-point/                    MODULE : CRUD des types
│   │   │   ├── _form.blade.php             formulaire partagé
│   │   │   ├── index.blade.php             liste + recherche
│   │   │   ├── create.blade.php
│   │   │   ├── edit.blade.php
│   │   │   └── show.blade.php              détail + points rattachés
│   │   └── points-fraicheur/               MODULE : CRUD des points
│   │       ├── _form.blade.php             formulaire partagé
│   │       ├── index.blade.php             liste + recherche
│   │       ├── create.blade.php
│   │       ├── edit.blade.php
│   │       └── show.blade.php              détail + repère cartographique
│   │
│   ├── components/                         COMPOSANTS PARTAGÉS (front + back)
│   │   ├── alert.blade.php
│   │   ├── card.blade.php
│   │   ├── form-input.blade.php
│   │   ├── form-select.blade.php
│   │   ├── page-header.blade.php
│   │   └── pagination.blade.php
│   │
│   └── errors/                             ERREURS PARTAGÉES (résolues par Laravel)
│       ├── 403.blade.php
│       ├── 404.blade.php
│       └── 500.blade.php
│
├── public/
│   ├── assets/front/                       templates front (VaultEdge) : css, js, img, fonts
│   │   └── vendor/leaflet/                 Leaflet 1.9.4 en local (pas de CDN)
│   ├── assets/back/                        templates back (Sneat) : css, js, img, vendor
│   └── build/                              sortie Vite (non utilisée par les layouts)
│
├── lang/fr/                                traductions françaises (laravel-lang)
│   ├── fr.json
│   └── {auth,pagination,passwords,validation,...}.php
│
├── database/
│   ├── migrations/                         schéma (+ rôle, type_points, points_fraicheur)
│   ├── factories/
│   │   ├── UserFactory.php                 états user / admin
│   │   ├── TypePointFactory.php
│   │   └── PointFraicheurFactory.php
│   └── seeders/
│       ├── DatabaseSeeder.php              comptes de démonstration
│       ├── TypePointSeeder.php             3 types (Parc, Salle climatisée, Plage)
│       └── PointFraicheurSeeder.php        30 lieux réels de Grande Tunis (OSM)
│
├── tests/
│   ├── Feature/
│   │   ├── AdminAccessTest.php             403 back office, accès admin
│   │   ├── AssistantIaTest.php             assistant IA (chatbot, Http::fake)
│   │   ├── PagesRenderTest.php             rendu de toutes les pages
│   │   ├── PointsFraicheurTest.php         module points de fraîcheur
│   │   ├── ProfileTest.php
│   │   └── Auth/{Authentication,Registration,EmailVerification,...}Test.php
│   └── Unit/
│
├── .env / .env.example                     fr, Europe/Paris, MySQL alerte_canicule
├── phpunit.xml                             tests en SQLite mémoire
├── vite.config.js, package.json
└── README.md
```

### Conventions de nommage

| Élément            | Front office                  | Back office                   |
|--------------------|-------------------------------|-------------------------------|
| Namespace PHP      | `App\FrontOffice\...`         | `App\BackOffice\...`          |
| Fichier de routes  | `routes/front.php` (+ `auth.php`) | `routes/back.php`         |
| Préfixe de route   | `front.*`, `login`, `profile.*` | `admin.*` (URI `/admin`)   |
| Vues               | `resources/views/front/*`     | `resources/views/back/*`      |
| Layout             | `front.layouts.front`         | `back.layouts.back`           |
| Assets             | `public/assets/front/`        | `public/assets/back/`         |

Éléments volontairement partagés : `app/Models`, `app/Support/AuthRedirect.php`,
`app/Http/Controllers/Controller.php`, `resources/views/components/`,
`resources/views/errors/` (Laravel résout `errors.403` à cet emplacement).

---

## Routes principales

| Méthode | URI                   | Nom               | Fichier          | Description                |
|---------|-----------------------|-------------------|------------------|----------------------------|
| GET     | `/`                   | `front.home`      | `routes/front.php` | Accueil (public)         |
| GET     | `/login`, `/register` | `login`,`register`| `routes/auth.php`  | Authentification (public) |
| GET     | `/profile`            | `profile.edit`    | `routes/front.php` | Profil (connecté)         |
| GET     | `/points-fraicheur`   | `points-fraicheur.index` | `routes/front.php` | Onglets liste/carte intégrés (public) |
| GET     | `/points-fraicheur?vue=carte` | `points-fraicheur.index` | `routes/front.php` | Ouvre la même page en mode carte |
| GET     | `/points-fraicheur/carte` | `points-fraicheur.carte` | `routes/front.php` | Carte plein écran (public) |
| GET     | `/points-fraicheur/proches` | `points-fraicheur.proches` | `routes/front.php` | Points proches, JSON (public) |
| GET     | `/points-fraicheur/{pointFraicheur}` | `points-fraicheur.show` | `routes/front.php` | Détail du point (public) |
| GET     | `/admin`              | `admin.dashboard` | `routes/back.php`  | Tableau de bord (admin)   |
| *       | `/admin/users`        | `admin.users.*`   | `routes/back.php`  | CRUD utilisateurs (admin) |
| *       | `/admin/types-point`  | `admin.types-point.*` | `routes/back.php`  | CRUD types de point (admin) |
| *       | `/admin/points-fraicheur` | `admin.points-fraicheur.*` | `routes/back.php` | CRUD points (admin) |

- Le middleware `admin` (`app/BackOffice/Middleware/AdminMiddleware.php`, alias déclaré
  dans `bootstrap/app.php`) renvoie **403** aux non-administrateurs.
- Après connexion, un administrateur est dirigé vers `/admin`, un utilisateur vers `/`
  (voir `app/Support/AuthRedirect.php`).

## SmartGrid AI — cartographie des coupures

- Back office : `/admin/smartgrid` (route `admin.smartgrid.index`, accès administrateur).
- Carte publique : `/coupures/carte`. Les coupures terminées, rapports, coordonnées
  des équipes et informations d’adresse/descriptives privées ne sont pas exposés.
- Les marqueurs sont affichés uniquement lorsque les coordonnées exactes ont été
  saisies et validées. Aucun centre de quartier n’est utilisé pour placer une panne.
- Les coordonnées facultatives des quartiers, coupures et impacts sont ajoutées
  par migrations additives. Appliquer les migrations avec `php artisan migrate`;
  aucune réinitialisation de base n’est nécessaire.
- Leaflet, son module de regroupement et les tuiles OpenStreetMap sont chargés
  depuis leurs services externes. Les cartes affichent l’attribution OpenStreetMap.
- Pour les analyses Gemini, configurer `GEMINI_API_KEY` (modèle facultatif dans
  `GEMINI_MODEL`). Pour les itinéraires routiers et leur matrice de durées,
  configurer `OPENROUTESERVICE_API_KEY` (et facultativement
  `OPENROUTESERVICE_BASE_URL`). Les clés restent côté serveur. Sans elles, le
  tableau de bord et les scores déterministes restent disponibles, tandis que
  l’IA et le routage affichent leur état non configuré.
- Le score de priorité est déterministe : gravité maximale (0–40), personnes
  affectées (0–25), présence d’un établissement sensible (école ou hôpital,
  0–15), durée écoulée (0–15) et absence d’intervention active (0–5). Une coupure terminée reçoit le score 0. Le trajet
  classe d’abord les priorités, puis minimise le temps routier entre priorités
  égales via OpenRouteService. L’ordre et la recommandation sont indicatifs et
  doivent être validés par un administrateur.
- Vérification : `php artisan route:list` et `php artisan test`. Les tests utilisent
  SQLite en mémoire et des réponses HTTP simulées pour les fournisseurs externes.

## Valeur ajoutée du module Points de fraîcheur

**Explorateur à onglets : liste OU carte, sur la même page.**
La page `/points-fraicheur` propose deux onglets — **Liste** et **Carte** —
qui n'affichent qu'une seule vue à la fois, **sur tous les écrans (desktop
compris)**. L'onglet Carte affiche la carte Leaflet + OpenStreetMap en pleine
largeur (fichiers Leaflet servis **en local** dans
`public/assets/front/vendor/leaflet/`, aucune clé API, seules les tuiles
proviennent d'Internet). La carte reçoit **tous les points filtrés** (sans
pagination) pendant que la liste reste paginée. Au clic sur un marqueur, une
fenêtre affiche le nom, le type, l'adresse, les horaires, le badge
« Accessible » ainsi que deux liens : « Détails » (page du point) et
« Itinéraire » (OSRM à pied, nouvel onglet).

- **Synchronisation liste ↔ carte** : chaque fiche porte un bouton
  **« Localiser »** qui bascule vers l'onglet Carte, recentre (zoom 16), ouvre
  la fenêtre du marqueur et met la fiche en surbrillance ; inversement,
  cliquer un marqueur surligne la fiche correspondante (visible au retour sur
  l'onglet Liste).
- **Onglets Liste / Carte** : un seul onglet visible à la fois (liste en
  grille 3 colonnes, carte en pleine largeur) ; l'état est conservé dans
  l'URL (`?vue=carte`) et exposé en sémantique d'onglets (`role="tablist"`,
  `aria-selected`). Le menu front ne comporte que « Points de fraîcheur » :
  l'onglet Carte se trouve dans la page.
- Bouton **« Près de moi »** : le navigateur fournit la position, la carte se
  centre dessus (marqueur « Vous êtes ici ») et **les distances en km
  (1 décimale) s'ajoutent dans les fenêtres des marqueurs**. Refus de la
  permission / position indisponible / délai dépassé : message en français
  affiché dans la page (jamais d'`alert()`).
- Bouton **« Voir sur la carte »** sur la page détail →
  `/points-fraicheur?point={id}` ouvre l'onglet Carte centré sur ce point
  (zoom 16). La route `/points-fraicheur/carte` reste disponible en
  **plein écran** (bouton « Plein écran » de l'onglet Carte).
- **Les filtres (recherche + type) pilotent la liste et la carte** : la carte
  est construite avec les mêmes critères que la liste paginée.
- Côté serveur, le scope `PointFraicheur::lesPlusProches()` calcule la distance
  en SQL avec la **formule de Haversine** (rayon 6371 km, bindings SQL, protection
  `LEAST(1, GREATEST(-1, ...))` autour de `acos()`).

| Méthode | URI                                      | Nom                          | Description                                      |
|---------|------------------------------------------|------------------------------|--------------------------------------------------|
| GET     | `/points-fraicheur`                      | `points-fraicheur.index`     | Onglets liste/carte intégrés, filtres, `?vue=carte`, `?point={id}` (public) |
| GET     | `/points-fraicheur/carte`                | `points-fraicheur.carte`     | Carte Leaflet plein écran, filtre, géolocalisation (public)  |
| GET     | `/points-fraicheur/proches?lat=&lng=&limit=` | `points-fraicheur.proches` | JSON des points triés par distance (public, 422 si coordonnées invalides) |

**Scénario de démonstration en 5 étapes**

1. L'habitant ouvre **Points de fraîcheur** puis l'onglet **Carte** : les 30
   points s'affichent en pleine largeur sur la carte intégrée.
2. Il choisit **Type de point → Plage** puis **Filtrer** : la liste et la carte
   n'affichent plus que les plages de la côte.
3. Il clique sur **Près de moi** et autorise la géolocalisation : la carte se
   centre sur sa position (marqueur « Vous êtes ici ») et les distances en km
   apparaissent dans les fenêtres des marqueurs.
4. Il clique sur **Localiser** d'une fiche (ou sur un marqueur) : la carte se
   recentre (zoom 16), la fenêtre s'ouvre et la fiche est surlignée ; il clique
   sur **Détails** pour voir la fiche complète du point.
5. Sur la fiche, il clique sur **Itinéraire** : OpenStreetMap s'ouvre dans un
   nouvel onglet avec l'itinéraire à pied jusqu'au point de fraîcheur.

## Points d'attention

- **Pas de `@vite`** dans les layouts : CSS/JS chargés depuis `public/assets/...`
  (aucun lien en dur, toujours `asset()` / `route()`).
- Langue `fr`, fuseau `Europe/Paris`, données de démo `fr_FR`.
- **Module « Points de fraîcheur »** : `TypePoint` (1) — `PointFraicheur` (N) ;
  la suppression d'un type encore rattaché à des points est refusée par le contrôleur.
  Les styles du front sont ajoutés dans `public/assets/front/css/app-custom.css`
  (section « Points de fraîcheur »), jamais dans les fichiers du template.

## Tests

```bash
php artisan test
```

56 tests (188 assertions) — SQLite en mémoire (`phpunit.xml`).
`tests/Feature/PointsFraicheurTest.php` couvre la consultation publique, le filtre
par type, la recherche, la pagination, la carte intégrée à la page de liste,
les accès (invité / utilisateur / admin), la validation et le refus de
suppression d'un type avec points.
`tests/Feature/PointsFraicheurCarteTest.php` couvre la page carte, le tri par
distance de la route `proches` (Haversine) et la réponse 422 des coordonnées
invalides.

Données de démonstration du module :

```bash
php artisan migrate:fresh --seed   # 30 points réels (11 parcs, 12 salles climatisées, 7 plages)
```
