<?php

namespace App\Tables\Infraestructure\Entrypoint\Http;

use App\Tables\Application\CreateTables\CreateTables;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController
{
    public function __construct(
        private CreateTables $createTables,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'zone_id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $response = ($this->createTables)(
            $validated['zone_id'],
            $validated['name'],
        );

        return new JsonResponse($response->toArray(), 201);
    }
}
