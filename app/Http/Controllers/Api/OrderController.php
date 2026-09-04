<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use Domain\Orders\Actions\CreateOrderAction;
use Domain\Orders\DataTransferObjects\OrderData;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request, CreateOrderAction $action): JsonResponse
    {
        $dto = new OrderData(
            items: $request->input('items')
        );

        $order = $action->execute($dto);

        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $order->id,
                'total_cents' => $order->total_cents,
                'status' => $order->status,
            ]
        ], Response::HTTP_CREATED);
    }
}
