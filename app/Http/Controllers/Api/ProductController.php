<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    public function index()
    {
        return ProductResource::collection(Product::with(['category', 'sizes'])->get());
    }

    public function show(Product $product)
    {
        $product->load(['category', 'sizes']);
        return new ProductResource($product);
    }
}



