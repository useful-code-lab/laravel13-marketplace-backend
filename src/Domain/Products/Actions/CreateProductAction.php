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

        return Product::create([
            'title' => $data->title,
            'slug' => $slug,
            'description' => $data->description,
            'price_cents' => $data->priceCents,
            'stock' => $data->stock,
            'status' => $data->status,
        ]);
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
