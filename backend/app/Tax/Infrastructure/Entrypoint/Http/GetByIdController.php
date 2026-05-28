<?php

namespace App\Tax\Infrastructure\Entrypoint\Http;

use App\Tax\Application\Handler\GetTaxByIdHandler;
use App\Tax\Application\Query\GetTaxByIdQuery;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GetByIdController
{
    public function __construct(
        private GetTaxByIdHandler $getTaxByIdHandler,
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
            $response = ($this->getTaxByIdHandler)(
                new GetTaxByIdQuery(
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