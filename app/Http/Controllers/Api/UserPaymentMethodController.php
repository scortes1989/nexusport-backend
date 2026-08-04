<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserPaymentMethodRequest;
use App\Http\Resources\UserPaymentMethodResource;
use App\Models\UserPaymentMethod;
use Illuminate\Http\Request;

class UserPaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        $methods = $request->user()
            ->userPaymentMethods()
            ->with('paymentMethod')
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return UserPaymentMethodResource::collection($methods);
    }

    public function store(StoreUserPaymentMethodRequest $request)
    {
        $user = $request->user();
        $isFirst = $user->userPaymentMethods()->count() === 0;
        $isDefault = $request->boolean('is_default') || $isFirst;

        if ($isDefault) {
            $user->userPaymentMethods()->update(['is_default' => false]);
        }

        $rawCardNumber = $request->validated('card_number');
        $lastFour = $rawCardNumber
            ? substr(preg_replace('/\D/', '', $rawCardNumber), -4)
            : $request->validated('last_four');

        $method = $user->userPaymentMethods()->create([
            'payment_method_id' => $request->validated('payment_method_id'),
            'card_brand' => $request->validated('card_brand'),
            'last_four' => $lastFour,
            'cardholder_name' => $request->validated('cardholder_name'),
            'expiration_month' => $request->validated('expiration_month'),
            'expiration_year' => $request->validated('expiration_year'),
            'is_default' => $isDefault,
        ]);

        return new UserPaymentMethodResource($method->load('paymentMethod'));
    }

    public function show(Request $request, UserPaymentMethod $userPaymentMethod)
    {
        if ($userPaymentMethod->user_id !== $request->user()->id) {
            abort(403, 'No tienes permiso para acceder a este medio de pago.');
        }

        return new UserPaymentMethodResource($userPaymentMethod->load('paymentMethod'));
    }

    public function destroy(Request $request, UserPaymentMethod $userPaymentMethod)
    {
        if ($userPaymentMethod->user_id !== $request->user()->id) {
            abort(403, 'No tienes permiso para eliminar este medio de pago.');
        }

        $wasDefault = $userPaymentMethod->is_default;
        $userPaymentMethod->delete();

        if ($wasDefault) {
            $next = $request->user()->userPaymentMethods()->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        return response()->noContent();
    }

    public function setDefault(Request $request, UserPaymentMethod $userPaymentMethod)
    {
        if ($userPaymentMethod->user_id !== $request->user()->id) {
            abort(403, 'No tienes permiso para modificar este medio de pago.');
        }

        $request->user()->userPaymentMethods()->where('id', '!=', $userPaymentMethod->id)->update(['is_default' => false]);
        $userPaymentMethod->update(['is_default' => true]);

        return new UserPaymentMethodResource($userPaymentMethod->load('paymentMethod'));
    }
}
