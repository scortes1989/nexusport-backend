<?php

namespace Tests\Unit;

use App\Models\Commune;
use App\Models\Coupon;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_coupon_validation_date_ranges(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 4, 12, 0, 0));

        $validCoupon = Coupon::create([
            'code' => 'ACTIVE10',
            'type' => 'product',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'start_date' => Carbon::create(2026, 8, 1),
            'end_date' => Carbon::create(2026, 8, 10),
        ]);

        $expiredCoupon = Coupon::create([
            'code' => 'EXPIRED',
            'type' => 'product',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'start_date' => Carbon::create(2026, 7, 1),
            'end_date' => Carbon::create(2026, 8, 1),
        ]);

        $futureCoupon = Coupon::create([
            'code' => 'FUTURE',
            'type' => 'product',
            'discount_type' => 'percentage',
            'discount_value' => 15,
            'start_date' => Carbon::create(2026, 8, 10),
            'end_date' => Carbon::create(2026, 8, 20),
        ]);

        $this->assertNull($validCoupon->checkValidity(50000));
        $this->assertEquals('El cupón ha expirado.', $expiredCoupon->checkValidity(50000));
        $this->assertEquals('El cupón aún no está vigente.', $futureCoupon->checkValidity(50000));

        Carbon::setTestNow();
    }

    public function test_coupon_soft_deletes(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 4, 12, 0, 0));

        $coupon = Coupon::create([
            'code' => 'DELETED10',
            'type' => 'product',
            'discount_type' => 'percentage',
            'discount_value' => 10,
            'start_date' => Carbon::create(2026, 8, 1),
            'end_date' => Carbon::create(2026, 8, 10),
        ]);

        $coupon->delete(); // Soft delete

        $this->assertTrue($coupon->trashed());
        $this->assertEquals('El cupón no está disponible.', $coupon->checkValidity(50000));

        Carbon::setTestNow();
    }

    public function test_coupon_usage_limit_based_on_orders(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 4, 12, 0, 0));

        $commune = Commune::create(['name' => 'Providencia', 'shipping_price' => 3000]);

        $coupon = Coupon::create([
            'code' => 'ONETIME',
            'type' => 'product',
            'discount_type' => 'fixed',
            'discount_value' => 5000,
            'usage_limit' => 1,
            'start_date' => Carbon::create(2026, 8, 1),
            'end_date' => Carbon::create(2026, 8, 10),
        ]);

        $this->assertNull($coupon->checkValidity(50000));

        // Create 1 order with this coupon
        Order::create([
            'session_id' => 'session-1',
            'customer_name' => 'Test',
            'customer_email' => 'test@example.com',
            'shipping_address' => 'Addr',
            'commune_id' => $commune->id,
            'coupon_id' => $coupon->id,
            'discount_amount' => 5000,
            'shipping_cost' => 3000,
            'subtotal' => 50000,
            'total' => 48000,
        ]);

        $this->assertEquals('El cupón ha alcanzado su límite máximo de usos.', $coupon->checkValidity(50000));

        Carbon::setTestNow();
    }

    public function test_product_percentage_discount_calculation(): void
    {
        $coupon = new Coupon([
            'type' => 'product',
            'discount_type' => 'percentage',
            'discount_value' => 10.0, // 10%
            'max_discount_amount' => 4000.0, // cap at $4000
        ]);

        // 10% of $30.000 is $3.000 (under $4000 cap)
        $this->assertEquals(3000.0, $coupon->calculateDiscount(30000.0, 3000.0));

        // 10% of $60.000 is $6.000, but capped at $4.000
        $this->assertEquals(4000.0, $coupon->calculateDiscount(60000.0, 3000.0));
    }

    public function test_product_fixed_discount_calculation(): void
    {
        $coupon = new Coupon([
            'type' => 'product',
            'discount_type' => 'fixed',
            'discount_value' => 5000.0,
        ]);

        // Subtotal $50.000 -> discount $5.000
        $this->assertEquals(5000.0, $coupon->calculateDiscount(50000.0, 3000.0));

        // Subtotal $3.000 -> discount capped at subtotal $3.000
        $this->assertEquals(3000.0, $coupon->calculateDiscount(3000.0, 3000.0));
    }

    public function test_shipping_discount_calculation(): void
    {
        $shippingCoupon = new Coupon([
            'type' => 'shipping',
            'discount_type' => 'percentage',
            'discount_value' => 100.0, // 100% free shipping
        ]);

        // Shipping cost $3.500 -> discount $3.500
        $this->assertEquals(3500.0, $shippingCoupon->calculateDiscount(50000.0, 3500.0));
    }
}
