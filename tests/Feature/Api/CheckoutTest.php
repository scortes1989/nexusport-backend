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

class CheckoutTest extends TestCase
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

        $this->category = Category::create([
            'name' => 'Chaquetas',
            'slug' => 'chaquetas',
            'description' => 'Test Desc',
        ]);

        $this->product = Product::create([
            'name' => 'Product Test',
            'slug' => 'product-test',
            'price' => 50.00,
            'description' => 'Desc',
            'category' => 'Chaquetas',
            'category_id' => $this->category->id,
            'stock' => 10,
        ]);

        $this->productSize = ProductSize::create([
            'product_id' => $this->product->id,
            'size' => 'M',
            'stock' => 5,
        ]);

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

    public function test_can_checkout_successfully(): void
    {
        // 1. Add item to user cart in DB
        CartItem::create([
            'session_id' => $this->sessionId,
            'product_id' => $this->product->id,
            'product_size_id' => $this->productSize->id,
            'quantity' => 2,
        ]);

        // 2. Perform checkout
        $response = $this->withHeader('X-Session-ID', $this->sessionId)
            ->postJson('/api/checkout', [
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
                'customerName',
                'customerEmail',
                'shippingAddress',
                'communeName',
                'shippingCost',
                'subtotal',
                'total',
                'status',
                'paymentMethodName',
                'transactionId',
                'createdAt',
            ]
        ]);

        // 3. Assert database state
        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Juan Pérez',
            'customer_email' => 'juan@example.com',
            'total' => 103000.00, // (2 * 50) = 100.00 USD + 3000 CLP. Since the price is 100, shipping is added.
            'status' => 'paid',
        ]);

        // Assert payment record was created
        $this->assertDatabaseHas('payments', [
            'amount' => 103000.00,
            'status' => 'completed',
        ]);

        // Assert stock was decremented (5 - 2 = 3)
        $this->assertEquals(3, $this->productSize->fresh()->stock);

        // Assert user cart was cleared
        $this->assertDatabaseMissing('cart_items', [
            'session_id' => $this->sessionId,
        ]);
    }

    public function test_checkout_fails_if_stock_insufficient(): void
    {
        // Add item to user cart in DB with quantity higher than stock (5)
        CartItem::create([
            'session_id' => $this->sessionId,
            'product_id' => $this->product->id,
            'product_size_id' => $this->productSize->id,
            'quantity' => 6,
        ]);

        $response = $this->withHeader('X-Session-ID', $this->sessionId)
            ->postJson('/api/checkout', [
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
}
