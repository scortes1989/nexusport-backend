<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'isDefault' => (bool) $this->is_default,
            'createdAt' => $this->created_at,
            'commune' => new CommuneResource($this->commune),
        ];
    }
}
