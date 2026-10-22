<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class PrometheusMetricsMiddleware
{
    /**
     * Fichier où les métriques HTTP cumulatives sont stockées rapidement
     */
    protected function getStoragePath(): string
    {
        $dir = storage_path('framework/metrics');
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true, true);
        }
        return $dir . '/http_metrics.json';
    }

    /**
     * Traite la requête et comptabilise la métrique HTTP
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();

        // Ne pas polluer les métriques avec les requêtes de scraping et health check
        if ($path === 'metrics' || $path === 'up' || str_starts_with($path, 'assets/')) {
            return $next($request);
        }

        $start = microtime(true);
        $response = $next($request);
        $duration = microtime(true) - $start;

        try {
            $this->recordMetric($request->method(), $response->getStatusCode(), $duration);
        } catch (\Throwable $e) {
            // Silencieux pour ne pas impacter la requête utilisateur
        }

        return $response;
    }

    /**
     * Enregistre les métriques HTTP cumulées
     */
    protected function recordMetric(string $method, int $status, float $duration): void
    {
        $file = $this->getStoragePath();
        $data = [
            'total' => 0,
            'status' => [],
            'methods' => [],
            'last_duration' => 0,
            'total_duration' => 0,
        ];

        if (File::exists($file)) {
            $content = File::get($file);
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $data = array_merge($data, $decoded);
            }
        }

        $data['total'] = ($data['total'] ?? 0) + 1;
        $data['status'][$status] = ($data['status'][$status] ?? 0) + 1;
        $data['methods'][$method] = ($data['methods'][$method] ?? 0) + 1;
        $data['last_duration'] = round($duration, 4);
        $data['total_duration'] = round(($data['total_duration'] ?? 0) + $duration, 4);

        File::put($file, json_encode($data));
    }
}
