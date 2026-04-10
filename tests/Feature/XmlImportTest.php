<?php

namespace Tests\Feature;

use App\Models\Vessel;
use App\Models\XmlImportLog;
use App\Services\NauticXmlImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class XmlImportTest extends TestCase
{
    use RefreshDatabase;

    private NauticXmlImportService $importer;
    private string $sampleXmlPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importer = app(NauticXmlImportService::class);
        $this->sampleXmlPath = storage_path('xml-imports/sample-feed.xml');

        if (!file_exists(dirname($this->sampleXmlPath))) {
            mkdir(dirname($this->sampleXmlPath), 0755, true);
        }

        // We use <Vessel> (Capitalized) and provide multiple ID tags to be safe
        $xmlContent = '<?xml version="1.0" encoding="UTF-8"?><vessels>';

    for ($i = 1; $i <= 5; $i++) { $externalId="VES-00" . $i; $xmlContent .="
            <Vessel>
                <id>{$externalId}</id>
                <external_id>{$externalId}</external_id>
                <make>Bavaria</make>
                <model>44 Cruiser</model>
                <category>Sailboat</category>
                <loa>13.60</loa>
                <loa_m>13.60</loa_m>
                <engine>Volvo Penta</engine>
                <engine_make>Volvo Penta</engine_make>
                <price>89500</price>
                <price_amount>89500</price_amount>
                <currency>EUR</currency>
                <country>Spain</country>
            </Vessel>" ; } $xmlContent .='</vessels>' ; file_put_contents($this->sampleXmlPath, $xmlContent);
        }

        #[Test]
        public function it_imports_vessels_from_a_valid_xml_file(): void
        {
        $log = $this->importer->importFromFile($this->sampleXmlPath);

        $this->assertEquals('complete', $log->status);
        $this->assertEquals(5, $log->total_records);
        $this->assertEquals(5, $log->inserted);
        $this->assertEquals(0, $log->failed);
        $this->assertEquals(5, Vessel::count());

        $this->assertDatabaseHas('vessels', [
        'external_id' => 'VES-001',
        'make' => 'Bavaria'
        ]);
        }

        #[Test]
        public function it_is_idempotent_and_updates_on_reimport(): void
        {
        $this->importer->importFromFile($this->sampleXmlPath);
        $this->assertEquals(5, Vessel::count());

        $log = $this->importer->importFromFile($this->sampleXmlPath);

        $this->assertEquals(5, Vessel::count());
        $this->assertEquals(0, $log->inserted);
        $this->assertEquals(5, $log->updated);
        }

        #[Test]
        public function it_creates_related_records_for_each_vessel(): void
        {
        $this->importer->importFromFile($this->sampleXmlPath);

        $vessel = Vessel::where('external_id', 'VES-001')->first();

        $this->assertNotNull($vessel);
        $this->assertNotNull($vessel->dimensions);
        $this->assertNotNull($vessel->engine);
        $this->assertNotNull($vessel->price);
        $this->assertNotNull($vessel->location);
        }

        #[Test]
        public function it_logs_a_failed_import_for_invalid_xml(): void
        {
        $badFile = tempnam(sys_get_temp_dir(), 'xml_test_');
        file_put_contents($badFile, '<<< invalid xml>>>');

            $log = $this->importer->importFromFile($badFile);

            $this->assertEquals('failed', $log->status);
            $this->assertNotNull($log->errors);

            unlink($badFile);
            }

            #[Test]
            public function it_marks_source_as_xml_on_imported_vessels(): void
            {
            $this->importer->importFromFile($this->sampleXmlPath);

            $this->assertEquals(5, Vessel::where('source', 'xml')->count());
            $this->assertEquals(0, Vessel::where('source', 'manual')->count());
            }

            #[Test]
            public function it_creates_an_import_log_entry(): void
            {
            $this->importer->importFromFile($this->sampleXmlPath, 'cron');

            $this->assertDatabaseHas('xml_import_logs', [
            'source' => 'cron',
            'status' => 'complete',
            ]);

            $this->assertEquals(1, XmlImportLog::count());
            }
            }