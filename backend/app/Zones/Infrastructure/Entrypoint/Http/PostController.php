<?php

namespace App\Zones\Infrastructure\Entrypoint\Http;

use App\Zones\Application\Command\CreateZonesCommand;
use App\Zones\Application\Handler\CreateZonesHandler;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController
{
    private const REGLAS_VALIDACION = [
        'name'          => ['required', 'string', 'max:255'],
        'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
    ];

    public function __construct(
        private CreateZonesHandler $createZonesHandler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), self::REGLAS_VALIDACION);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        try {
            $validated = $validator->validated();

            $response = ($this->createZonesHandler)(
                CreateZonesCommand::create(
                    name:         $validated['name'],
                    restaurantId: $validated['restaurant_id'],
                ),
            );

            return new JsonResponse($response->toArray(), 201);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}