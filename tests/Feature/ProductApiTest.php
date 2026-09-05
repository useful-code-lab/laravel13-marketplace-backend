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
        // Создаем тестового пользователя
        $user = \App\Models\User::create([
            'name' => 'Vendor User',
            'email' => 'vendor@example.com',
            'password' => 'password123',
            'role' => 'vendor'
        ]);

        // Симулируем, что запрос отправляет этот авторизованный пользователь
        \Laravel\Sanctum\Sanctum::actingAs($user);

        $response = $this->postJson('/api/products', [
            'title' => 'iPhone 15 Pro',
            'description' => 'Флагманский смартфон',
            'price_cents' => 99900,
            'stock' => 10,
            'status' => 'published',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure([
            'success',
            'data' => ['id', 'title', 'slug', 'price_cents', 'stock', 'status']
        ]);
        $response->assertJsonPath('data.slug', 'iphone-15-pro');

        $this->assertDatabaseHas('products', [
            'title' => 'iPhone 15 Pro',
            'price_cents' => 99900,
        ]);
    }

    public function test_customer_cannot_create_product_via_api(): void
    {
        // 1. Создаем пользователя с ролью customer
        $user = \App\Models\User::create([
            'name' => 'Regular Customer',
            'email' => 'customer_bad@example.com',
            'password' => 'password123',
            'role' => 'customer' // Роль, которой запрещено создавать товары
        ]);

        \Laravel\Sanctum\Sanctum::actingAs($user);

        // 2. Пытаемся отправить запрос
        $response = $this->postJson('/api/products', [
            'title' => 'Zapreshenka',
            'price_cents' => 1000,
            'stock' => 1,
        ]);

        // 3. Ожидаем статус 403 Forbidden (Доступ запрещен)
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

}
