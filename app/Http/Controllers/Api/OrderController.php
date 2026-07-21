<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:255',
            'commune_id' => 'required|exists:communes,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $sessionId = $request->header('X-Session-ID');
        if (!$sessionId) {
            return response()->json(['message' => 'Session ID header is missing.'], 400);
        }

        // Get the cart items
        $cartItems = CartItem::with(['product', 'productSize'])->where('session_id', $sessionId)->get();
        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'El carrito está vacío.'], 400);
        }

        try {
            $order = DB::transaction(function () use ($request, $cartItems, $sessionId) {
                $subtotal = 0;

                // 1. Verify stock and calculate subtotal
                foreach ($cartItems as $item) {
                    if (!$item->productSize) {
                        throw ValidationException::withMessages([
                            'cart' => "El producto {$item->product->name} no tiene una talla válida asociada."
                        ]);
                    }
                    if ($item->quantity > $item->productSize->stock) {
                        throw ValidationException::withMessages([
                            'stock' => "No hay suficiente stock para {$item->product->name} en talla {$item->productSize->size}. Stock disponible: {$item->productSize->stock}."
                        ]);
                    }
                    $subtotal += $item->quantity * $item->product->price;
                }

                // 2. Fetch commune for shipping cost
                $commune = Commune::findOrFail($request->commune_id);
                $shippingCost = (float) $commune->shipping_price;
                $total = $subtotal + $shippingCost;

                // Calculate dispatch date (today, or next business day if weekend)
                $dispatchDate = now();
                if ($dispatchDate->isWeekend()) {
                    while ($dispatchDate->isWeekend()) {
                        $dispatchDate->addDay();
                    }
                }

                // Calculate delivery date skipping weekends starting from dispatch date
                $deliveryDate = clone $dispatchDate;
                $addedDays = 0;
                while ($addedDays < $commune->days_to_deliver) {
                    $deliveryDate->addDay();
                    if (!$deliveryDate->isWeekend()) {
                        $addedDays++;
                    }
                }

                // Generate unique order code
                do {
                    $code = 'ORD-' . strtoupper(Str::random(8));
                } while (Order::where('code', $code)->exists());

                // 3. Create the Order
                $order = Order::create([
                    'code' => $code,
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
                    'estimated_dispatch_date' => $dispatchDate->toDateString(),
                    'estimated_delivery_date' => $deliveryDate->toDateString(),
                ]);

                // 4. Create OrderItems & deduct stock
                foreach ($cartItems as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'product_size_id' => $item->product_size_id,
                        'quantity' => $item->quantity,
                        'price' => $item->product->price,
                    ]);

                    // Deduct stock
                    $item->productSize->decrement('stock', $item->quantity);
                }

                // 5. Create Payment record
                Payment::create([
                    'order_id' => $order->id,
                    'payment_method_id' => $request->payment_method_id,
                    'amount' => $total,
                    'status' => 'completed',
                    'transaction_id' => 'TX-' . strtoupper(Str::random(12)),
                ]);

                // 6. Clear session cart
                CartItem::where('session_id', $sessionId)->delete();

                return $order;
            });

            // Load relations for the resource mapping
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
