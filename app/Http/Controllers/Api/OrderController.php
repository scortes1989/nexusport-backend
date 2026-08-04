<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Commune;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Http\Resources\OrderResource;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request)
    {
        $sessionId = $request->validated('session_id');
        $cartItems = CartItem::with(['product', 'productSize'])->where('session_id', $sessionId)->get();
        $userId = $request->user('sanctum')?->id;

        try {
            $order = DB::transaction(function () use ($request, $cartItems, $sessionId, $userId) {
                $subtotal = (float) $cartItems->sum(fn ($item) => $item->quantity * $item->product->price);

                $commune = Commune::findOrFail($request->commune_id);
                $shippingCost = (float) $commune->shipping_price;

                $couponId = null;
                $discountAmount = 0.0;

                if ($request->filled('coupon_code')) {
                    $code = strtoupper(trim($request->input('coupon_code')));
                    $coupon = Coupon::where('code', $code)->first();

                    if (!$coupon) {
                        throw ValidationException::withMessages([
                            'coupon_code' => 'El cupón ingresado no existe.',
                        ]);
                    }

                    $validityError = $coupon->checkValidity($subtotal);
                    if ($validityError) {
                        throw ValidationException::withMessages([
                            'coupon_code' => $validityError,
                        ]);
                    }

                    $couponId = $coupon->id;
                    $discountAmount = $coupon->calculateDiscount($subtotal, $shippingCost);
                }

                $total = max(0.0, $subtotal + $shippingCost - $discountAmount);

                $dates = $commune->calculateDeliveryDates();

                $order = Order::create([
                    'session_id' => $sessionId,
                    'user_id' => $userId,
                    'customer_name' => $request->name,
                    'customer_email' => $request->email,
                    'shipping_address' => $request->address,
                    'commune_id' => $request->commune_id,
                    'coupon_id' => $couponId,
                    'discount_amount' => $discountAmount,
                    'shipping_cost' => $shippingCost,
                    'subtotal' => $subtotal,
                    'total' => $total,
                    'status' => 'paid',
                    'estimated_dispatch_date' => $dates['estimated_dispatch_date'],
                    'estimated_delivery_date' => $dates['estimated_delivery_date'],
                ]);

                foreach ($cartItems as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'product_size_id' => $item->product_size_id,
                        'quantity' => $item->quantity,
                        'price' => $item->product->price,
                    ]);

                    $item->productSize->decrement('stock', $item->quantity);
                }

                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $total,
                    'status' => 'completed',
                    'transaction_id' => 'TX-' . strtoupper(Str::random(12)),
                ]);

                CartItem::where('session_id', $sessionId)->delete();

                return $order;
            });

            $order->load(['items.product', 'items.productSize', 'commune', 'payment', 'coupon']);

            return new OrderResource($order);

        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Ocurrió un error al procesar el pago.', 'error' => $e->getMessage()], 500);
        }
    }

    public function myOrders(Request $request)
    {
        $orders = $request->user()
            ->orders()
            ->with(['items.product', 'items.productSize', 'commune', 'payment', 'coupon'])
            ->latest()
            ->get();

        return OrderResource::collection($orders);
    }

    public function show(string $id)
    {
        $query = Order::query();
        if (is_numeric($id)) {
            $query->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('code', $id);
            });
        } else {
            $query->where('code', $id);
        }
        $order = $query->firstOrFail();

        $order->load(['items.product', 'items.productSize', 'commune', 'payment', 'coupon']);
        return new OrderResource($order);
    }
}
