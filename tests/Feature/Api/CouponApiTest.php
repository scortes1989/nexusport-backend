<?php

namespace Tests\Feature\Api;

use App\Models\CartItem;
use App\Models\Commune;
use App\Models\Coupon;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponApiTest extends TestCase
{
    use RefreshDatabase;

    private $product;
    private $productSize;
    private $commune;
    private $coupon;
    private $sessionId = 'coupon-session-123';

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::factory()->create(['price' => 50000.00]);
        $this->productSize = $this->product->sizes[0];
        $this->productSize->update(['stock' => 10]);

        $this->commune = Commune::create([
            'name' => 'Las Condes',
            'shipping_price' => 4000.00,
        ]);

        $this->coupon = Coupon::create([
            'code' => 'DESCUENTO10',
            'type' => 'product',
            'discount_type' => 'percentage',
            'discount_value' => 10.0,
            'min_purchase_amount' => 0.0,
            'start_date' => Carbon::now()->subDays(1),
            'end_date' => Carbon::now()->addDays(30),
        ]);
    }

    public function test_can_validate_coupon_successfully(): void
    {
        CartItem::create([
            'session_id' => $this->sessionId,
            'product_id' => $this->product->id,
            'product_size_id' => $this->productSize->id,
            'quantity' => 1,
        ]);

        $response = $this->postJson('/api/coupons/validate', [
            'code' => 'descuento10', // Test lowercase input
            'session_id' => $this->sessionId,
            'commune_id' => $this->commune->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'valid' => true,
            'coupon' => [
                'code' => 'DESCUENTO10',
                'type' => 'product',
                'discountType' => 'percentage',
                'discountValue' => 10.0,
            ],
            'discountAmount' => 5000.0, // 10% of 50000
            'appliedTo' => 'product',
        ]);
    }

    public function test_validate_coupon_fails_for_invalid_code(): void
    {
        CartItem::create([
            'session_id' => $this->sessionId,
            'product_id' => $this->product->id,
            'product_size_id' => $this->productSize->id,
            'quantity' => 1,
        ]);

        $response = $this->postJson('/api/coupons/validate', [
            'code' => 'INEXISTENTE',
            'session_id' => $this->sessionId,
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'El cupón ingresado no existe o no es válido.']);
    }

    public function test_validate_coupon_fails_for_expired_coupon(): void
    {
        $expired = Coupon::create([
            'code' => 'OLDCODE',
            'type' => 'product',
            'discount_type' => 'percentage',
            'discount_value' => 20.0,
            'start_date' => Carbon::now()->subDays(30),
            'end_date' => Carbon::now()->subDays(1),
        ]);

        CartItem::create([
            'session_id' => $this->sessionId,
            'product_id' => $this->product->id,
            'product_size_id' => $this->productSize->id,
            'quantity' => 1,
        ]);

        $response = $this->postJson('/api/coupons/validate', [
            'code' => 'OLDCODE',
            'session_id' => $this->sessionId,
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['message' => 'El cupón ha expirado.']);
    }

    public function test_can_create_order_with_coupon_applied(): void
    {
        CartItem::create([
            'session_id' => $this->sessionId,
            'product_id' => $this->product->id,
            'product_size_id' => $this->productSize->id,
            'quantity' => 1, // $50.000 subtotal
        ]);

        // Total should be: 50.000 (subtotal) + 4.000 (shipping) - 5.000 (10% discount) = 49.000
        $response = $this->withHeader('X-Session-ID', $this->sessionId)
            ->postJson('/api/orders', [
                'name' => 'Carlos López',
                'email' => 'carlos@example.com',
                'address' => 'Av Vitacura 1234',
                'commune_id' => $this->commune->id,
                'coupon_code' => 'DESCUENTO10',
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'data' => [
                'subtotal' => 50000.0,
                'shippingCost' => 4000.0,
                'discountAmount' => 5000.0,
                'total' => 49000.0,
                'coupon' => [
                    'code' => 'DESCUENTO10',
                ],
            ],
        ]);

        $orderCode = $response->json('data.code');
        $this->assertDatabaseHas('orders', [
            'code' => $orderCode,
            'coupon_id' => $this->coupon->id,
            'discount_amount' => 5000.00,
            'total' => 49000.00,
        ]);
    }
}
