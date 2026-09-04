<?php

namespace Domain\Products\DataTransferObjects;

/**
 * Строго типизированный объект данных продукта
 */
readonly class ProductData
{
    public function __construct(
        public string $title,
        public ?string $description,
        public int $priceCents,
        public int $stock,
        public string $status = 'draft'
    ) {}
}
