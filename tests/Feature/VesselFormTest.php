<?php

namespace Tests\Feature;

use App\Models\Vessel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class VesselFormTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_vessel_via_the_form(): void
    {
        $response = $this->post(route('vessels.store'), $this->validVesselData());

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('vessels', [
            'make'   => 'Bavaria',
            'model'  => '44 Cruiser',
            'source' => 'manual',
        ]);

        $vessel = Vessel::first();
        $this->assertNotNull($vessel->price);
        $this->assertNotNull($vessel->dimensions);
        $this->assertNotNull($vessel->engine);
        $this->assertNotNull($vessel->location);
    }

    /** @test */
    public function it_validates_required_fields(): void
    {
        $response = $this->post(route('vessels.store'), []);

        $response->assertSessionHasErrors(['make', 'model', 'category', 'price', 'currency']);
    }

    /** @test */
    public function it_validates_price_is_numeric(): void
    {
        $data          = $this->validVesselData();
        $data['price'] = 'not-a-number';

        $response = $this->post(route('vessels.store'), $data);

        $response->assertSessionHasErrors(['price']);
    }

    /** @test */
    public function it_can_update_a_vessel(): void
    {
        $vessel = Vessel::create([
            'source' => 'manual', 'name' => 'Old Name',
            'category' => 'Sailboat', 'make' => 'Old', 'model' => 'Model',
            'status' => 'available',
        ]);

        $data          = $this->validVesselData();
        $data['make']  = 'Jeanneau';
        $data['model'] = 'Sun Odyssey 440';

        $this->put(route('vessels.update', $vessel), $data);

        $vessel->refresh();
        $this->assertEquals('Jeanneau', $vessel->make);
        $this->assertEquals('Sun Odyssey 440', $vessel->model);
    }

    /** @test */
    public function it_soft_deletes_a_vessel(): void
    {
        $vessel = Vessel::create([
            'source' => 'manual', 'name' => 'Test', 'category' => 'Sailboat',
            'make' => 'Test', 'model' => 'Boat', 'status' => 'available',
        ]);

        $this->delete(route('vessels.destroy', $vessel));

        $this->assertSoftDeleted('vessels', ['id' => $vessel->id]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function validVesselData(): array
    {
        return [
            'category'        => 'Sailboat',
            'make'            => 'Bavaria',
            'model'           => '44 Cruiser',
            'year_built'      => 2019,
            'status'          => 'available',
            'description'     => 'Test vessel description.',
            'loa_m'           => 13.60,
            'beam_m'          => 4.27,
            'draft_m'         => 1.85,
            'engine_make'     => 'Volvo Penta',
            'engine_model'    => 'D2-40',
            'engine_power_hp' => 40,
            'fuel_type'       => 'diesel',
            'price'           => 89500,
            'currency'        => 'EUR',
            'country'         => 'Spain',
            'port'            => 'Palma de Mallorca',
        ];
    }
}