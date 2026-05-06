<?php

namespace App\Order\Infrastructure\Entrypoint\Http;

use App\Order\Application\CreateOrder\CreateOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController
{
    public function __construct(
        private CreateOrder $createOrder,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
            'table_id' => ['required', 'string'],
            'opened_by_user_id' => ['required', 'integer'],
            'closed_by_user_id' => ['nullable', 'integer'],
            'status' => ['required', 'string', 'in:open,closed,cancelled'],
            'diners' => ['required', 'integer', 'min:1'],
            'order_lines' => ['required', 'array', 'min:1'],
            'order_lines.*.product_id' => ['required', 'string', 'uuid'],
            'order_lines.*.user_id' => ['required', 'integer'],
            'order_lines.*.quantity' => ['required', 'integer', 'min:1'],
            'order_lines.*.price' => ['required', 'numeric', 'min:0'],
            'order_lines.*.tax_percentage' => ['required', 'numeric', 'min:0'],
        ]);

        $response = ($this->createOrder)(
            $validated['restaurant_id'],
            $validated['table_id'],
            $validated['opened_by_user_id'],
            $validated['closed_by_user_id'] ?? null,
            $validated['status'],
            $validated['diners'],
            $validated['order_lines'],
        );

        return new JsonResponse($response->toArray(), 201);
    }
}