<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use Domain\Products\Actions\CreateProductAction;
use Domain\Products\DataTransferObjects\ProductData;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Domain\Products\Models\Product; 
use Illuminate\Support\Facades\Gate;


class ProductController extends Controller
{
    /**
     * Внедряем экшен через Dependency Injection (DI) в конструктор или метод
     */
    public function store(StoreProductRequest $request, CreateProductAction $action): JsonResponse
    {
        // Проверяем права текущего авторизованного пользователя на создание модели Product
        Gate::authorize('create', Product::class);

        // Преобразуем валидированные данные из HTTP-запроса в доменный DTO
        $dto = new ProductData(
            title: $request->input('title'),
            description: $request->input('description'),
            priceCents: $request->input('price_cents'),
            stock: $request->input('stock'),
            status: $request->input('status', 'draft')
        );

        // Вызываем бизнес-логику создания продукта
        $product = $action->execute($dto);

        // Возвращаем строго типизированный JSON-ответ
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'title' => $product->title,
                'slug' => $product->slug,
                'price_cents' => $product->price_cents,
                'stock' => $product->stock,
                'status' => $product->status,
            ]
        ], Response::HTTP_CREATED);
    }
}
