<?php

namespace App\Tables\Infrastructure\Entrypoint\Http;

use App\Tables\Application\DeleteTable\DeleteTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

final class DeleteController
{
    public function __construct(
        private DeleteTable $deleteTable,
    ) {}

    public function __invoke(string $id): JsonResponse
    {
        $validator = Validator::make([
            'id' => $id,
        ], [
            'id' => ['required', 'uuid'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $deleted = ($this->deleteTable)($id);

        if (! $deleted) {
            return new JsonResponse([
                'message' => 'Table not found',
            ], 404);
        }

        return new JsonResponse(null, 204);
    }
}
