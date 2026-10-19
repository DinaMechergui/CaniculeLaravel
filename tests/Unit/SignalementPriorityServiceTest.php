<?php

namespace Tests\Unit;

use App\Services\SignalementPriorityService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SignalementPriorityServiceTest extends TestCase
{
    private SignalementPriorityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SignalementPriorityService();
    }

    public function test_fallback_priority_identifies_urgency(): void
    {
        config(['services.gemini.key' => '']);

        $this->assertSame('urgente', $this->service->determinePriority('Danger de feu et flammes dans l armoire electrique'));
        $this->assertSame('urgente', $this->service->determinePriority('Risque d accident et blesses'));
    }

    public function test_fallback_priority_identifies_low_priority(): void
    {
        config(['services.gemini.key' => '']);

        $this->assertSame('faible', $this->service->determinePriority('Defaut esthetique mineur et peinture legere'));
    }

    public function test_fallback_priority_defaults_to_moyenne(): void
    {
        config(['services.gemini.key' => '']);

        $this->assertSame('moyenne', $this->service->determinePriority('Panne d eclairage dans le couloir principal'));
    }

    public function test_gemini_api_determines_priority_when_successful(): void
    {
        config(['services.gemini.key' => 'valid-mock-key']);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => 'urgente'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->assertSame('urgente', $this->service->determinePriority('Probleme tres critique'));
    }

    public function test_gemini_api_falls_back_on_error_or_invalid_response(): void
    {
        config(['services.gemini.key' => 'valid-mock-key']);

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response('Server Error', 500),
        ]);

        $this->assertSame('urgente', $this->service->determinePriority('Danger immediat'));
    }
}
