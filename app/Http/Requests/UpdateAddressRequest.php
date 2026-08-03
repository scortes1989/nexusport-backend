<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        $address = $this->route('address');
        if (is_string($address) || is_numeric($address)) {
            $address = \App\Models\Address::find($address);
        }
        return $address instanceof \App\Models\Address && $address->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'address' => ['sometimes', 'required', 'string', 'max:500'],
            'commune_id' => ['sometimes', 'required', 'integer', 'exists:communes,id'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }
}
