<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Domain\Orders\Events\OrderCreated;
use Domain\Orders\Listeners\SendOrderConfirmationNotification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
