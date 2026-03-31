<?php

namespace App\Zones\Infrastructure\Entrypoint\Http;

use App\Zones\Application\CreateZones\CreateZones;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController
{
    public function __construct(
        private CreateZones $createZones,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
        ]);

        $response = ($this->createZones)(
            $validated['name'],
            $validated['restaurant_id'],
        );

        return new JsonResponse($response->toArray(), 201);
    }
}
