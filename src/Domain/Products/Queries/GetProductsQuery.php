<?php

namespace Domain\Products\Queries;

use Domain\Products\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class GetProductsQuery
{
    /**
     * Возвращает пагинированный список опубликованных товаров с кэшированием
     */
    public function execute(int $perPage = 15): LengthAwarePaginator
    {
        $page = request()->get('page', 1);

        // Используем атомарное кэширование (помнит данные до очистки)
        // В Enterprise-системах рекомендуется использовать Redis теги: Cache::tags(['products'])
        return Cache::remember("products.list.page.{$page}.per.{$perPage}", now()->addHours(2), function () use ($perPage) {
            return Product::where('status', 'published')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        });
    }
}
