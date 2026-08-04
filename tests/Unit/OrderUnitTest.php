<?php

namespace Tests\Unit;

use App\Models\Commune;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_automatically_generates_unique_code(): void
    {
        $commune = Commune::create([
            'name' => 'Ñuñoa',
            'shipping_price' => 3000.00,
        ]);

        $order1 = Order::create([
            'session_id' => 'session-abc',
            'customer_name' => 'Ana López',
            'customer_email' => 'ana@example.com',
            'shipping_address' => 'Av. Ossa 123',
            'commune_id' => $commune->id,
            'shipping_cost' => 3000.00,
            'subtotal' => 15000.00,
            'total' => 18000.00,
        ]);

        $order2 = Order::create([
            'session_id' => 'session-xyz',
            'customer_name' => 'Carlos Ruíz',
            'customer_email' => 'carlos@example.com',
            'shipping_address' => 'Av. Irarrázaval 456',
            'commune_id' => $commune->id,
            'shipping_cost' => 3000.00,
            'subtotal' => 20000.00,
            'total' => 23000.00,
        ]);

        $this->assertNotNull($order1->code);
        $this->assertStringStartsWith('ORD-', $order1->code);

        $this->assertNotNull($order2->code);
        $this->assertStringStartsWith('ORD-', $order2->code);

        $this->assertNotEquals($order1->code, $order2->code);
    }

    public function test_order_relationships(): void
    {
        $user = User::factory()->create();
        $commune = Commune::create([
            'name' => 'Macul',
            'shipping_price' => 3000.00,
        ]);
        $product = Product::factory()->create();
        $productSize = $product->sizes[0];

        $order = Order::create([
            'user_id' => $user->id,
            'session_id' => 'session-rel',
            'customer_name' => 'Pedro Soto',
            'customer_email' => 'pedro@example.com',
            'shipping_address' => 'Calle Mayor 10',
            'commune_id' => $commune->id,
            'shipping_cost' => 3000.00,
            'subtotal' => 10000.00,
            'total' => 13000.00,
        ]);

        $item = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_size_id' => $productSize->id,
            'quantity' => 1,
            'price' => 10000.00,
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'amount' => 13000.00,
            'status' => 'completed',
            'transaction_id' => 'TX-TEST-999',
        ]);

        $this->assertEquals($user->id, $order->user->id);
        $this->assertEquals($commune->id, $order->commune->id);
        $this->assertCount(1, $order->items);
        $this->assertEquals($item->id, $order->items->first()->id);
        $this->assertEquals($payment->id, $order->payment->id);
    }
}
