<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => (int) $this->quantity,
            'productSizeId' => (int) $this->product_size_id,
            'size' => $this->productSize ? $this->productSize->size : '',
            'product' => new ProductResource($this->product),
            'productSize' => new ProductSizeResource($this->productSize),
        ];
    }
}
