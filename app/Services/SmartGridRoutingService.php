<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SmartGridRoutingService
{
    public function isConfigured(): bool
    {
        return filled(config('services.openrouteservice.api_key'));
    }

    /**
     * Business priority is sorted first; road travel time breaks ties.
     * This makes the proposed order auditable rather than silently overriding priorities.
     */
    public function optimize(array $origin, array $stops): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Le routage n’est pas configuré. Ajoutez OPENROUTESERVICE_API_KEY dans le fichier .env.');
        }

        $locations = [
            [(float) $origin['longitude'], (float) $origin['latitude']],
            ...array_map(fn (array $stop) => [(float) $stop['longitude'], (float) $stop['latitude']], $stops),
        ];
        $key = config('services.openrouteservice.api_key');
        $base = rtrim(config('services.openrouteservice.base_url'), '/');

        try {
            $matrix = Http::acceptJson()->withHeaders(['Authorization' => $key])->timeout(15)
                ->post($base.'/v2/matrix/driving-car', [
                    'locations' => $locations,
                    'metrics' => ['duration', 'distance'],
                    'resolve_locations' => false,
                ])->throw()->json();

            if (! is_array($matrix['durations'] ?? null) || count($matrix['durations']) !== count($locations)) {
                Log::warning('Matrice de routage invalide pour SmartGrid.');
                throw new RuntimeException('Le service de routage n’a pas pu comparer les étapes sélectionnées.');
            }

            $remaining = array_keys($stops);
            $ordered = [];
            $current = 0;
            while ($remaining !== []) {
                usort($remaining, function (int $a, int $b) use ($stops, $matrix, $current): int {
                    $scoreComparison = $stops[$b]['priority']['score'] <=> $stops[$a]['priority']['score'];
                    if ($scoreComparison !== 0) {
                        return $scoreComparison;
                    }

                    $durationA = $matrix['durations'][$current][$a + 1] ?? null;
                    $durationB = $matrix['durations'][$current][$b + 1] ?? null;
                    if (! is_numeric($durationA) || ! is_numeric($durationB)) {
                        throw new RuntimeException('Le service de routage n’a pas pu calculer un trajet entre deux étapes.');
                    }

                    return $durationA <=> $durationB;
                });
                $next = array_shift($remaining);
                $ordered[] = $stops[$next];
                $current = $next + 1;
            }

            $routeLocations = [
                $locations[0],
                ...array_map(fn (array $stop) => [(float) $stop['longitude'], (float) $stop['latitude']], $ordered),
            ];
            $route = Http::acceptJson()->withHeaders(['Authorization' => $key])->timeout(20)
                ->post($base.'/v2/directions/driving-car/geojson', [
                    'coordinates' => $routeLocations,
                    'instructions' => true,
                    'units' => 'km',
                ])->throw()->json();
        } catch (\Illuminate\Http\Client\RequestException|\Illuminate\Http\Client\ConnectionException $exception) {
            Log::warning('Échec du routage SmartGrid.', ['exception' => $exception::class]);
            throw new RuntimeException('Le service de routage est momentanément indisponible. Les distances n’ont pas été estimées.');
        }

        $summary = $route['features'][0]['properties']['summary'] ?? null;
        $geometry = $route['features'][0]['geometry'] ?? null;
        if (! is_array($summary) || ! is_array($geometry)) {
            Log::warning('Réponse de routage OpenRouteService invalide.');
            throw new RuntimeException('Le service de routage a retourné une réponse inexploitable.');
        }

        if (! is_numeric($summary['distance'] ?? null) || ! is_numeric($summary['duration'] ?? null)) {
            Log::warning('Résumé de routage invalide pour SmartGrid.');
            throw new RuntimeException('Le service de routage n’a pas fourni de distance et durée vérifiables.');
        }

        return [
            'steps' => array_values($ordered),
            'distance_km' => $summary['distance'],
            'duration_seconds' => $summary['duration'],
            'geometry' => $geometry,
            'algorithm' => 'Priorité déterministe décroissante, puis durée routière minimale pour départager les priorités égales.',
        ];
    }
}
