<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

class ProductApiTest extends TestCase
{
    use RefreshDatabase; // Очищает базу данных перед каждым тестом

    public function test_can_create_product_via_api(): void
    {
        // 1. Отправляем POST запрос на наш эндпоинт
        $response = $this->postJson('/api/products', [
            'title' => 'iPhone 15 Pro',
            'description' => 'Флагманский смартфон',
            'price_cents' => 99900, // $999.00
            'stock' => 10,
            'status' => 'published',
        ]);

        // 2. Проверяем HTTP статус ответа (201 Created)
        $response->assertStatus(Response::HTTP_CREATED);

        // 3. Проверяем структуру JSON ответа
        $response->assertJsonStructure([
            'success',
            'data' => ['id', 'title', 'slug', 'price_cents', 'stock', 'status']
        ]);

        // 4. Проверяем автоматическую генерацию уникального slug
        $response->assertJsonPath('data.slug', 'iphone-15-pro');

        // 5. Проверяем, что запись физически появилась в БД
        $this->assertDatabaseHas('products', [
            'title' => 'iPhone 15 Pro',
            'price_cents' => 99900,
        ]);
    }
}
