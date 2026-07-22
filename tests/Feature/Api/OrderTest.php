<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductSize;
use App\Models\Commune;
use App\Models\PaymentMethod;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private $category;
    private $product;
    private $productSize;
    private $commune;
    private $paymentMethod;
    private $sessionId = 'test-session-id-123';

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::factory()->create(['price' => 50.00]);
        $this->productSize = $this->product->sizes[0];
        $this->productSize->update(['stock' => 5]);
        $this->category = $this->product->category;

        $this->commune = Commune::create([
            'name' => 'Providencia',
            'shipping_price' => 3000.00,
        ]);

        $this->paymentMethod = PaymentMethod::create([
            'name' => 'Webpay Plus',
            'code' => 'webpay',
            'is_active' => true,
        ]);
    }

    public function test_can_list_payment_methods(): void
    {
        $response = $this->getJson('/api/payment-methods');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'code',
                ]
            ]
        ]);
        $response->assertJsonFragment(['code' => 'webpay']);
    }

    public function test_can_create_order_successfully(): void
    {
        // Calculate expected dispatch date skipping weekends
        $dispatchDate = now();
        if ($dispatchDate->isWeekend()) {
            while ($dispatchDate->isWeekend()) {
                $dispatchDate->addDay();
            }
        }
        $expectedDispatch = $dispatchDate->toDateString();

        // Calculate expected delivery date skipping weekends starting from dispatch
        $deliveryDate = clone $dispatchDate;
        $addedDays = 0;
        while ($addedDays < 2) {
            $deliveryDate->addDay();
            if (!$deliveryDate->isWeekend()) {
                $addedDays++;
            }
        }
        $expectedDate = $deliveryDate->toDateString();

        // 1. Add item to user cart in DB
        CartItem::create([
            'session_id' => $this->sessionId,
            'product_id' => $this->product->id,
            'product_size_id' => $this->productSize->id,
            'quantity' => 2,
        ]);

        // 2. Perform checkout
        $response = $this->withHeader('X-Session-ID', $this->sessionId)
            ->postJson('/api/orders', [
                'name' => 'Juan Pérez',
                'email' => 'juan@example.com',
                'address' => 'Av Providencia 100',
                'commune_id' => $this->commune->id,
                'payment_method_id' => $this->paymentMethod->id,
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'code',
                'customerName',
                'customerEmail',
                'shippingAddress',
                'commune' => [
                    'id',
                    'name',
                    'shippingPrice',
                    'daysToDeliver',
                ],
                'shippingCost',
                'subtotal',
                'total',
                'status',
                'paymentMethod' => [
                    'id',
                    'name',
                    'logo',
                ],
                'payment' => [
                    'id',
                    'amount',
                    'status',
                    'transactionId',
                    'createdAt',
                ],
                'createdAt',
                'estimatedDispatchDate',
                'estimatedDeliveryDate',
                'items' => [
                    '*' => [
                        'id',
                        'productSize' => [
                            'id',
                            'size',
                            'stock',
                        ],
                        'quantity',
                        'price',
                        'product' => [
                            'id',
                            'name',
                            'description',
                            'price',
                            'imageUrl',
                        ]
                    ]
                ]
            ]
        ]);

        $orderCode = $response->json('data.code');
        $this->assertNotNull($orderCode);

        // 3. Assert database state
        $this->assertDatabaseHas('orders', [
            'code' => $orderCode,
            'customer_name' => 'Juan Pérez',
            'customer_email' => 'juan@example.com',
            'total' => 3100.00,
            'status' => 'paid',
            'estimated_dispatch_date' => $expectedDispatch . ' 00:00:00',
            'estimated_delivery_date' => $expectedDate . ' 00:00:00',
        ]);

        // Assert payment record was created
        $this->assertDatabaseHas('payments', [
            'amount' => 3100.00,
            'status' => 'completed',
        ]);

        // Assert stock was decremented (5 - 2 = 3)
        $this->assertEquals(3, $this->productSize->fresh()->stock);

        // Assert user cart was cleared
        $this->assertDatabaseMissing('cart_items', [
            'session_id' => $this->sessionId,
        ]);
    }

    public function test_order_creation_validation_fails_for_missing_fields(): void
    {
        $response = $this->withHeader('X-Session-ID', $this->sessionId)
            ->postJson('/api/orders', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email', 'address', 'commune_id', 'payment_method_id']);
    }

    public function test_order_creation_fails_if_stock_insufficient(): void
    {
        // Add item to user cart in DB with quantity higher than stock (5)
        CartItem::create([
            'session_id' => $this->sessionId,
            'product_id' => $this->product->id,
            'product_size_id' => $this->productSize->id,
            'quantity' => 6,
        ]);

        $response = $this->withHeader('X-Session-ID', $this->sessionId)
            ->postJson('/api/orders', [
                'name' => 'Juan Pérez',
                'email' => 'juan@example.com',
                'address' => 'Av Providencia 100',
                'commune_id' => $this->commune->id,
                'payment_method_id' => $this->paymentMethod->id,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['stock']);

        // Assert stock remains unchanged (5)
        $this->assertEquals(5, $this->productSize->fresh()->stock);
    }

    public function test_can_track_order_details(): void
    {
        // Create an order directly in DB
        $order = Order::create([
            'code' => 'ORD-12345678',
            'session_id' => $this->sessionId,
            'customer_name' => 'Juan Pérez',
            'customer_email' => 'juan@example.com',
            'shipping_address' => 'Av Providencia 100',
            'commune_id' => $this->commune->id,
            'shipping_cost' => 3000.00,
            'subtotal' => 100000.00,
            'total' => 103000.00,
            'status' => 'paid',
            'payment_method_id' => $this->paymentMethod->id,
            'estimated_dispatch_date' => '2026-07-24',
            'estimated_delivery_date' => '2026-07-25',
        ]);

        \App\Models\Payment::create([
            'order_id' => $order->id,
            'payment_method_id' => $this->paymentMethod->id,
            'amount' => $order->total,
            'status' => 'completed',
            'transaction_id' => 'TX-12345678',
        ]);

        // Search by ID
        $response = $this->getJson("/api/orders/{$order->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'code',
                'customerName',
                'customerEmail',
                'shippingAddress',
                'commune' => [
                    'id',
                    'name',
                    'shippingPrice',
                    'daysToDeliver',
                ],
                'shippingCost',
                'subtotal',
                'total',
                'status',
                'paymentMethod' => [
                    'id',
                    'name',
                    'logo',
                ],
                'payment' => [
                    'id',
                    'amount',
                    'status',
                    'transactionId',
                    'createdAt',
                ],
                'createdAt',
                'estimatedDispatchDate',
                'estimatedDeliveryDate',
                'items',
            ]
        ]);
        $response->assertJsonFragment(['code' => 'ORD-12345678']);

        // Search by Code
        $responseByCode = $this->getJson("/api/orders/ORD-12345678");
        $responseByCode->assertStatus(200);
        $responseByCode->assertJsonFragment(['customerName' => 'Juan Pérez']);
    }
}
