<?php

namespace Infrastructure\Payments;

use Domain\Orders\Models\Order;

interface PaymentGatewayInterface
{
    /**
     * Инициирует оплату заказа во внешней системе
     * @return string URL-адрес платежной страницы для перенаправления пользователя
     */
    public function charge(Order $order): string;
}
