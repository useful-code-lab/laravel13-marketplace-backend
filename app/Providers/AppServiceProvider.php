<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Domain\Orders\Events\OrderCreated;
use Domain\Orders\Listeners\SendOrderConfirmationNotification;
use Infrastructure\Payments\PaymentGatewayInterface;
use Infrastructure\Payments\FakePaymentGateway;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Регистрируем наш платежный шлюз в контейнере зависимостей
        $this->app->bind(PaymentGatewayInterface::class, FakePaymentGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       // Ручное связывание доменного события и асинхронного листенера
        Event::listen(
            OrderCreated::class,
            SendOrderConfirmationNotification::class
        );
    }
}
