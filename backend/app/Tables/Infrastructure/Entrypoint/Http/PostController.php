<?php

namespace App\Tables\Infrastructure\Entrypoint\Http;

use App\Tables\Application\Command\CreateTableCommand;
use App\Tables\Application\Handler\CreateTableHandler;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController
{
    private const VALIDATION_RULES = [
        'zone_id'       => ['required', 'integer', 'exists:zones,id'],
        'name'          => ['required', 'string', 'max:255'],
        'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
    ];

    public function __construct(
        private CreateTableHandler $createTableHandler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), self::VALIDATION_RULES);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        try {
            $validated = $validator->validated();

            $response = ($this->createTableHandler)(
                CreateTableCommand::create(
                    name:         $validated['name'],
                    zoneId:       $validated['zone_id'],
                    restaurantId: $validated['restaurant_id'],
                ),
            );

            return new JsonResponse($response->toArray(), 201);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}