<?php

namespace Domain\Products\Actions;

use Domain\Products\DataTransferObjects\ProductData;
use Domain\Products\Models\Product;
use Illuminate\Support\Str;

class CreateProductAction
{
    /**
     * Выполняет бизнес-логику создания нового продукта
     */
    public function execute(ProductData $data): Product
    {
        $slug = $this->generateUniqueSlug($data->title);

        $product = Product::create([
            'title' => $data->title,
            'slug' => $slug,
            'description' => $data->description,
            'price_cents' => $data->priceCents,
            'stock' => $data->stock,
            'status' => $data->status,
        ]);

        // Инвалидируем кэш каталога, так как данные изменились
        // В реальном проекте с Redis это будет: Cache::tags(['products'])->flush();
        // Для стандартного кэша очищаем все страницы (для простоты очистим базовые ключи или весь пул)
        // В Laravel 11/13 можно использовать хелпер для выборочной очистки или просто очистить кэш продуктов:
        \Illuminate\Support\Facades\Cache::flush(); 

        return $product;
    }


    /**
     * Рекурсивная генерация уникального slug для избежания конфликтов в БД
     */
    private function generateUniqueSlug(string $title): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}
