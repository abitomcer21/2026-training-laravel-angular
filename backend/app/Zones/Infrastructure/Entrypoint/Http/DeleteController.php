<?php

namespace App\Zones\Infrastructure\Entrypoint\Http;

use App\Zones\Application\Command\DeleteZonesCommand;
use App\Zones\Application\Handler\DeleteZonesHandler;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeleteController
{
    public function __construct(
        private DeleteZonesHandler $deleteZonesHandler,
    ) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make(['id' => $id], [
            'id' => ['required', 'uuid'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        try {
            ($this->deleteZonesHandler)(
                DeleteZonesCommand::create(
                    id:           $id,
                    restaurantId: $request->user()->restaurant_id,
                ),
            );

            return new JsonResponse(null, 204);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}