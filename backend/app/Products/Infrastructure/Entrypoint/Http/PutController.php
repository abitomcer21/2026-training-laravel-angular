<?php

namespace App\Products\Infrastructure\Entrypoint\Http;

use App\Family\Infrastructure\Persistence\Models\EloquentFamily;
use App\Products\Application\UpdateProduct\UpdateProduct;
use App\Tax\Infrastructure\Persistence\Models\EloquentTax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PutController
{
    public function __construct(
        private UpdateProduct $updateProduct,
    ) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'price' => ['nullable', 'integer', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'image_src' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
        ]);

        $response = ($this->updateProduct)(
            $id,
            $validated['name'] ?? null,
            $validated['price'] ?? null,
            $validated['stock'] ?? null,
            $validated['image_src'] ?? null,
            $validated['active'] ?? null,
        );

        if ($response === null) {
            return new JsonResponse(['message' => 'Product not found'], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}
