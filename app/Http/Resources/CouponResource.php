<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type,
            'discountType' => $this->discount_type,
            'discountValue' => (float) $this->discount_value,
            'minPurchaseAmount' => (float) $this->min_purchase_amount,
            'maxDiscountAmount' => $this->max_discount_amount ? (float) $this->max_discount_amount : null,
            'startDate' => $this->start_date ? $this->start_date->toISOString() : null,
            'endDate' => $this->end_date ? $this->end_date->toISOString() : null,
        ];
    }
}
