<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::create([
            'code' => 'NEXUS10',
            'type' => 'product',
            'discount_type' => 'percentage',
            'discount_value' => 10.00,
            'min_purchase_amount' => 0.00,
            'start_date' => Carbon::now()->subDays(1),
            'end_date' => Carbon::now()->addDays(30),
        ]);

        Coupon::create([
            'code' => 'ENVIOGRATIS',
            'type' => 'shipping',
            'discount_type' => 'percentage',
            'discount_value' => 100.00,
            'min_purchase_amount' => 0.00,
            'start_date' => Carbon::now()->subDays(1),
            'end_date' => Carbon::now()->addDays(30),
        ]);

        Coupon::create([
            'code' => 'DESCUENTO5000',
            'type' => 'product',
            'discount_type' => 'fixed',
            'discount_value' => 5000.00,
            'min_purchase_amount' => 20000.00,
            'start_date' => Carbon::now()->subDays(1),
            'end_date' => Carbon::now()->addDays(30),
        ]);

        Coupon::create([
            'code' => 'EXPIRADO',
            'type' => 'product',
            'discount_type' => 'percentage',
            'discount_value' => 20.00,
            'min_purchase_amount' => 0.00,
            'start_date' => Carbon::now()->subDays(30),
            'end_date' => Carbon::now()->subDays(1),
        ]);
    }
}
