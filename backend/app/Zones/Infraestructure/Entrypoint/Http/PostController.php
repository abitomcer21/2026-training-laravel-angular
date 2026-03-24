<?php

namespace App\Zones\Infraestructure\Entrypoint\Http;

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
        ]);

        $response = ($this->createZones)(
            $validated['name'],
        );

        return new JsonResponse($response->toArray(), 201);
    }
}
