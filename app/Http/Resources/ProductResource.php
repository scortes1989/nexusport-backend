<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'longDescription' => $this->long_description,
            'price' => (float) $this->price,
            'category' => $this->category ? $this->category->name : 'Accesorios',
            'gradient' => $this->gradient,
            'rating' => (float) $this->rating,
            'reviewsCount' => (int) $this->reviews_count,
            'specs' => $this->specs,
            'featured' => (bool) $this->featured,
            'stock' => (int) $this->stock,
        ];
    }

}
