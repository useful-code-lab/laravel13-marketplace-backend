<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use Domain\Orders\Actions\CreateOrderAction;
use Domain\Orders\DataTransferObjects\OrderData;
use Infrastructure\Payments\PaymentGatewayInterface;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    // Внедряем интерфейс шлюза напрямую в метод контроллера
    public function store(
        StoreOrderRequest $request, 
        CreateOrderAction $action,
        PaymentGatewayInterface $paymentGateway
    ): JsonResponse {
        
        $dto = new OrderData(
            items: $request->input('items')
        );

        // 1. Создаем заказ и списываем stock
        $order = $action->execute($dto);

        // 2. Генерируем платежную ссылку через наш абстрактный шлюз
        $paymentUrl = $paymentGateway->charge($order);

        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $order->id,
                'total_cents' => $order->total_cents,
                'status' => $order->status,
                'payment_url' => $paymentUrl, // Пользователь фронтенда перейдет по этой ссылке для оплаты
            ]
        ], Response::HTTP_CREATED);
    }
}
