<?php

namespace Domain\Orders\Actions;

use Domain\Orders\Models\Order;
use Domain\Shared\Exceptions\BusinessException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConfirmOrderPaymentAction
{
    /**
     * Переводит заказ в статус 'paid' с защитой от повторных вызовов
     */
    public function execute(string $orderId): Order
    {
        return DB::transaction(function () use ($orderId) {
            // Блокируем строку заказа для апдейта
            /** @var Order $order */
            $order = Order::where('id', $orderId)->lockForUpdate()->firstOrFail();

            // Паттерн Идемпотентности: если заказ уже оплачен, просто возвращаем его (Idempotent response)
            if ($order->status === 'paid') {
                Log::warning("Webhook проигнорирован: Заказ {$orderId} уже имеет статус 'paid'.");
                return $order;
            }

            // Если заказ был отменен, мы не имеем права принимать по нему оплату
            if ($order->status === 'cancelled') {
                throw new BusinessException("Невозможно оплатить отмененный заказ {$orderId}.");
            }

            // Меняем статус
            $order->update(['status' => 'paid']);

            Log::info("Заказ {$orderId} успешно оплачен через Webhook.");

            // Здесь в будущем можно выстрелить новое событие event(new OrderPaid($order));

            return $order;
        });
    }
}
