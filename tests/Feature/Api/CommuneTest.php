<?php

namespace Tests\Feature\Api;

use App\Models\Commune;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommuneTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_communes(): void
    {
        Commune::create(['name' => 'Santiago', 'shipping_price' => 3000.00]);
        Commune::create(['name' => 'Valparaíso', 'shipping_price' => 5000.00]);

        $response = $this->getJson('/api/communes');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'shippingPrice',
                ]
            ]
        ]);

        $response->assertJsonFragment([
            'name' => 'Santiago',
            'shippingPrice' => 3000.00,
        ]);
    }
}
