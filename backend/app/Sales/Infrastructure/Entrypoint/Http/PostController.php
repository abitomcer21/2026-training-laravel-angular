<?php

namespace App\Sales\Infrastructure\Entrypoint\Http;

use App\Sales\Application\Command\CreateSaleCommand;
use App\Sales\Application\Handler\CreateSaleHandler;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController
{
    private const REGLAS_VALIDACION = [
        'order_id' => ['required', 'string', 'uuid'],
        'user_id'  => ['required', 'integer'],
    ];

    public function __construct(
        private CreateSaleHandler $createSaleHandler,
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

            $response = ($this->createSaleHandler)(
                CreateSaleCommand::create(
                    orderId: $validated['order_id'],
                    userId:  (string) $validated['user_id'],
                ),
            );

            return new JsonResponse($response->toArray(), 201);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}