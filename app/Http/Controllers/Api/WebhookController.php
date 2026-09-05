<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Domain\Orders\Actions\ConfirmOrderPaymentAction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    /**
     * Принимает POST-запрос от платежного шлюза
     */
    public function handlePaymentGateway(Request $request, ConfirmOrderPaymentAction $action): JsonResponse
    {
        // Senior-безопасность: проверяем секретный токен шлюза из заголовков
        // В реальном проекте это выносится в Middleware
        $signature = $request->header('X-Payment-Signature');
        if ($signature !== 'secret_webhook_token_123') {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $orderId = $request->input('order_id');
        $event = $request->input('event');

        // Обрабатываем только событие успешной оплаты
        if ($event === 'payment.succeeded' && $orderId) {
            $action->execute($orderId);
        }

        // Шлюзы требуют строго статус 200 OK в ответ, иначе они будут слать вебхук бесконечно
        return response()->json(['success' => true], Response::HTTP_OK);
    }
}
