<?php

namespace Tests\Feature;

use App\Models\PointFraicheur;
use App\Models\TypePoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Tests de l'assistant IA (chatbot OpenRouter) : page publique, validation
 * du message, clé manquante et panne du fournisseur.
 */
class AssistantIaTest extends TestCase
{
    use RefreshDatabase;

    public function test_assistant_page_is_public(): void
    {
        $this->get('/assistant')
            ->assertOk()
            ->assertSee('ai-messages')
            ->assertSee('ai-form')
            ->assertSee('Assistant IA');
    }

    public function test_assistant_requires_a_message(): void
    {
        $this->postJson('/assistant', [])->assertStatus(422);
    }

    public function test_assistant_returns_the_ai_answer(): void
    {
        config(['services.openrouter.key' => 'cle-de-test']);

        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [
                    ['message' => ['role' => 'assistant', 'content' => 'Rendez-vous au parc du Belvédère.']],
                ],
            ]),
        ]);

        $type = TypePoint::factory()->create(['nom' => 'Parc']);
        PointFraicheur::factory()->create([
            'type_point_id' => $type->id,
            'nom' => 'Parc du Belvédère',
        ]);

        $this->postJson('/assistant', [
            'message' => 'Où puis-je me rafraîchir ?',
            'historique' => [
                ['role' => 'user', 'content' => 'Bonjour'],
            ],
        ])
            ->assertOk()
            ->assertJsonPath('reponse', 'Rendez-vous au parc du Belvédère.');

        // Le contexte envoyé au modèle contient les points de la base.
        Http::assertSent(function ($requete): bool {
            $donnees = $requete->data();
            $systeme = $donnees['messages'][0] ?? [];

            return str_contains($requete->url(), 'openrouter.ai')
                && ($systeme['role'] ?? '') === 'system'
                && str_contains($systeme['content'] ?? '', 'Parc du Belv');
        });
    }

    public function test_points_page_embeds_the_chatbot(): void
    {
        $this->get('/points-fraicheur')
            ->assertOk()
            ->assertSee('ai-embed-form')
            ->assertSee('ai-embed-messages');
    }

    public function test_assistant_uses_the_page_context(): void
    {
        config(['services.openrouter.key' => 'cle-de-test']);

        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [
                    ['message' => ['role' => 'assistant', 'content' => 'Avec ces filtres…']],
                ],
            ]),
        ]);

        $this->postJson('/assistant', [
            'message' => 'Et avec ces filtres ?',
            'contexte' => 'type de point = Plage (7)',
        ])->assertOk();

        Http::assertSent(function ($requete): bool {
            $systeme = $requete->data()['messages'][0]['content'] ?? '';

            return str_contains($systeme, 'Filtres actuellement appliqués')
                && str_contains($systeme, 'Plage');
        });
    }

    public function test_assistant_reports_a_missing_key(): void
    {
        config(['services.openrouter.key' => null]);

        $this->postJson('/assistant', ['message' => 'Bonjour'])
            ->assertStatus(503)
            ->assertJsonStructure(['reponse']);
    }

    public function test_assistant_reports_a_provider_failure(): void
    {
        config(['services.openrouter.key' => 'cle-de-test']);

        Http::fake([
            'openrouter.ai/*' => Http::response('erreur', 500),
        ]);

        $this->postJson('/assistant', ['message' => 'Bonjour'])
            ->assertStatus(502)
            ->assertJsonStructure(['reponse']);
    }
}
