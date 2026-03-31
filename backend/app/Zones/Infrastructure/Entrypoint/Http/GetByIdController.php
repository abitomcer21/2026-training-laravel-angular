<?php

namespace App\Zones\Infrastructure\Entrypoint\Http;

use App\Zones\Application\GetZonesById\GetZonesById;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class GetByIdController
{
    public function __construct(
        private GetZonesById $getZonesById,
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

        $response = ($this->getZonesById)($id);

        if ($response === null) {
            return new JsonResponse([
                'message' => 'Zones not found',
            ], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}
