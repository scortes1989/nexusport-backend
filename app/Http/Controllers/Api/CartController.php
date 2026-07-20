<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Product;
use App\Http\Resources\ProductResource;

class CartController extends Controller
{
    private function getSessionId(Request $request): string
    {
        $sessionId = $request->header('X-Session-ID');
        if (empty($sessionId)) {
            abort(400, 'Falta la cabecera requerida: X-Session-ID');
        }
        return $sessionId;
    }

    public function index(Request $request)
    {
        $sessionId = $this->getSessionId($request);
        $items = CartItem::where('session_id', $sessionId)
            ->with(['product.sizes', 'product.category', 'productSize'])
            ->get();

        return response()->json([
            'cart' => $items->map(function ($item) {
                return [
                    'product' => new ProductResource($item->product),
                    'productSizeId' => (int) $item->product_size_id,
                    'size' => $item->productSize ? $item->productSize->size : '',
                    'quantity' => (int) $item->quantity,
                ];
            })
        ]);
    }

    public function store(Request $request)
    {
        $sessionId = $this->getSessionId($request);
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_size_id' => 'required|exists:product_sizes,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $productId = $request->input('product_id');
        $productSizeId = $request->input('product_size_id');
        $quantity = $request->input('quantity');

        // Check stock limit for that specific size in backend
        $sizeObj = \App\Models\ProductSize::findOrFail($productSizeId);
        $maxStock = $sizeObj->stock;

        if ($maxStock <= 0) {
            return response()->json(['message' => 'Producto sin existencias en esta talla.'], 422);
        }

        $item = CartItem::where('session_id', $sessionId)
            ->where('product_id', $productId)
            ->where('product_size_id', $productSizeId)
            ->first();

        if ($item) {
            $newQty = min($item->quantity + $quantity, $maxStock);
            $item->update(['quantity' => $newQty]);
        } else {
            CartItem::create([
                'session_id' => $sessionId,
                'product_id' => $productId,
                'product_size_id' => $productSizeId,
                'quantity' => min($quantity, $maxStock),
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function update(Request $request)
    {
        $sessionId = $this->getSessionId($request);
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_size_id' => 'required|exists:product_sizes,id',
            'quantity' => 'required|integer',
        ]);

        $productId = $request->input('product_id');
        $productSizeId = $request->input('product_size_id');
        $quantity = $request->input('quantity');

        $item = CartItem::where('session_id', $sessionId)
            ->where('product_id', $productId)
            ->where('product_size_id', $productSizeId)
            ->first();

        if ($item) {
            if ($quantity <= 0) {
                $item->delete();
            } else {
                $sizeObj = \App\Models\ProductSize::findOrFail($productSizeId);
                $maxStock = $sizeObj->stock;
                $item->update(['quantity' => min($quantity, $maxStock)]);
            }
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        $sessionId = $this->getSessionId($request);
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_size_id' => 'required|exists:product_sizes,id',
        ]);

        $productId = $request->input('product_id');
        $productSizeId = $request->input('product_size_id');

        CartItem::where('session_id', $sessionId)
            ->where('product_id', $productId)
            ->where('product_size_id', $productSizeId)
            ->delete();

        return response()->json(['success' => true]);
    }
}
