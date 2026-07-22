<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\CartItem;

class StoreOrderRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:255',
            'commune_id' => 'required|exists:communes,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $sessionId = $this->input('session_id');
            if (!$sessionId) return;

            $cartItems = CartItem::with(['product', 'productSize'])
                ->where('session_id', $sessionId)
                ->get();

            if ($cartItems->isEmpty()) {
                $validator->errors()->add('cart', 'El carrito está vacío.');
                return;
            }

            foreach ($cartItems as $item) {
                if (!$item->productSize) {
                    $validator->errors()->add('cart', "El producto {$item->product->name} no tiene una talla válida asociada.");
                    return;
                }

                if ($item->quantity > $item->productSize->stock) {
                    $validator->errors()->add('stock', "No hay suficiente stock para {$item->product->name} en talla {$item->productSize->size}. Stock disponible: {$item->productSize->stock}.");
                    return;
                }
            }
        });
    }
}
