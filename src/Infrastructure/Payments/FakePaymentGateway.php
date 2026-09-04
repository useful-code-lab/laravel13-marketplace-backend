<?php

namespace Infrastructure\Payments;

use Domain\Orders\Models\Order;
use Illuminate\Support\Facades\Http;

class FakePaymentGateway implements PaymentGatewayInterface
{
    public function charge(Order $order): string
    {
       
        // Симулируем генерацию платежной ссылки банком для нашего UUID заказа
        return "https://fake-payment-gateway.com{$order->id}?amount={$order->total_cents}";
    }
}
