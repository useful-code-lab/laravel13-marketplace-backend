<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\AuthController;

Route::post('/products', [ProductController::class, 'store']);
Route::post('/orders', [OrderController::class, 'store']);


Route::post('/webhooks/payment', [WebhookController::class, 'handlePaymentGateway']);

// Публичные маршруты (Доступны всем)
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/webhooks/payment', [WebhookController::class, 'handlePaymentGateway']);

// Защищенные маршруты (Требуют валидный Bearer Token в заголовке Authorization)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::post('/orders', [OrderController::class, 'store']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
