<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ProductSize;
use App\Models\CartItem;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'session_id' => $this->header('X-Session-ID'),
        ]);
    }

    public function rules(): array
    {
        return [
            'session_id' => 'required|string',
            'product_id' => 'required|exists:products,id',
            'product_size_id' => [
                'required',
                'exists:product_sizes,id',
                function ($attribute, $value, $fail) {
                    $sizeObj = ProductSize::find($value);
                    if (!$sizeObj) return;

                    if ($sizeObj->stock <= 0) {
                        $fail('Producto sin existencias en esta talla.');
                        return;
                    }

                    $requestedQty = (int) $this->input('quantity', 1);
                    $sessionId = $this->input('session_id');
                    $productId = $this->input('product_id');

                    $existingItem = CartItem::where('session_id', $sessionId)
                        ->where('product_id', $productId)
                        ->where('product_size_id', $value)
                        ->first();

                    $existingQty = $existingItem ? $existingItem->quantity : 0;
                    $totalQty = $existingQty + $requestedQty;

                    if ($totalQty > $sizeObj->stock) {
                        $fail("No hay suficiente stock disponible (disponible: {$sizeObj->stock}).");
                    }
                },
            ],
            'quantity' => 'required|integer|min:1',
        ];
    }
}
