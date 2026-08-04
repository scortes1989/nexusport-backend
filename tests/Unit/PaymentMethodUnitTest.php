<?php

namespace Tests\Unit;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_method_casts_and_user_relation(): void
    {
        $user = User::factory()->create();

        $card = PaymentMethod::create([
            'user_id' => $user->id,
            'card_brand' => 'Mastercard',
            'last_four' => '9999',
            'cardholder_name' => 'Mario Rossi',
            'expiration_month' => '05',
            'expiration_year' => '2030',
            'is_default' => true,
        ]);

        $this->assertIsBool($card->is_default);
        $this->assertTrue($card->is_default);
        $this->assertEquals($user->id, $card->user->id);
    }
}
