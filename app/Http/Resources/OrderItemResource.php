<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'productId' => $this->product_id,
            'productName' => $this->product ? $this->product->name : null,
            'productImageUrl' => $this->product ? $this->product->imageUrl : null,
            'size' => $this->productSize ? $this->productSize->size : null,
            'quantity' => (int) $this->quantity,
            'price' => (float) $this->price,
        ];
    }
}
