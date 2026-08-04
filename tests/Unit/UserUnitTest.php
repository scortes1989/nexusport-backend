<?php

namespace Tests\Unit;

use App\Models\Address;
use App\Models\Commune;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_relationships(): void
    {
        $user = User::factory()->create();

        $commune = Commune::create([
            'name' => 'La Florida',
            'shipping_price' => 3500.00,
        ]);

        $address = Address::create([
            'user_id' => $user->id,
            'name' => 'Casa',
            'address' => 'Av. La Florida 888',
            'commune_id' => $commune->id,
            'is_default' => true,
        ]);

        $card = PaymentMethod::create([
            'user_id' => $user->id,
            'card_brand' => 'Visa',
            'last_four' => '1234',
            'cardholder_name' => 'Usuario Test',
            'expiration_month' => '10',
            'expiration_year' => '2029',
            'is_default' => true,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'session_id' => 'sess-user-1',
            'customer_name' => 'Usuario Test',
            'customer_email' => $user->email,
            'shipping_address' => $address->address,
            'commune_id' => $commune->id,
            'shipping_cost' => 3500.00,
            'subtotal' => 50000.00,
            'total' => 53500.00,
        ]);

        $this->assertCount(1, $user->addresses);
        $this->assertEquals($address->id, $user->addresses->first()->id);

        $this->assertCount(1, $user->paymentMethods);
        $this->assertEquals($card->id, $user->paymentMethods->first()->id);

        $this->assertCount(1, $user->orders);
        $this->assertEquals($order->id, $user->orders->first()->id);
    }
}
