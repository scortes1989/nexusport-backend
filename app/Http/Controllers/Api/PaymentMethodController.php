<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentMethodRequest;
use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        $methods = $request->user()
            ->paymentMethods()
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return PaymentMethodResource::collection($methods);
    }

    public function store(StorePaymentMethodRequest $request)
    {
        $user = $request->user();
        $isFirst = $user->paymentMethods()->count() === 0;
        $isDefault = $request->boolean('is_default') || $isFirst;

        if ($isDefault) {
            $user->paymentMethods()->update(['is_default' => false]);
        }

        $rawCardNumber = $request->validated('card_number');
        $lastFour = $rawCardNumber
            ? substr(preg_replace('/\D/', '', $rawCardNumber), -4)
            : $request->validated('last_four');

        $method = $user->paymentMethods()->create([
            'card_brand' => $request->validated('card_brand'),
            'last_four' => $lastFour,
            'cardholder_name' => $request->validated('cardholder_name'),
            'expiration_month' => $request->validated('expiration_month'),
            'expiration_year' => $request->validated('expiration_year'),
            'is_default' => $isDefault,
        ]);

        return new PaymentMethodResource($method);
    }

    public function show(Request $request, PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->user_id !== $request->user()->id) {
            abort(403, 'No tienes permiso para acceder a este medio de pago.');
        }

        return new PaymentMethodResource($paymentMethod);
    }

    public function destroy(Request $request, PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->user_id !== $request->user()->id) {
            abort(403, 'No tienes permiso para eliminar este medio de pago.');
        }

        $wasDefault = $paymentMethod->is_default;
        $paymentMethod->delete();

        if ($wasDefault) {
            $next = $request->user()->paymentMethods()->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        return response()->noContent();
    }

    public function setDefault(Request $request, PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->user_id !== $request->user()->id) {
            abort(403, 'No tienes permiso para modificar este medio de pago.');
        }

        $request->user()->paymentMethods()->where('id', '!=', $paymentMethod->id)->update(['is_default' => false]);
        $paymentMethod->update(['is_default' => true]);

        return new PaymentMethodResource($paymentMethod);
    }
}
