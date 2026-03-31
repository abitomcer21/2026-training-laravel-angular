<?php

namespace App\Zones\Infrastructure\Entrypoint\Http;

use App\Zones\Application\DeleteZone\DeleteZones;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DeleteController
{
    public function __construct(private DeleteZones $deleteZone) {}

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

        $deleteZone = ($this->deleteZone)($id);

        if (!$deleteZone) {
            return new JsonResponse([
                'message' => 'Zone not found',
            ], 404);
        }
        return new JsonResponse(null, 204);
    }
}
