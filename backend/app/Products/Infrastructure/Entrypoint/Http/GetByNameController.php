<?php

namespace App\Products\Infrastructure\Entrypoint\Http;

use App\Products\Application\Handler\GetProductByNameHandler;
use App\Products\Application\Query\GetProductByNameQuery;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GetByNameController
{
    public function __construct(
        private GetProductByNameHandler $getProductByNameHandler,
    ) {}

    public function __invoke(Request $request, string $name): JsonResponse
    {
        $validator = Validator::make(['name' => $name], [
            'name' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        try {
            $response = ($this->getProductByNameHandler)(
                new GetProductByNameQuery(
                    name:         $name,
                    restaurantId: $request->user()->restaurant_id,
                ),
            );

            return new JsonResponse($response->toArray(), 200);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}