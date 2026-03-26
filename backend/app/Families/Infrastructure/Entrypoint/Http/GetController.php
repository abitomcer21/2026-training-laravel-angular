<?php

namespace App\Families\Infrastructure\Entrypoint\Http;

use App\Families\Application\GetFamily\GetFamily;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class GetController
{
    public function __construct(
        private GetFamily $getFamily,
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

        $response = ($this->getFamily)($id);

        if ($response === null) {
            return new JsonResponse([
                'message' => 'Family not found',
            ], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}