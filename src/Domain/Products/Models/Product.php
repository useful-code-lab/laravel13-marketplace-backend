<?php

namespace Domain\Products\Models;

use App\Models\User;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Senior-модель в стиле Laravel 13 с использованием native PHP-атрибутов.
 * Больше никаких прыжков между массивами конфигурации внутри класса.
 */
#[Table('products', key: 'id', keyType: 'string', incrementing: false)]
#[Fillable(['title', 'slug', 'description', 'price_cents', 'stock', 'status', 'vendor_id'])]
class Product extends Model
{
    use HasFactory;
    use HasUuids; // Автоматическая генерация UUID для первичного ключа

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'stock' => 'integer',
        ];
    }

    /**
     * Связь товара с его продавцом
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    /**
     * Явное указание фабрики для DDD модели
     */
    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
