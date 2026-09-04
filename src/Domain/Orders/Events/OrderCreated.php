<?php

namespace Domain\Orders\Events;

use Domain\Orders\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Событие, сигнализирующее о том, что заказ успешно оформлен в БД
 */
class OrderCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels; // Автоматически сериализует Eloquent-модель для очередей

    public function __construct(
        public Order $order
    ) {}
}
