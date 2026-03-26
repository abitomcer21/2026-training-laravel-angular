<?php

namespace App\Families\Infrastructure\Entrypoint\Http;

use App\Families\Application\DeleteFamily\DeleteFamily;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DeleteController
{
    public function __construct(
        private DeleteFamily $deleteFamily,
    ) {
    }

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

        $deleted = ($this->deleteFamily)($id);

        if (!$deleted) {
            return new JsonResponse([
                'message' => 'Family not found',
            ], 404);
        }

        return new JsonResponse(null, 204);
    }
}