<?php

namespace App\Http\Controllers;

use App\Models\Alerte;
use App\Models\BulletinAlerte;
use App\Models\Coupure;
use App\Models\Diagnostic;
use App\Models\Intervention;
use App\Models\PointFraicheur;
use App\Models\Quartier;
use App\Models\Reservation;
use App\Models\Signalement;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MetricsController extends Controller
{
    /**
     * Exporte les métriques système et métier au format Prometheus OpenMetrics
     */
    public function __invoke(): Response
    {
        $lines = [];

        // 1. Informations sur l'application Laravel
        $lines[] = '# HELP laravel_up État de fonctionnement de l\'application Laravel (1 = OK)';
        $lines[] = '# TYPE laravel_up gauge';
        $lines[] = 'laravel_up 1';

        $lines[] = '# HELP laravel_app_info Métadonnées de l\'application DevSpark';
        $lines[] = '# TYPE laravel_app_info gauge';
        $appName = addslashes(config('app.name', 'DevSpark'));
        $appEnv = addslashes(config('app.env', 'production'));
        $phpVersion = PHP_VERSION;
        $laravelVersion = app()->version();
        $lines[] = "laravel_app_info{app=\"{$appName}\",env=\"{$appEnv}\",php_version=\"{$phpVersion}\",laravel_version=\"{$laravelVersion}\"} 1";

        // 2. Mémoire PHP
        $lines[] = '# HELP php_memory_usage_bytes Mémoire PHP actuellement allouée en octets';
        $lines[] = '# TYPE php_memory_usage_bytes gauge';
        $lines[] = 'php_memory_usage_bytes ' . memory_get_usage(true);

        $lines[] = '# HELP php_memory_peak_bytes Pic de mémoire PHP allouée en octets';
        $lines[] = '# TYPE php_memory_peak_bytes gauge';
        $lines[] = 'php_memory_peak_bytes ' . memory_get_peak_usage(true);

        // 3. État et latence de la base de données
        $dbUp = 0;
        $dbDuration = 0.0;
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $dbDuration = round((microtime(true) - $start) * 1000, 2); // ms
            $dbUp = 1;
        } catch (\Throwable $e) {
            $dbUp = 0;
        }

        $lines[] = '# HELP devspark_database_up État de la connexion MySQL (1 = connecté, 0 = erreur)';
        $lines[] = '# TYPE devspark_database_up gauge';
        $lines[] = "devspark_database_up {$dbUp}";

        $lines[] = '# HELP devspark_database_ping_milliseconds Temps de réponse du ping MySQL en millisecondes';
        $lines[] = '# TYPE devspark_database_ping_milliseconds gauge';
        $lines[] = "devspark_database_ping_milliseconds {$dbDuration}";

        // 4. Métriques Métier DevSpark (si DB accessible)
        if ($dbUp === 1) {
            try {
                // Utilisateurs
                $lines[] = '# HELP devspark_users_total Nombre total d\'utilisateurs par rôle';
                $lines[] = '# TYPE devspark_users_total gauge';
                $usersByRole = User::query()
                    ->select('role', DB::raw('count(*) as total'))
                    ->groupBy('role')
                    ->pluck('total', 'role');
                foreach (['admin', 'user'] as $role) {
                    $cnt = $usersByRole->get($role, 0);
                    $lines[] = "devspark_users_total{role=\"{$role}\"} {$cnt}";
                }

                // Réservations par statut
                $lines[] = '# HELP devspark_reservations_total Nombre de réservations par statut';
                $lines[] = '# TYPE devspark_reservations_total gauge';
                $resasByStatut = Reservation::query()
                    ->select('statut', DB::raw('count(*) as total'))
                    ->groupBy('statut')
                    ->pluck('total', 'statut');

                $statuts = ['confirmee', 'en_attente', 'terminee', 'no_show', 'annulee', 'expiree', 'liste_attente'];
                foreach ($statuts as $statut) {
                    $cnt = $resasByStatut->get($statut, 0);
                    $lines[] = "devspark_reservations_total{statut=\"{$statut}\"} {$cnt}";
                }

                // Réservations aujourd'hui
                $lines[] = '# HELP devspark_reservations_today_total Réservations enregistrées pour la date courante';
                $lines[] = '# TYPE devspark_reservations_today_total gauge';
                $todayCount = Reservation::whereDate('date_reservation', now()->toDateString())->count();
                $lines[] = "devspark_reservations_today_total {$todayCount}";

                // Score de risque moyen IA
                $lines[] = '# HELP devspark_reservations_risk_score_average Score moyen de risque no-show calculé par l\'IA';
                $lines[] = '# TYPE devspark_reservations_risk_score_average gauge';
                $avgScore = Reservation::whereNotNull('score_risque_no_show')->avg('score_risque_no_show') ?? 0;
                $lines[] = 'devspark_reservations_risk_score_average ' . round((float) $avgScore, 2);

                // Points de fraîcheur
                $lines[] = '# HELP devspark_points_fraicheur_total Nombre de points de fraîcheur répertoriés';
                $lines[] = '# TYPE devspark_points_fraicheur_total gauge';
                $lines[] = 'devspark_points_fraicheur_total ' . PointFraicheur::count();

                $lines[] = '# HELP devspark_points_capacite_total Capacité d\'accueil totale des points de fraîcheur';
                $lines[] = '# TYPE devspark_points_capacite_total gauge';
                $lines[] = 'devspark_points_capacite_total ' . (PointFraicheur::sum('capacite') ?? 0);

                // Alertes Canicule
                $lines[] = '# HELP devspark_alertes_total Nombre d\'alertes canicule par statut';
                $lines[] = '# TYPE devspark_alertes_total gauge';
                $alertesByStatut = Alerte::query()
                    ->select('statut', DB::raw('count(*) as total'))
                    ->groupBy('statut')
                    ->pluck('total', 'statut');
                foreach (['brouillon', 'active', 'archivee'] as $statut) {
                    $cnt = $alertesByStatut->get($statut, 0);
                    $lines[] = "devspark_alertes_total{statut=\"{$statut}\"} {$cnt}";
                }

                // Bulletins de suivi
                $lines[] = '# HELP devspark_bulletins_total Nombre de bulletins de suivi météo';
                $lines[] = '# TYPE devspark_bulletins_total gauge';
                $lines[] = 'devspark_bulletins_total ' . BulletinAlerte::count();

                // Coupures d'électricité
                $lines[] = '# HELP devspark_coupures_total Nombre de coupures de courant enregistrées';
                $lines[] = '# TYPE devspark_coupures_total gauge';
                $lines[] = 'devspark_coupures_total ' . Coupure::count();

                // Interventions
                $lines[] = '# HELP devspark_interventions_total Nombre d\'interventions techniques';
                $lines[] = '# TYPE devspark_interventions_total gauge';
                $lines[] = 'devspark_interventions_total ' . Intervention::count();

                // Quartiers
                $lines[] = '# HELP devspark_quartiers_total Nombre de quartiers surveillés';
                $lines[] = '# TYPE devspark_quartiers_total gauge';
                $lines[] = 'devspark_quartiers_total ' . Quartier::count();

                // Signalements & Diagnostics
                $lines[] = '# HELP devspark_signalements_total Nombre de signalements citoyens';
                $lines[] = '# TYPE devspark_signalements_total gauge';
                $lines[] = 'devspark_signalements_total ' . Signalement::count();

                $lines[] = '# HELP devspark_diagnostics_total Nombre de diagnostics énergétiques';
                $lines[] = '# TYPE devspark_diagnostics_total gauge';
                $lines[] = 'devspark_diagnostics_total ' . Diagnostic::count();
            } catch (\Throwable $e) {
                // Silencieux en cas de table manquante
            }
        }

        // 5. Métriques HTTP de trafic (issues du Middleware)
        $metricsFile = storage_path('framework/metrics/http_metrics.json');
        $httpTotal = 0;
        $httpAvgDuration = 0;
        $statusCodes = [];

        if (File::exists($metricsFile)) {
            $data = json_decode(File::get($metricsFile), true);
            if (is_array($data)) {
                $httpTotal = (int) ($data['total'] ?? 0);
                $statusCodes = (array) ($data['status'] ?? []);
                if ($httpTotal > 0 && isset($data['total_duration'])) {
                    $httpAvgDuration = round(($data['total_duration'] / $httpTotal) * 1000, 2); // ms
                }
            }
        }

        $lines[] = '# HELP devspark_http_requests_total Nombre total de requêtes HTTP traitées';
        $lines[] = '# TYPE devspark_http_requests_total counter';
        $lines[] = "devspark_http_requests_total {$httpTotal}";

        $lines[] = '# HELP devspark_http_requests_by_status Nombre de requêtes HTTP par code de statut';
        $lines[] = '# TYPE devspark_http_requests_by_status counter';
        foreach ($statusCodes as $status => $count) {
            $lines[] = "devspark_http_requests_by_status{status_code=\"{$status}\"} {$count}";
        }

        $lines[] = '# HELP devspark_http_request_duration_average_milliseconds Durée moyenne de traitement HTTP en millisecondes';
        $lines[] = '# TYPE devspark_http_request_duration_average_milliseconds gauge';
        $lines[] = "devspark_http_request_duration_average_milliseconds {$httpAvgDuration}";

        // Format texte Prometheus avec saut de ligne final obligatoire
        $content = implode("\n", $lines) . "\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }
}
