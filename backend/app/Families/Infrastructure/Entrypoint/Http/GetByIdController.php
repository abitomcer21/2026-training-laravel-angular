<?php

namespace App\Families\Infrastructure\Entrypoint\Http;

use App\Families\Application\GetFamilyById\GetFamilyById;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class GetByIdController
{
    public function __construct(
        private GetFamilyById $getFamilyById,
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

        $response = ($this->getFamilyById)($id);

        if ($response === null) {
            return new JsonResponse([
                'message' => 'Error not found',
            ], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}