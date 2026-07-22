<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommuneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'shippingPrice' => (float) $this->shipping_price,
            'daysToDeliver' => (int) $this->days_to_deliver,
        ];
    }
}
