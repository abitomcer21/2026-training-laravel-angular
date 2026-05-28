<?php

namespace App\Tax\Infrastructure\Entrypoint\Http;

use App\Tax\Application\Command\CreateTaxCommand;
use App\Tax\Application\Handler\CreateTaxHandler;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController
{
    private const REGLAS_VALIDACION = [
        'name'          => ['required', 'string', 'max:255'],
        'percentage'    => ['required', 'integer', 'min:0', 'max:100'],
        'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
    ];

    public function __construct(
        private CreateTaxHandler $createTaxHandler,
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

            $response = ($this->createTaxHandler)(
                CreateTaxCommand::create(
                    name:         $validated['name'],
                    percentage:   $validated['percentage'],
                    restaurantId: $validated['restaurant_id'],
                ),
            );

            return new JsonResponse($response->toArray(), 201);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}