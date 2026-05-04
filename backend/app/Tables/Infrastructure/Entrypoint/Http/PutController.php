<?php

namespace App\Tables\Infrastructure\Entrypoint\Http;

use App\Tables\Application\UpdateTable\UpdateTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PutController
{
    public function __construct(
        private UpdateTable $updateTable,
    ) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $response = ($this->updateTable)(
            $id,
            $validated['name'],
        );

        if ($response === null) {
            return new JsonResponse([
                'message' => 'Table not found',
            ], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}

