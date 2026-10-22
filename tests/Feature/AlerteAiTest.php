<?php

namespace Tests\Feature;

use App\Models\Alerte;
use App\Models\BulletinAlerte;
use App\Models\Diffusion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlerteAiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->user = User::factory()->create();
    }

    public function test_guest_cannot_access_ai_endpoints(): void
    {
        $response = $this->postJson(route('admin.bulletins-alerte.ia.generer'), ['alerte_id' => 1]);
        $response->assertUnauthorized();

        $response = $this->postJson(route('admin.diffusions.ia.adapter'), ['bulletin_alerte_id' => 1, 'canal' => 'sms']);
        $response->assertUnauthorized();
    }

    public function test_simple_user_cannot_access_ai_endpoints(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('admin.bulletins-alerte.ia.generer'), ['alerte_id' => 1]);
        $this->assertTrue(in_array($response->getStatusCode(), [302, 403]));

        $response = $this->actingAs($this->user)->postJson(route('admin.diffusions.ia.adapter'), ['bulletin_alerte_id' => 1, 'canal' => 'sms']);
        $this->assertTrue(in_array($response->getStatusCode(), [302, 403]));
    }

    public function test_admin_can_generate_bulletin_via_ai(): void
    {
        $alerte = Alerte::factory()->enCours()->create([
            'titre' => 'Canicule exceptionnelle',
            'type' => 'canicule',
            'zone_concernee' => 'Grand Tunis',
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.bulletins-alerte.ia.generer'), [
            'alerte_id' => $alerte->id,
            'keywords' => 'Pic de chaleur 46°C, sirocco violent',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'niveau',
            'niveau_label',
            'evolution',
            'evolution_label',
            'message',
            'conduite_a_tenir',
            'temperature_max',
            'provider',
        ]);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertContains($data['niveau'], ['vert', 'jaune', 'orange', 'rouge']);
        $this->assertNotEmpty($data['message']);
        $this->assertNotEmpty($data['conduite_a_tenir']);
    }

    public function test_cannot_generate_bulletin_for_terminated_alerte(): void
    {
        $alerte = Alerte::factory()->terminee()->create();

        $response = $this->actingAs($this->admin)->postJson(route('admin.bulletins-alerte.ia.generer'), [
            'alerte_id' => $alerte->id,
            'keywords' => 'Test',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_admin_can_adapt_diffusion_via_ai_for_all_channels(): void
    {
        $alerte = Alerte::factory()->create([
            'zone_concernee' => 'Kairouan',
        ]);

        $bulletin = BulletinAlerte::factory()->create([
            'alerte_id' => $alerte->id,
            'niveau' => 'rouge',
            'evolution' => 'aggravation',
            'message' => 'Vigilance rouge chaleur extrême sur Kairouan avec 47°C attendus.',
        ]);

        $canaux = array_keys(Diffusion::CANAUX);

        foreach ($canaux as $canal) {
            $response = $this->actingAs($this->admin)->postJson(route('admin.diffusions.ia.adapter'), [
                'bulletin_alerte_id' => $bulletin->id,
                'canal' => $canal,
            ]);

            $response->assertOk();
            $data = $response->json();

            $this->assertTrue($data['success']);
            $this->assertEquals($canal, $data['canal']);
            $this->assertNotEmpty($data['contenu']);
            $this->assertLessThanOrEqual(160, mb_strlen($data['contenu']));
            $this->assertGreaterThan(0, $data['destinataires_estimes']);
            $this->assertNotEmpty($data['provider']);
        }
    }

    public function test_adapt_diffusion_validation_errors(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('admin.diffusions.ia.adapter'), [
            'canal' => 'canal_invalide',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['bulletin_alerte_id', 'canal']);
    }

    public function test_gemini_api_call_integration_for_bulletin_and_diffusion(): void
    {
        config(['services.gemini.key' => 'fake-test-key']);

        \Illuminate\Support\Facades\Http::fake([
            'https://generativelanguage.googleapis.com/*' => \Illuminate\Support\Facades\Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode([
                                    'niveau' => 'orange',
                                    'evolution' => 'aggravation',
                                    'message' => 'Message généré par Gemini API',
                                    'conduite_a_tenir' => 'Consignes de sécurité Gemini',
                                    'temperature_max' => 43,
                                    'contenu' => 'Diffusion SMS via Gemini',
                                    'destinataires_estimes' => 30000,
                                ])]
                            ]
                        ]
                    ]
                ]
            ]),
        ]);

        $alerte = Alerte::factory()->enCours()->create();
        $bulletin = BulletinAlerte::factory()->create(['alerte_id' => $alerte->id]);

        $respB = $this->actingAs($this->admin)->postJson(route('admin.bulletins-alerte.ia.generer'), [
            'alerte_id' => $alerte->id,
            'keywords' => 'Test Gemini',
        ]);

        $respB->assertOk();
        $this->assertEquals('Google Gemini 1.5 Flash (IA Cloud)', $respB->json('provider'));

        $respD = $this->actingAs($this->admin)->postJson(route('admin.diffusions.ia.adapter'), [
            'bulletin_alerte_id' => $bulletin->id,
            'canal' => 'sms',
        ]);

        $respD->assertOk();
        $this->assertEquals('Google Gemini 1.5 Flash (IA Cloud)', $respD->json('provider'));
    }

    public function test_openrouter_api_call_integration_when_gemini_absent(): void
    {
        config([
            'services.gemini.key' => null,
            'services.openrouter.key' => 'fake-openrouter-key',
        ]);

        \Illuminate\Support\Facades\Http::fake([
            'https://openrouter.ai/*' => \Illuminate\Support\Facades\Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'niveau' => 'rouge',
                                'evolution' => 'aggravation',
                                'message' => 'Message OpenRouter GPT-4o',
                                'conduite_a_tenir' => 'Consignes OpenRouter',
                                'temperature_max' => 46,
                                'contenu' => 'Diffusion OpenRouter SMS',
                                'destinataires_estimes' => 45000,
                            ])
                        ]
                    ]
                ]
            ]),
        ]);

        $alerte = Alerte::factory()->enCours()->create();
        $bulletin = BulletinAlerte::factory()->create(['alerte_id' => $alerte->id]);

        $respB = $this->actingAs($this->admin)->postJson(route('admin.bulletins-alerte.ia.generer'), [
            'alerte_id' => $alerte->id,
        ]);

        $respB->assertOk();
        $this->assertEquals('OpenRouter GPT-4o-mini (IA Cloud)', $respB->json('provider'));

        $respD = $this->actingAs($this->admin)->postJson(route('admin.diffusions.ia.adapter'), [
            'bulletin_alerte_id' => $bulletin->id,
            'canal' => 'notification_push',
        ]);

        $respD->assertOk();
        $this->assertEquals('OpenRouter GPT-4o-mini (IA Cloud)', $respD->json('provider'));
    }

    public function test_ai_bulletin_temperature_scale_complies_with_rules(): void
    {
        $alerte = Alerte::factory()->enCours()->create([
            'zone_concernee' => 'Mourouj',
        ]);

        $cases = [
            'morouj 27' => 'vert',
            'tunis 20°C' => 'vert',
            'sfax 30' => 'vert',
            'mourouj 35' => 'jaune',
            'bizerte 39' => 'jaune',
            'tozeur 42' => 'rouge',
            'kairouan 46°C' => 'rouge',
            'tabarka 15°C' => 'jaune',
            'zaghouan 10' => 'jaune',
            'ain draham 8' => 'rouge',
            'kef 4°C' => 'rouge',
        ];

        foreach ($cases as $keywords => $expectedLevel) {
            $response = $this->actingAs($this->admin)->postJson(route('admin.bulletins-alerte.ia.generer'), [
                'alerte_id' => $alerte->id,
                'keywords' => $keywords,
            ]);

            $response->assertOk();
            $data = $response->json();
            $this->assertEquals(
                $expectedLevel,
                $data['niveau'],
                "Échec pour '{$keywords}' : attendu {$expectedLevel}, obtenu {$data['niveau']}"
            );
        }
    }
}
