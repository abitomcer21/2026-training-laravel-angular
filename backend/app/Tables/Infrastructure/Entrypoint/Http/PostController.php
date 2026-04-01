<?php

namespace App\Tables\Infrastructure\Entrypoint\Http;

use App\Tables\Application\CreateTables\CreateTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController
{
    public function __construct(
        private CreateTable $createTable,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'zone_id' => ['required', 'integer', 'exists:zones,id'],
            'name' => ['required', 'string', 'max:255'],
            'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
        ]);

        $response = ($this->createTable)(
            $validated['zone_id'],
            $validated['name'],
            $validated['restaurant_id'],
        );

        return new JsonResponse($response->toArray(), 201);
    }
}