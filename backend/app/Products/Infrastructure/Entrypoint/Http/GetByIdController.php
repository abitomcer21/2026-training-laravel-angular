<?php

namespace App\Products\Infrastructure\Entrypoint\Http;

use App\Products\Application\GetProductById\GetProductById;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class GetByIdController
{
    public function __construct(

        private GetProductById $getProducById,
    ) {}

    public function __invoke(string $id): JsonResponse {
        $validator = Validator::make([
            'id' => $id,
        ], [
            'id' => ['required', 'uuid'],
        ]);

        if($validator->fails()) {
            return new JsonResponse ([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $response = ($this->getProducById)($id);

        if($response === null){
            return new JsonResponse([
                'message' => 'Product not found',
            ], 404);
        }

        return new JsonResponse($response->toArray(), 200);

    }
}
