<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string'],
            'session_id' => ['required', 'string'],
            'commune_id' => ['nullable', 'exists:communes,id'],
        ];
    }
}
