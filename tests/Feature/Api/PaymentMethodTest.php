<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_authenticated_user_can_create_payment_method(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/payment-methods', [
                'card_brand' => 'Visa',
                'last_four' => '4242',
                'cardholder_name' => 'Juan Pérez',
                'expiration_month' => '08',
                'expiration_year' => '2028',
                'is_default' => true,
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'cardBrand' => 'Visa',
                'lastFour' => '4242',
                'cardholderName' => 'Juan Pérez',
                'isDefault' => true,
            ],
        ]);

        $this->assertDatabaseHas('payment_methods', [
            'user_id' => $this->user->id,
            'last_four' => '4242',
        ]);
    }

    public function test_authenticated_user_can_create_payment_method_with_full_card_number(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/payment-methods', [
                'card_brand' => 'Mastercard',
                'card_number' => '4532 1234 5678 9876',
                'cardholder_name' => 'María Silva',
                'expiration_month' => '11',
                'expiration_year' => '2027',
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'cardBrand' => 'Mastercard',
                'lastFour' => '9876',
                'cardholderName' => 'María Silva',
            ],
        ]);

        $this->assertDatabaseHas('payment_methods', [
            'user_id' => $this->user->id,
            'last_four' => '9876',
        ]);
    }

    public function test_authenticated_user_can_list_their_payment_methods(): void
    {
        PaymentMethod::create([
            'user_id' => $this->user->id,
            'card_brand' => 'Mastercard',
            'last_four' => '5555',
            'cardholder_name' => 'Juan Pérez',
            'expiration_month' => '12',
            'expiration_year' => '2027',
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/payment-methods');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.cardBrand', 'Mastercard');
    }

    public function test_authenticated_user_can_delete_their_payment_method(): void
    {
        $card = PaymentMethod::create([
            'user_id' => $this->user->id,
            'card_brand' => 'AMEX',
            'last_four' => '3000',
            'cardholder_name' => 'Juan Pérez',
            'expiration_month' => '01',
            'expiration_year' => '2026',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/payment-methods/{$card->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('payment_methods', ['id' => $card->id]);
    }

    public function test_user_cannot_access_another_users_payment_method(): void
    {
        $otherUser = User::factory()->create();
        $card = PaymentMethod::create([
            'user_id' => $otherUser->id,
            'card_brand' => 'Visa',
            'last_four' => '9999',
            'cardholder_name' => 'Otro Usuario',
            'expiration_month' => '05',
            'expiration_year' => '2029',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/payment-methods/{$card->id}");

        $response->assertStatus(403);
    }
}
