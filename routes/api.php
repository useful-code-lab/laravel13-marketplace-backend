<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\WebhookController;

Route::post('/products', [ProductController::class, 'store']);
Route::post('/orders', [OrderController::class, 'store']);


Route::post('/webhooks/payment', [WebhookController::class, 'handlePaymentGateway']);


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
