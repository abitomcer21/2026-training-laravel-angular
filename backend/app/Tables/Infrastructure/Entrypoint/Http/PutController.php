<?php

namespace App\Tables\Infrastructure\Entrypoint\Http;

use App\Tables\Application\Command\UpdateTableCommand;
use App\Tables\Application\Handler\UpdateTableHandler;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PutController
{
    private const VALIDATION_RULES = [
        'name' => ['nullable', 'string', 'max:255'],
    ];

    public function __construct(
        private UpdateTableHandler $updateTableHandler,
    ) {}

    public function __invoke(Request $request, string $id): JsonResponse
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

            $response = ($this->updateTableHandler)(
                UpdateTableCommand::create(
                    id:           $id,
                    name:         $validated['name'] ?? null,
                    restaurantId: $request->user()->restaurant_id,
                ),
            );

            return new JsonResponse($response->toArray(), 200);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}