<?php

namespace Tests\Feature;

use App\Models\User;
use Domain\Orders\Events\OrderCreated;
use Domain\Products\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase; // Очищаем БД перед каждым тестом

    public function test_can_create_order_successfully_and_receives_payment_url(): void
    {
        // Добавьте эти строки первыми внутри каждого тест-метода:
        $user = User::create([
            'name' => 'Customer User',
            'email' => 'customer'.uniqid().'@example.com', // уникальный email для каждого теста
            'password' => 'password123',
            'role' => 'customer',
        ]);
        Sanctum::actingAs($user);

        // 1. Фейкуем события, чтобы листенеры не выполнялись вживую во время теста
        Event::fake([OrderCreated::class]);

        $vendor = User::create([
            'name' => 'Vendor Owner',
            'email' => 'owner@example.com',
            'password' => 'password',
            'role' => 'vendor',
        ]);

        // 2. Создаем тестовый продукт на складе через модель Laravel 13
        /** @var Product $product */
        $product = Product::create([
            'vendor_id' => $vendor->id, // Передаем связь
            'title' => 'PlayStation 5',
            'slug' => 'playstation-5',
            'price_cents' => 50000, // 500.00$
            'stock' => 5,
            'status' => 'published',
        ]);

        // 3. Отправляем запрос на создание заказа (покупаем 2 приставки)
        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        // 4. Проверяем успешный HTTP-ответ
        $response->assertStatus(Response::HTTP_CREATED);

        // 5. Проверяем, что в JSON вернулась правильная сумма (50000 * 2 = 100000) и ссылка на оплату
        $response->assertJsonPath('data.total_cents', 100000);
        $response->assertJsonPath('data.status', 'pending');
        $this->assertStringContainsString('fake-payment-gateway.com', $response->json('data.payment_url'));

        // 6. Проверяем, что остаток на складе уменьшился (было 5, купили 2 -> осталось 3)
        $this->assertEquals(3, $product->fresh()->stock);

        // 7. Проверяем, что событие OrderCreated было сгенерировано системой
        Event::assertDispatched(OrderCreated::class);
    }

    public function test_cannot_create_order_if_out_of_stock(): void
    {
        // Добавьте эти строки первыми внутри каждого тест-метода:
        $user = User::create([
            'name' => 'Customer User',
            'email' => 'customer'.uniqid().'@example.com', // уникальный email для каждого теста
            'password' => 'password123',
            'role' => 'customer',
        ]);
        Sanctum::actingAs($user);

        $vendor = User::create([
            'name' => 'Vendor Owner 2',
            'email' => 'owner2@example.com',
            'password' => 'password',
            'role' => 'vendor',
        ]);

        /** @var Product $product */
        $product = Product::create([
            'vendor_id' => $vendor->id, // Передаем связь
            'title' => 'Xbox Series X',
            'slug' => 'xbox-series-x',
            'price_cents' => 40000,
            'stock' => 1, // На складе всего 1 штука
            'status' => 'published',
        ]);

        // Пытаемся купить 2 штуки
        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        // Проверяем, что наш глобальный перехватчик исключений вернул 422 ошибку
        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('error', 'business_rule_violation');

        // Проверяем, что stock не изменился и остался равен 1
        $this->assertEquals(1, $product->fresh()->stock);
    }
}
