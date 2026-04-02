<?php

namespace App\Families\Infrastructure\Entrypoint\Http;

use App\Families\Application\ActivateFamily\ActivateFamily;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

final class ActivateController
{
    public function __construct(
        private ActivateFamily $activateFamily,
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

        $response = ($this->activateFamily)($id);

        if ($response === null) {
            return new JsonResponse([
                'message' => 'Family not found',
            ], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}
