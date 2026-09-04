<?php

namespace Domain\Orders\DataTransferObjects;

readonly class OrderData
{
    /**
     * @param array<array{product_id: string, quantity: int}> $items
     */
    public function __construct(
        public array $items
    ) {}
}
