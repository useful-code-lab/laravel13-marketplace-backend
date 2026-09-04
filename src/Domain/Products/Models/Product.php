<?php

namespace Domain\Products\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property int $price_cents
 * @property int $stock
 * @property string $status
 */
class Product extends Model
{
    use HasFactory;
    use HasUuids; // Автоматически генерирует UUID при создании записи

    // Указываем Laravel, что ID не является автоинкрементным int
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'price_cents',
        'stock',
        'status',
    ];

    protected $casts = [
        'price_cents' => 'integer',
        'stock' => 'integer',
    ];
}
