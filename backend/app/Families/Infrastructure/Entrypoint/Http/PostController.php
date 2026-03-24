<?php

namespace App\Families\Infrastructure\Entrypoint\Http;

use App\Families\Application\CreateFamilies\CreateFamilies as CreateFamilies;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController
{
    public function __construct(
        private CreateFamilies $createFamilies,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'activo' => ['required', 'boolean'],
        ]);

        $response = ($this->createFamilies)(
            $validated['name'],
            $validated['activo'],
        );

        return new JsonResponse($response->toArray(), 201);
    }
}
