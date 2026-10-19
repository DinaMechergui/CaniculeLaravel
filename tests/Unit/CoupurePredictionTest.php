<?php

namespace Tests\Unit;

use App\Models\Coupure;
use App\Models\Quartier;
use App\Services\CoupurePredictionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CoupurePredictionTest extends TestCase
{
    use RefreshDatabase;

    private CoupurePredictionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CoupurePredictionService();
    }

    public function test_prediction_returns_deterministic_estimate_without_ai(): void
    {
        config(['services.openrouter.key' => '']);

        $quartier = Quartier::factory()->create();
        $now = Carbon::now();

        $result = $this->service->predireFin($quartier->id, 'panne', $now, 'Transformateur HS');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('date_fin_estimee', $result);
        $this->assertArrayHasKey('duree_minutes', $result);
        $this->assertArrayHasKey('confiance', $result);
        $this->assertSame('Moteur statistique historique', $result['source']);
        $this->assertSame(210, $result['duree_minutes']);
    }

    public function test_prediction_uses_historical_data_when_available(): void
    {
        config(['services.openrouter.key' => '']);

        $quartier = Quartier::factory()->create();
        $debut = Carbon::now()->subHours(5);
        $fin = $debut->copy()->addMinutes(60);

        Coupure::factory()->create([
            'quartier_id' => $quartier->id,
            'type' => 'delestage',
            'date_debut' => $debut,
            'date_fin' => $fin,
            'statut' => 'resolue',
        ]);

        $result = $this->service->predireFin($quartier->id, 'delestage', Carbon::now(), 'Délestage programmé');

        $this->assertGreaterThan(0, $result['duree_minutes']);
        $this->assertArrayHasKey('explication', $result);
    }

    public function test_prediction_with_mocked_ai(): void
    {
        config(['services.openrouter.key' => 'mock-token']);

        Http::fake([
            'https://openrouter.ai/api/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'duree_minutes' => 90,
                                'confiance' => 'Très élevée (90%)',
                                'explication' => 'Intervention rapide de la STEG',
                                'facteurs' => ['Heure creuse', 'Équipe sur place'],
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $quartier = Quartier::factory()->create();
        $result = $this->service->predireFin($quartier->id, 'surcharge', Carbon::now(), 'Surcharge estivale');

        $this->assertSame(90, $result['duree_minutes']);
        $this->assertSame('Très élevée (90%)', $result['confiance']);
        $this->assertSame('Intelligence Artificielle (GPT-4o-mini)', $result['source']);
    }
}
