<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'code' => $this->code,
            'customerName' => $this->customer_name,
            'customerEmail' => $this->customer_email,
            'shippingAddress' => $this->shipping_address,
            'communeName' => $this->commune ? $this->commune->name : null,
            'shippingCost' => (float) $this->shipping_cost,
            'subtotal' => (float) $this->subtotal,
            'total' => (float) $this->total,
            'status' => $this->status,
            'paymentMethodName' => $this->paymentMethod ? $this->paymentMethod->name : null,
            'transactionId' => $this->payment ? $this->payment->transaction_id : null,
            'createdAt' => $this->created_at ? $this->created_at->toISOString() : null,
            'estimatedDispatchDate' => $this->estimated_dispatch_date ? $this->estimated_dispatch_date->toDateString() : null,
            'estimatedDeliveryDate' => $this->estimated_delivery_date ? $this->estimated_delivery_date->toDateString() : null,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
