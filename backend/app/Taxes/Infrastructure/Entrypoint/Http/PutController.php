<?php

namespace App\Taxes\Infrastructure\Entrypoint\Http;

use App\Taxes\Application\UpdateTaxes\UpdateTaxes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PutController
{
    public function __construct(
        private UpdateTaxes $updateTaxes,
    ) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'percentage' => ['nullable', 'integer', 'min:0'],
        ]);

        $response = ($this->updateTaxes)(
            $id,
            $validated['name'] ?? null,
            $validated['percentage'] ?? null,
        );

        if ($response === null) {
            return new JsonResponse(['message' => 'Tax not found'], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}