<?php

namespace Database\Seeders;

use App\Models\User;
use Domain\Orders\Models\Order;
use Domain\Orders\Models\OrderItem;
use Domain\Products\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Создаем тестовых продавцов (Vendors)
        User::factory()->count(5)->vendor()->create();

        // 2. Создаем тестовых покупателей (Customers)
        User::factory()->count(10)->customer()->create();

        // 3. Создаем 30 различных товаров, привязанных к продавцам
        $products = Product::factory()->count(30)->create();

        // 4. Генерируем 15 случайных заказов для наполнения админки и дашборда
        for ($i = 0; $i < 15; $i++) {
            $status = collect(['pending', 'paid', 'shipped'])->random();

            // Создаем сам заказ
            $order = Order::create([
                'id' => (string) Str::uuid(),
                'total_cents' => 0, // Посчитаем ниже на основе позиций
                'status' => $status,
                'created_at' => now()->subDays(rand(1, 30)), // Раскидаем даты по календарю для графиков
            ]);

            $totalCents = 0;
            // Берем от 1 до 3 случайных товаров в этот чек
            $randomProducts = $products->random(rand(1, 3));

            foreach ($randomProducts as $product) {
                $quantity = rand(1, 2);
                $priceCents = $product->price_cents;
                $totalCents += $priceCents * $quantity;

                OrderItem::create([
                    'id' => (string) Str::uuid(),
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price_cents' => $priceCents,
                ]);
            }

            // Обновляем финальную стоимость заказа
            $order->update(['total_cents' => $totalCents]);
        }
    }
}
