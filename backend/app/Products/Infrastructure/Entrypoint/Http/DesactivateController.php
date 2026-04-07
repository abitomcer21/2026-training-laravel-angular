<?php

namespace App\Products\Infrastructure\Entrypoint\Http;

use App\Products\Application\DesactivateProduct\DesactivateProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

final class DesactivateController
{
    public function __construct(
        private DesactivateProduct $desactivateProduct,
    ) {}

    public function __invoke(string $id): JsonResponse
    {
        $validator = Validator::make([
            'id' => $id,
        ], [
            'id' => ['required', 'uuid'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $response = ($this->desactivateProduct)($id);

        if ($response === null) {
            return new JsonResponse([
                'message' => 'Product not found',
            ], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}
