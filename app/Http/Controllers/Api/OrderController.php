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
use App\Http\Resources\OrderResource;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request)
    {
        $sessionId = $request->validated('session_id');
        $cartItems = CartItem::with(['product', 'productSize'])->where('session_id', $sessionId)->get();

        try {
            $order = DB::transaction(function () use ($request, $cartItems, $sessionId) {
                $subtotal = (float) $cartItems->sum(fn ($item) => $item->quantity * $item->product->price);

                $commune = Commune::findOrFail($request->commune_id);
                $shippingCost = (float) $commune->shipping_price;
                $total = $subtotal + $shippingCost;

                $dates = $commune->calculateDeliveryDates();

                $order = Order::create([
                    'session_id' => $sessionId,
                    'customer_name' => $request->name,
                    'customer_email' => $request->email,
                    'shipping_address' => $request->address,
                    'commune_id' => $request->commune_id,
                    'shipping_cost' => $shippingCost,
                    'subtotal' => $subtotal,
                    'total' => $total,
                    'status' => 'paid',
                    'payment_method_id' => $request->payment_method_id,
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
                    'payment_method_id' => $request->payment_method_id,
                    'amount' => $total,
                    'status' => 'completed',
                    'transaction_id' => 'TX-' . strtoupper(Str::random(12)),
                ]);

                CartItem::where('session_id', $sessionId)->delete();

                return $order;
            });

            $order->load(['items.product', 'items.productSize', 'commune', 'paymentMethod', 'payment']);

            return new OrderResource($order);

        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Ocurrió un error al procesar el pago.', 'error' => $e->getMessage()], 500);
        }
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

        $order->load(['items.product', 'items.productSize', 'commune', 'paymentMethod', 'payment']);
        return new OrderResource($order);
    }
}
