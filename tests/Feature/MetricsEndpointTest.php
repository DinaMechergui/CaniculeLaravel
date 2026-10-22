<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetricsEndpointTest extends TestCase
{
    use RefreshDatabase;
    public function test_metrics_endpoint_is_accessible(): void
    {
        $response = $this->get('/metrics');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; version=0.0.4; charset=utf-8');

        $content = $response->getContent();
        $this->assertStringContainsString('laravel_up 1', $content);
        $this->assertStringContainsString('php_memory_usage_bytes', $content);
        $this->assertStringContainsString('devspark_database_up', $content);
        $this->assertStringContainsString('devspark_reservations_total', $content);
    }
}
