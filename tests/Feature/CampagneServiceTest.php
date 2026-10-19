<?php
namespace Tests\Feature;

use App\Models\CampagneConseil;
use App\Models\Conseil;
use App\Services\CampagneService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampagneServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_status_transitions_cannot_go_backwards(): void
    {
        $campagne=CampagneConseil::factory()->for(Conseil::factory())->create(['statut'=>'brouillon','date_fin'=>now()->addDays(2)]);
        $service=new CampagneService();
        $this->assertNull($service->activer($campagne));
        $this->assertSame('active',$campagne->fresh()->statut);
        $this->assertNotNull($service->activer($campagne->fresh()));
        $this->assertNull($service->terminer($campagne->fresh()));
        $this->assertSame('terminee',$campagne->fresh()->statut);
    }

    public function test_automatic_status_update_supports_dry_run(): void
    {
        $campagne=CampagneConseil::factory()->for(Conseil::factory())->create(['statut'=>'active','date_fin'=>now()->subDay()]);
        $service=new CampagneService();
        $result=$service->actualiserStatuts(true);
        $this->assertSame(1,$result['terminees']);
        $this->assertSame('active',$campagne->fresh()->statut);
        $service->actualiserStatuts();
        $this->assertSame('terminee',$campagne->fresh()->statut);
    }
}
