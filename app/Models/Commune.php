<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commune extends Model
{
    protected $guarded = [];

    public function calculateDeliveryDates(): array
    {
        $dispatchDate = now();
        if ($dispatchDate->isWeekend()) {
            while ($dispatchDate->isWeekend()) {
                $dispatchDate->addDay();
            }
        }

        $deliveryDate = clone $dispatchDate;
        $addedDays = 0;
        while ($addedDays < $this->days_to_deliver) {
            $deliveryDate->addDay();
            if (!$deliveryDate->isWeekend()) {
                $addedDays++;
            }
        }

        return [
            'estimated_dispatch_date' => $dispatchDate->toDateString(),
            'estimated_delivery_date' => $deliveryDate->toDateString(),
        ];
    }
}
