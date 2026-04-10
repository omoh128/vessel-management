<?php

namespace Database\Seeders;

use App\Models\Vessel;
use App\Services\NauticXmlImportService;
use Illuminate\Database\Seeder;

class VesselSeeder extends Seeder
{
    /**
     * Seed the database using the actual XML import service.
     * This means the seeder proves the importer works end-to-end.
     */
    public function run(): void
    {
        $this->command->info('Seeding vessels from sample XML feed...');

        $importer = app(NauticXmlImportService::class);

        $sampleFile = database_path('seeders/data/sample-feed.xml');

        if (!file_exists($sampleFile)) {
            $this->command->warn('Sample XML not found. Creating one inline...');
            $this->createSampleManualVessels();
            return;
        }

        $log = $importer->importFromFile($sampleFile, 'seeder');

        $this->command->table(
            ['Inserted', 'Updated', 'Failed', 'Status'],
            [[$log->inserted, $log->updated, $log->failed, $log->status]]
        );

        // Also add one manual-entry vessel to show both sources
        $this->createManualVessel();

        $this->command->info('Seeding complete. Total vessels: ' . Vessel::count());
    }

    private function createManualVessel(): void
    {
        $vessel = Vessel::create([
            'source'      => 'manual',
            'name'        => 'Jeanneau Sun Odyssey 440',
            'category'    => 'Sailboat',
            'make'        => 'Jeanneau',
            'model'       => 'Sun Odyssey 440',
            'year_built'  => 2020,
            'status'      => 'available',
            'description' => 'Jeanneau Sun Odyssey 440 in great condition. 3-cabin layout. Entered manually.',
        ]);

        $vessel->dimensions()->create(['loa_m' => 13.34, 'beam_m' => 4.35, 'draft_m' => 2.05]);
        $vessel->engine()->create(['make' => 'Yanmar', 'model' => '4JH45', 'power_hp' => 45, 'fuel_type' => 'diesel']);
        $vessel->price()->create(['amount' => 175000, 'currency' => 'EUR']);
        $vessel->location()->create(['country' => 'Italy', 'port' => 'Portofino']);

        $this->command->line('  + Manual vessel added: Jeanneau Sun Odyssey 440');
    }

    private function createSampleManualVessels(): void
    {
        $samples = [
            ['make' => 'Bavaria',   'model' => '38 Match',    'category' => 'Sailboat',  'year' => 2018, 'price' => 69000,  'currency' => 'EUR'],
            ['make' => 'Fairline',  'model' => 'Squadron 42', 'category' => 'Motorboat', 'year' => 2017, 'price' => 195000, 'currency' => 'EUR'],
            ['make' => 'Fountaine', 'model' => 'Lucia 40',    'category' => 'Catamaran', 'year' => 2019, 'price' => 285000, 'currency' => 'EUR'],
        ];

        foreach ($samples as $data) {
            $vessel = Vessel::create([
                'source'     => 'manual',
                'name'       => $data['make'] . ' ' . $data['model'],
                'category'   => $data['category'],
                'make'       => $data['make'],
                'model'      => $data['model'],
                'year_built' => $data['year'],
                'status'     => 'available',
            ]);
            $vessel->price()->create(['amount' => $data['price'], 'currency' => $data['currency']]);
        }
    }
}