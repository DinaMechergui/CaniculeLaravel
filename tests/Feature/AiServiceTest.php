<?php

namespace Tests\Feature;

use App\Services\AiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Tests du service IA générique : secours systématique, parsing JSON sûr,
 * mise en cache, confidentialité (masquage des données personnelles) et
 * fournisseurs (OpenRouter / Gemini).
 */
class AiServiceTest extends TestCase
{
    private function ouvrirOpenRouter(): void
    {
        config([
            'services.gemini.key' => null,
            'services.openrouter.key' => 'cle-de-test',
        ]);
    }

    private function sansAucunFournisseur(): void
    {
        config([
            'services.gemini.key' => null,
            'services.openrouter.key' => null,
        ]);
    }

    private function reponseOpenRouter(string $contenu): array
    {
        return [
            'choices' => [['message' => ['content' => $contenu]]],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Secours systématique (jamais d'exception, toujours une valeur)
    |--------------------------------------------------------------------------
    */

    public function test_json_returns_fallback_when_no_provider_is_configured(): void
    {
        $this->sansAucunFournisseur();

        $resultat = app(AiService::class)->json(
            'Tu es un assistant.',
            'Bonjour',
            ['reponse' => 'secours', 'confiance' => 0]
        );

        $this->assertSame(['reponse' => 'secours', 'confiance' => 0], $resultat);
    }

    public function test_texte_returns_fallback_when_no_provider_is_configured(): void
    {
        $this->sansAucunFournisseur();

        $resultat = app(AiService::class)->texte('Tu es un assistant.', 'Bonjour', 'réponse de secours');

        $this->assertSame('réponse de secours', $resultat);
    }

    public function test_json_returns_fallback_when_provider_fails_with_http_error(): void
    {
        $this->ouvrirOpenRouter();
        Http::fake(['*' => Http::response('erreur serveur', 500)]);

        $resultat = app(AiService::class)->json('sys', 'prompt', ['ok' => false]);

        $this->assertSame(['ok' => false], $resultat);
        // 2 tentatives (retry(2)) puis secours.
        Http::assertSentCount(2);
    }

    public function test_json_returns_fallback_when_provider_returns_invalid_json(): void
    {
        $this->ouvrirOpenRouter();
        Http::fake(['*' => Http::response($this->reponseOpenRouter('ceci n\'est pas du json'))]);

        $resultat = app(AiService::class)->json('sys', 'prompt', ['ok' => false]);

        $this->assertSame(['ok' => false], $resultat);
    }

    /*
    |--------------------------------------------------------------------------
    | Parsing JSON sûr
    |--------------------------------------------------------------------------
    */

    public function test_json_response_wrapped_in_markdown_fences_is_extracted(): void
    {
        $this->ouvrirOpenRouter();
        Http::fake(['*' => Http::response($this->reponseOpenRouter(
            "```json\n{\"priorite\":\"haute\",\"confiance\":0.8}\n```"
        ))]);

        $resultat = app(AiService::class)->json('sys', 'prompt', ['priorite' => 'normale']);

        $this->assertSame('haute', $resultat['priorite']);
        $this->assertSame(0.8, $resultat['confiance']);
    }

    public function test_json_response_with_surrounding_text_is_extracted(): void
    {
        $this->ouvrirOpenRouter();
        Http::fake(['*' => Http::response($this->reponseOpenRouter(
            'Voici le résultat : {"tags":["mobilite"]} en JSON.'
        ))]);

        $resultat = app(AiService::class)->json('sys', 'prompt', ['tags' => []]);

        $this->assertSame(['mobilite'], $resultat['tags']);
    }

    public function test_ai_response_cannot_empty_the_fallback_keys(): void
    {
        $this->ouvrirOpenRouter();
        Http::fake(['*' => Http::response($this->reponseOpenRouter('{"reponse":"par IA"}'))]);

        $resultat = app(AiService::class)->json('sys', 'prompt', [
            'reponse' => 'secours',
            'confiance' => 0,
        ]);

        $this->assertSame('par IA', $resultat['reponse']);
        $this->assertArrayHasKey('confiance', $resultat);
    }

    /*
    |--------------------------------------------------------------------------
    | Confidentialité
    |--------------------------------------------------------------------------
    */

    public function test_email_and_phone_are_masked_before_being_sent_to_the_provider(): void
    {
        $this->ouvrirOpenRouter();
        Http::fake(['*' => Http::response($this->reponseOpenRouter('{"ok":true}'))]);

        app(AiService::class)->json(
            'Tu es un assistant de réservation.',
            'Contact : amina@example.com au 98123456 ou +216 98 123 456.',
            ['ok' => false]
        );

        Http::assertSent(function ($requete) {
            $corps = $requete->body();

            return ! str_contains($corps, 'amina@example.com')
                && ! str_contains($corps, '98123456');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    */

    public function test_identical_requests_are_served_from_cache(): void
    {
        $this->ouvrirOpenRouter();
        Http::fake(['*' => Http::response($this->reponseOpenRouter('{"reponse":"une seule fois"}'))]);

        $service = app(AiService::class);

        $service->json('sys', 'identique', ['reponse' => '']);
        $service->json('sys', 'identique', ['reponse' => '']);

        Http::assertSentCount(1);
    }

    /*
    |--------------------------------------------------------------------------
    | Détection du fournisseur
    |--------------------------------------------------------------------------
    */

    public function test_configure_flag_reflects_available_keys(): void
    {
        $service = app(AiService::class);

        $this->sansAucunFournisseur();
        $this->assertFalse($service->configure());

        $this->ouvrirOpenRouter();
        $this->assertTrue($service->configure());
    }
}
