<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Http\Resources\PaymentMethodResource;

class PaymentMethodController extends Controller
{
    public function index()
    {
        return PaymentMethodResource::collection(
            PaymentMethod::where('is_active', true)->get()
        );
    }
}
