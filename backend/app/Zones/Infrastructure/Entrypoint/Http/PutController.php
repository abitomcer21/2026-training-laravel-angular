<?php

namespace App\Zones\Infrastructure\Entrypoint\Http;

use App\Zones\Application\UpdateZones\UpdateZones;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PutController
{
    public function __construct(private UpdateZones $updateZones) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $response = ($this->updateZones)(
            $id,
            $validated['name'] ?? null,
        );

        if ($response === null) {
            return new JsonResponse([
                'message' => 'Zone not found',
            ], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}