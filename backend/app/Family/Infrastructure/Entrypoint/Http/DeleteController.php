<?php

namespace App\Family\Infrastructure\Entrypoint\Http;

use App\Family\Application\DeleteFamily\DeleteFamily;
use App\Family\Domain\Exceptions\FamilyNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DeleteController
{
    public function __construct(
        private DeleteFamily $deleteFamily,
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

        try {
            ($this->deleteFamily)($id);
            return new JsonResponse(null, 204);

        } catch (FamilyNotFoundException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}