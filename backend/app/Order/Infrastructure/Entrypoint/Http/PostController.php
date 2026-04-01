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
            'opened_by_user_id' => ['required', 'string'],
            'status' => ['required', 'string', 'in:open,closed,cancelled'],
            'diners' => ['required', 'integer', 'min:1'],
        ]);

        $response = ($this->createOrder)(
            $validated['restaurant_id'],
            $validated['table_id'],
            $validated['opened_by_user_id'],
            $validated['status'],
            $validated['diners'],
        );

        return new JsonResponse($response->toArray(), 201);
    }
}
