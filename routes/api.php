<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CommuneController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RegisterController;

// Auth Routes
Route::post('/register', RegisterController::class);
Route::post('/login', [AuthController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy']);
    Route::get('/me', [AuthController::class, 'show']);
    Route::get('/my-orders', [OrderController::class, 'myOrders']);
});

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart', [CartController::class, 'store']);
Route::put('/cart/{cartItem}', [CartController::class, 'update']);
Route::delete('/cart/{cartItem}', [CartController::class, 'destroy']);

Route::get('/communes', [CommuneController::class, 'index']);
Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{order}', [OrderController::class, 'show']);



