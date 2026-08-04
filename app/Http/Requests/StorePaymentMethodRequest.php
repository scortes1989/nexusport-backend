<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'card_brand' => ['required', 'string', 'max:50'],
            'card_number' => ['nullable', 'string', 'min:13', 'max:25'],
            'last_four' => ['required_without:card_number', 'nullable', 'string'],
            'cardholder_name' => ['required', 'string', 'max:255'],
            'expiration_month' => ['required', 'string', 'max:2'],
            'expiration_year' => ['required', 'string', 'max:4'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}
