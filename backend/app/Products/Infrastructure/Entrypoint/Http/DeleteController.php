<?php

namespace App\Products\Infrastructure\Entrypoint\Http;

use App\Products\Application\DeleteProduct\DeleteProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

final class DeleteController
{

    public function __construct(
        private DeleteProduct $deleteProduct,
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

        $deleted = ($this->deleteProduct)($id);

        if (!$deleted) {
            return new JsonResponse([
                'message' => 'Product not found',
            ], 404);
        }

        return new JsonResponse(null, 204);
    }
}
