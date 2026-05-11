<?php

namespace App\Sales\Infrastructure\Entrypoint\Http;

use App\Sales\Application\CreateSale\CreateSale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController
{
    public function __construct(
        private CreateSale $createSale,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'string', 'uuid'],
            'user_id' => ['required', 'integer'],
        ]);

        $response = ($this->createSale)(
            $validated['order_id'],
            $validated['user_id'],
        );

        return new JsonResponse($response->toArray(), 201);
    }
}