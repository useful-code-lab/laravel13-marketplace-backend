<?php

namespace Domain\Orders\Actions;

use Domain\Orders\DataTransferObjects\OrderData;
use Domain\Orders\Models\Order;
use Domain\Products\Models\Product;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreateOrderAction
{
    public function execute(OrderData $data): Order
    {
        // Оборачиваем всё в ACID-транзакцию
        return DB::transaction(function () use ($data) {
            $order = Order::create([
                'total_cents' => 0,
                'status' => 'pending',
            ]);

            $totalCents = 0;

            foreach ($data->items as $itemData) {
                // Блокируем строку продукта для апдейта (защита от Race Condition)
                $product = Product::where('id', $itemData['product_id'])->lockForUpdate()->firstOrFail();

                if ($product->stock < $itemData['quantity']) {
                    throw new \Domain\Shared\Exceptions\BusinessException("Недостаточно товара {$product->title} на складе.");
                }

                // Списываем остаток
                $product->decrement('stock', $itemData['quantity']);

                $itemPrice = $product->price_cents * $itemData['quantity'];
                $totalCents += $itemPrice;

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'price_cents' => $product->price_cents,
                ]);
            }

            // Обновляем итоговую сумму заказа
            $order->update(['total_cents' => $totalCents]);

            return $order;
        });
    }
}
