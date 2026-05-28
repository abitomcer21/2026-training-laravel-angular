<?php

namespace App\Tax\Infrastructure\Entrypoint\Http;

use App\Tax\Application\Command\UpdateTaxCommand;
use App\Tax\Application\Handler\UpdateTaxHandler;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PutController
{
    private const VALIDATION_RULES = [
        'name'       => ['nullable', 'string', 'max:255'],
        'percentage' => ['nullable', 'integer', 'min:0'],
    ];

    public function __construct(
        private UpdateTaxHandler $updateTaxHandler,
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

            $response = ($this->updateTaxHandler)(
                UpdateTaxCommand::create(
                    id:           $id,
                    name:         $validated['name'] ?? null,
                    percentage:   $validated['percentage'] ?? null,
                    restaurantId: $request->user()->restaurant_id,
                ),
            );

            return new JsonResponse($response->toArray(), 200);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}