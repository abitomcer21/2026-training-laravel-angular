<?php

namespace App\Products\Infrastructure\Entrypoint\Http;

use App\Products\Application\UpdateProduct\UpdateProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PutController
{

    public function __construct(
        private UpdateProduct $updateProduct,
    ) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make([
            ...$request->all(),
            'id' => $id,
        ], [
            'id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image_src' => ['required', 'string', 'max:255'],
            'active' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $validated = $validator->validated();

        $response = ($this->updateProduct)(
            $id,
            $validated['name'],
            (int) $validated['price'],
            (int) $validated['stock'],
            $validated['image_src'],
            $request->boolean('active'),
        );

        if ($response === null) {
            return new JsonResponse([
                'message' => 'Product not found',
            ], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}