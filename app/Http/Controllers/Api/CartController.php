<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetCartRequest;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Http\Requests\DeleteCartRequest;
use App\Models\CartItem;
use App\Models\ProductSize;
use App\Http\Resources\CartItemResource;

class CartController extends Controller
{
    public function index(GetCartRequest $request)
    {
        $items = CartItem::where('session_id', $request->validated('session_id'))
            ->with(['product.sizes', 'product.category', 'productSize'])
            ->get();

        return CartItemResource::collection($items);
    }

    public function store(AddToCartRequest $request)
    {
        $sessionId = $request->validated('session_id');
        $productId = $request->validated('product_id');
        $productSizeId = $request->validated('product_size_id');
        $quantity = $request->validated('quantity');

        $item = CartItem::where('session_id', $sessionId)
            ->where('product_id', $productId)
            ->where('product_size_id', $productSizeId)
            ->first();

        if ($item) {
            $item->increment('quantity', $quantity);
        } else {
            $item = CartItem::create([
                'session_id' => $sessionId,
                'product_id' => $productId,
                'product_size_id' => $productSizeId,
                'quantity' => $quantity,
            ]);
        }

        $item->load(['product.sizes', 'product.category', 'productSize']);

        return new CartItemResource($item);
    }

    public function update(UpdateCartRequest $request, CartItem $cartItem)
    {
        $sessionId = $request->validated('session_id');
        if ($cartItem->session_id !== $sessionId) {
            abort(404);
        }

        $quantity = $request->validated('quantity');

        if ($quantity <= 0) {
            $cartItem->delete();
            return response()->noContent();
        }

        $cartItem->update(['quantity' => $quantity]);
        $cartItem->load(['product.sizes', 'product.category', 'productSize']);

        return new CartItemResource($cartItem);
    }

    public function destroy(DeleteCartRequest $request, CartItem $cartItem)
    {
        if ($cartItem->session_id !== $request->validated('session_id')) {
            abort(404);
        }

        $cartItem->delete();

        return response()->noContent();
    }
}
