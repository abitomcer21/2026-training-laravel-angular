<?php

namespace App\Sales\Infraestructure\Entrypoint\Http;

use App\Sales\Application\CreateSales\CreateSales;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController
{
    public function __construct(
        private CreateSales $createSales,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'table_id' => ['required', 'uuid'],
            'opened_by_user_id' => ['required', 'uuid'],
            'diners' => ['required', 'integer', 'min:1'],
        ]);

        $response = ($this->createSales)(
            $validated['table_id'],
            $validated['opened_by_user_id'],
            $validated['diners'],
        );

        return new JsonResponse($response->toArray(), 201);
    }
}
