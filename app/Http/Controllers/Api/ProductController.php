<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Resources\ProductResource;
use Domain\Products\Actions\CreateProductAction;
use Domain\Products\DataTransferObjects\ProductData;
use Domain\Products\Models\Product;
use Domain\Products\Queries\GetProductsQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    /**
     * Публичный просмотр каталога товаров с кэшированием
     */
    public function index(GetProductsQuery $query): JsonResponse
    {
        $products = $query->execute(perPage: 10);

        return response()->json([
            'success' => true,
            // Используем collection() для форматирования массивов/пагинации
            'data' => ProductResource::collection($products->items()),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
                'per_page' => $products->perPage(),
            ],
        ], Response::HTTP_OK);
    }

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
            status: $request->input('status', 'draft'),
            vendorId: $request->user()->id,
        );

        // Вызываем бизнес-логику создания продукта
        $product = $action->execute($dto);

        return response()->json([
            'success' => true,
            // Одиночную модель оборачиваем в экземпляр ресурса
            'data' => new ProductResource($product),
        ], Response::HTTP_CREATED);
    }
}
