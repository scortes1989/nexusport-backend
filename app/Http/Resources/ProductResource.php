<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'longDescription' => $this->long_description,
            'price' => (float) $this->price,
            'gradient' => $this->gradient,
            'rating' => (float) $this->rating,
            'reviewsCount' => (int) $this->reviews_count,
            'specs' => $this->specs,
            'featured' => (bool) $this->featured,
            'stock' => (int) $this->sizes->sum('stock'),
            'imageUrl' => $this->image_url,
            'category' => new CategoryResource($this->category),
            'sizes' => ProductSizeResource::collection($this->sizes),
        ];
    }



}
