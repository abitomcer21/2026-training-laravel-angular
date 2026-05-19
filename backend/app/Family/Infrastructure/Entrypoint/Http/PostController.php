<?php

namespace App\Family\Infrastructure\Entrypoint\Http;

use App\Family\Application\Command\CreateFamilyCommand;
use App\Family\Application\Handler\CreateFamilyHandler;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController
{
    private const REGLAS_VALIDACION = [
        'name'          => ['required', 'string'],
        'active'        => ['required', 'boolean'],
        'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
    ];

    public function __construct(
        private CreateFamilyHandler $createFamilyHandler,
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

            $response = ($this->createFamilyHandler)(
        CreateFamilyCommand::create(
        name:         $validated['name'],
        active:       $validated['active'],
        restaurantId: $validated['restaurant_id'],
        ),
    );

            return new JsonResponse($response->toArray(), 201);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}