<?php

namespace Domain\Orders\Listeners;

use Domain\Orders\Events\OrderCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmationNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Время ожидания перед повторной попыткой при сбое (в секундах)
     */
    public int $backoff = 60;

    /**
     * Количество попыток выполнить задачу при сбоях сети/API
     */
    public int $tries = 3;

    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        // В реальном Enterprise приложении здесь будет вызов Mail::to(...)->send(...)
        // Для демонстрации Senior-логирования пишем в системный лог
        Log::info("Асинхронная задача: Отправлено уведомление клиенту по заказу UUID: {$order->id}. Сумма: {$order->total_cents} копеек.");
    }
}
