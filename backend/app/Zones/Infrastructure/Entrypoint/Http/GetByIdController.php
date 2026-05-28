<?php

namespace App\Zones\Infrastructure\Entrypoint\Http;

use App\Zones\Application\Handler\GetZonesByIdHandler;
use App\Zones\Application\Query\GetZonesByIdQuery;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GetByIdController
{
    public function __construct(
        private GetZonesByIdHandler $getZonesByIdHandler,
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
            $response = ($this->getZonesByIdHandler)(
                new GetZonesByIdQuery(
                    id:           $id,
                    restaurantId: $request->user()->restaurant_id,
                ),
            );

            return new JsonResponse($response->toArray(), 200);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}