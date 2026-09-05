<?php

namespace App\Filament\Widgets;

use Domain\Orders\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStats extends BaseWidget
{
    /**
     * Время кэширования виджета (в секундах), чтобы не нагружать БД при каждом обновлении страницы
     */
    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // 1. Считаем общую выручку (переводим копейки из БД в рубли/доллары)
        $totalRevenueCents = Order::where('status', 'paid')->sum('total_cents');
        $totalRevenue = round($totalRevenueCents / 100, 2);

        // 2. Считаем количество активных заказов, ожидающих оплаты
        $pendingOrdersCount = Order::where('status', 'pending')->count();

        // 3. Вычисляем средний чек оплаченного заказа
        $paidOrdersCount = Order::where('status', 'paid')->count();
        $averageCheck = $paidOrdersCount > 0
            ? round(($totalRevenueCents / $paidOrdersCount) / 100, 2)
            : 0;

        return [
            Stat::make('Общая выручка', '$'.number_format($totalRevenue))
                ->description('Сумма всех оплаченных заказов')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Заказы в ожидании', $pendingOrdersCount)
                ->description('Требуют оплаты или обработки')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Средний чек', '$'.number_format($averageCheck, 2))
                ->description('Показатель ценности заказов')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),
        ];
    }
}
