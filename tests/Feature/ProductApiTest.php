<?php

namespace Tests\Feature;

use App\Models\User;
use Domain\Products\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase; // Очищает базу данных перед каждым тестом

    public function test_can_create_product_via_api(): void
    {
        // Создаем тестового пользователя
        $user = User::create([
            'name' => 'Vendor User',
            'email' => 'vendor@example.com',
            'password' => 'password123',
            'role' => 'vendor',
        ]);

        // Симулируем, что запрос отправляет этот авторизованный пользователь
        Sanctum::actingAs($user);

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
            'data' => ['id', 'title', 'slug', 'price', 'stock', 'status'],
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
        $user = User::create([
            'name' => 'Regular Customer',
            'email' => 'customer_bad@example.com',
            'password' => 'password123',
            'role' => 'customer', // Роль, которой запрещено создавать товары
        ]);

        Sanctum::actingAs($user);

        // 2. Пытаемся отправить запрос
        $response = $this->postJson('/api/products', [
            'title' => 'Zapreshenka',
            'price_cents' => 1000,
            'stock' => 1,
        ]);

        // 3. Ожидаем статус 403 Forbidden (Доступ запрещен)
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_can_get_paginated_list_of_published_products_only(): void
    {
        // Создаем вендора для тестов каталога
        $vendor = User::create([
            'name' => 'Test Vendor',
            'email' => 'vendor_catalog@example.com',
            'password' => 'password',
            'role' => 'vendor',
        ]);

        // 1. Создаем один опубликованный продукт и один черновик
        Product::create([
            'vendor_id' => $vendor->id, // Передаем связь
            'title' => 'Published Item',
            'slug' => 'published-item',
            'price_cents' => 1000,
            'stock' => 5,
            'status' => 'published', // Должен быть в выдаче
        ]);

        $vendor = User::create([
            'name' => 'Test Vendor',
            'email' => 'vendor_cache@example.com',
            'password' => 'password',
            'role' => 'vendor',
        ]);

        Product::create([
            'title' => 'Draft Item',
            'slug' => 'draft-item',
            'price_cents' => 2000,
            'stock' => 0,
            'status' => 'draft', // Должен быть СКРЫТ
            'vendor_id' => $vendor->id,
        ]);

        // 2. Делаем публичный запрос (без авторизации) на чтение каталога
        $response = $this->getJson('/api/products');

        // 3. Проверяем успешный статус и структуру пагинации
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'success',
            'data',
            'pagination' => ['current_page', 'last_page', 'total', 'per_page'],
        ]);

        // 4. Проверяем, что вернулся ровно 1 продукт (только опубликованный)
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.title', 'Published Item');
    }

    public function test_products_catalog_is_cached(): void
    {
        $vendor = User::create([
            'name' => 'Test Vendor 2',
            'email' => 'vendor_cache@example.com',
            'password' => 'password',
            'role' => 'vendor',
        ]);

        // 1. Создаем продукт
        Product::create([
            'vendor_id' => $vendor->id, // Передаем связь
            'title' => 'Cached Phone',
            'slug' => 'cached-phone',
            'price_cents' => 5000,
            'stock' => 2,
            'status' => 'published',
        ]);

        // 2. Делаем первый запрос, чтобы прогреть кэш
        $this->getJson('/api/products');

        // 3. Физически удаляем продукт из базы данных в обход логики приложения
        DB::table('products')->delete();

        // 4. Делаем второй запрос к API
        $response = $this->getJson('/api/products');

        // 5. Проверяем, что API ВСЁ ЕЩЕ отдает продукт, так как данные берутся из кэша, а не из пустой БД
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.title', 'Cached Phone');
    }
}
