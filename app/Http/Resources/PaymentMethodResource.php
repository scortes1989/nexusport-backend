<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->user_id,
            'cardBrand' => $this->card_brand,
            'lastFour' => $this->last_four,
            'cardholderName' => $this->cardholder_name,
            'expirationMonth' => $this->expiration_month,
            'expirationYear' => $this->expiration_year,
            'isDefault' => (bool) $this->is_default,
            'createdAt' => $this->created_at,
        ];
    }
}
