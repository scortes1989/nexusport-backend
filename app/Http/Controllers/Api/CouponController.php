<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateCouponRequest;
use App\Http\Resources\CouponResource;
use App\Models\CartItem;
use App\Models\Commune;
use App\Models\Coupon;

class CouponController extends Controller
{
    public function validateCoupon(ValidateCouponRequest $request)
    {
        $code = strtoupper(trim($request->validated('code')));
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json([
                'message' => 'El cupón ingresado no existe o no es válido.',
            ], 422);
        }

        $sessionId = $request->validated('session_id');
        $cartItems = CartItem::with('product')->where('session_id', $sessionId)->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => 'El carrito está vacío.',
            ], 422);
        }

        $subtotal = (float) $cartItems->sum(fn ($item) => $item->quantity * $item->product->price);

        $shippingCost = 0.0;
        if ($request->filled('commune_id')) {
            $commune = Commune::find($request->commune_id);
            if ($commune) {
                $shippingCost = (float) $commune->shipping_price;
            }
        }

        $validityError = $coupon->checkValidity($subtotal);
        if ($validityError) {
            return response()->json([
                'message' => $validityError,
            ], 422);
        }

        $discountAmount = $coupon->calculateDiscount($subtotal, $shippingCost);

        return response()->json([
            'valid' => true,
            'coupon' => new CouponResource($coupon),
            'discountAmount' => $discountAmount,
            'appliedTo' => $coupon->type,
        ]);
    }
}
