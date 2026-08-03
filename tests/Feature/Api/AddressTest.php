<?php

namespace Tests\Feature\Api;

use App\Models\Address;
use App\Models\Commune;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Commune $commune;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->commune = Commune::create([
            'name' => 'Providencia',
            'shipping_price' => 3500,
            'days_to_deliver' => 2,
        ]);
    }

    public function test_authenticated_user_can_create_address(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/addresses', [
                'name' => 'Casa',
                'address' => 'Av. Providencia 1234',
                'commune_id' => $this->commune->id,
                'is_default' => true,
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'name' => 'Casa',
                'address' => 'Av. Providencia 1234',
                'isDefault' => true,
            ],
        ]);

        $this->assertDatabaseHas('addresses', [
            'user_id' => $this->user->id,
            'name' => 'Casa',
        ]);
    }

    public function test_authenticated_user_can_list_their_addresses(): void
    {
        Address::create([
            'user_id' => $this->user->id,
            'commune_id' => $this->commune->id,
            'name' => 'Oficina',
            'address' => 'Pedro de Valdivia 500',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/addresses');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'Oficina');
    }

    public function test_authenticated_user_can_update_their_address(): void
    {
        $address = Address::create([
            'user_id' => $this->user->id,
            'commune_id' => $this->commune->id,
            'name' => 'Mi Casa',
            'address' => 'Calle Antigua 123',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/addresses/{$address->id}", [
                'address' => 'Calle Nueva 456',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('data.address', 'Calle Nueva 456');
    }

    public function test_authenticated_user_can_delete_their_address(): void
    {
        $address = Address::create([
            'user_id' => $this->user->id,
            'commune_id' => $this->commune->id,
            'name' => 'Para borrar',
            'address' => 'Sin direccion',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/addresses/{$address->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_user_cannot_access_or_modify_another_users_address(): void
    {
        $otherUser = User::factory()->create();
        $address = Address::create([
            'user_id' => $otherUser->id,
            'commune_id' => $this->commune->id,
            'name' => 'Direccion Ajena',
            'address' => 'Calle Privada 999',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/addresses/{$address->id}");

        $response->assertStatus(403);
    }
}
