<?php

namespace Tests\Unit;

use App\Models\Vessel;
use App\Services\NauticXmlExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class XmlExportTest extends TestCase
{
    use RefreshDatabase;

    private NauticXmlExportService $exporter;

    protected function setUp(): void
    {
        parent::setUp();

        // Use Laravel's service container to resolve the service
        $this->exporter = app(NauticXmlExportService::class);
    }

    #[Test]
    public function it_exports_valid_xml_for_a_single_vessel(): void
    {
        $vessel = $this->createVesselWithRelations();

        // Ensure we pass an Eloquent model, not a Collection
        $xml = $this->exporter->exportSingle($vessel);

        $this->assertStringContainsString('<?xml', $xml);
        $this->assertStringContainsString('<NauticNetwork', $xml);
        $this->assertStringContainsString('<Vessel>', $xml);
        $this->assertStringContainsString('Bavaria', $xml);
        $this->assertStringContainsString('89500', $xml);
        $this->assertStringContainsString('currency="EUR"', $xml);
    }

    #[Test]
    public function it_produces_parseable_xml(): void
    {
        $vessel = $this->createVesselWithRelations();
        $xml    = $this->exporter->exportSingle($vessel);

        libxml_use_internal_errors(true);
        $parsed = simplexml_load_string($xml);

        $this->assertNotFalse($parsed, 'Exported XML is not parseable');
        $this->assertEmpty(libxml_get_errors(), 'XML contains errors');
    }

    #[Test]
    public function it_exports_all_vessels_with_filters(): void
    {
        // Create multiple vessels with different statuses
        $this->createVesselWithRelations(['status' => 'available']);
        $this->createVesselWithRelations(['status' => 'sold', 'make' => 'Jeanneau']);

        // Pass Eloquent collections to exportAll
        $xmlAll      = $this->exporter->exportAll(); // no filter
        $xmlFiltered = $this->exporter->exportAll(['status' => 'available']); // filtered

        $parsedAll      = simplexml_load_string($xmlAll);
        $parsedFiltered = simplexml_load_string($xmlFiltered);

        $this->assertCount(2, $parsedAll->Vessel, 'All vessels XML count mismatch');
        $this->assertCount(1, $parsedFiltered->Vessel, 'Filtered vessels XML count mismatch');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function createVesselWithRelations(array $overrides = []): Vessel
    {
        $vessel = Vessel::create(array_merge([
            'source'      => 'manual',
            'external_id' => 'TEST-' . uniqid(),
            'name'        => 'Bavaria 44 Cruiser',
            'category'    => 'Sailboat',
            'make'        => 'Bavaria',
            'model'       => '44 Cruiser',
            'year_built'  => 2019,
            'status'      => 'available',
        ], $overrides));

        // Create related models
        $vessel->dimensions()->create([
            'loa_m'  => 13.6,
            'beam_m' => 4.27,
            'draft_m'=> 1.85
        ]);

        $vessel->engine()->create([
            'make'      => 'Volvo Penta',
            'model'     => 'D2-40',
            'power_hp'  => 40,
            'fuel_type' => 'diesel'
        ]);

        $vessel->price()->create([
            'amount'   => 89500,
            'currency' => 'EUR'
        ]);

        $vessel->location()->create([
            'country' => 'Spain',
            'port'    => 'Palma de Mallorca'
        ]);

        return $vessel;
    }
}