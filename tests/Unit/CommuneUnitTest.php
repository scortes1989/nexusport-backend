<?php

namespace Tests\Unit;

use App\Models\Commune;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommuneUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculate_delivery_dates_on_weekday(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 5, 10, 0, 0));

        $commune = Commune::create([
            'name' => 'Providencia',
            'shipping_price' => 3000.00,
            'days_to_deliver' => 2,
        ]);

        $dates = $commune->calculateDeliveryDates();

        $this->assertEquals('2026-08-05', $dates['estimated_dispatch_date']);
        $this->assertEquals('2026-08-07', $dates['estimated_delivery_date']);

        Carbon::setTestNow();
    }

    public function test_calculate_delivery_dates_skips_weekends(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 7, 10, 0, 0));

        $commune = Commune::create([
            'name' => 'Las Condes',
            'shipping_price' => 4000.00,
            'days_to_deliver' => 2,
        ]);

        $dates = $commune->calculateDeliveryDates();

        $this->assertEquals('2026-08-07', $dates['estimated_dispatch_date']);
        $this->assertEquals('2026-08-11', $dates['estimated_delivery_date']);

        Carbon::setTestNow();
    }

    public function test_calculate_delivery_dates_created_on_weekend(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 8, 14, 0, 0));

        $commune = Commune::create([
            'name' => 'Santiago Central',
            'shipping_price' => 2500.00,
            'days_to_deliver' => 1,
        ]);

        $dates = $commune->calculateDeliveryDates();

        $this->assertEquals('2026-08-10', $dates['estimated_dispatch_date']);
        $this->assertEquals('2026-08-11', $dates['estimated_delivery_date']);

        Carbon::setTestNow();
    }
}
