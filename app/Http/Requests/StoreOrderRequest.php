<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
}
