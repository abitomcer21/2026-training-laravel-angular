<?php

namespace App\Family\Infrastructure\Entrypoint\Http;

use App\Family\Application\UpdateFamily\UpdateFamily;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PutController
{
    public function __construct(
        private UpdateFamily $updateFamily,
    ) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
        ]);

        $response = ($this->updateFamily)(
            $id,
            $validated['name'] ?? null,
            $validated['active'] ?? null,
        );

        if ($response === null) {
            return new JsonResponse(['message' => 'Family not found'], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}
