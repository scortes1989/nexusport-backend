<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartRequest extends FormRequest
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
            'quantity' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $cartItem = $this->route('cartItem');
                    if ($cartItem && $value > 0) {
                        $maxStock = $cartItem->productSize ? $cartItem->productSize->stock : 0;
                        if ($value > $maxStock) {
                            $fail("No hay suficiente stock disponible (disponible: {$maxStock}).");
                        }
                    }
                },
            ],
        ];
    }
}
