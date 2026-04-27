<?php

namespace App\Products\Infrastructure\Entrypoint\Http;

use App\Family\Infrastructure\Persistence\Models\EloquentFamily;
use App\Products\Application\CreateProduct\CreateProduct;
use App\Tax\Infrastructure\Persistence\Models\EloquentTax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController
{
    public function __construct(
        private CreateProduct $createProduct,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
            'family_id' => ['required', 'string', 'uuid'],
            'tax_id' => ['required', 'string', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image_src' => ['nullable', 'string', 'max:255'],
            'active' => ['required', 'boolean'],
        ]);

        $imageSrc = $validated['image_src'] ?? null;

        $response = ($this->createProduct)(
            $validated['family_id'],
            $validated['tax_id'],
            $validated['restaurant_id'],
            $validated['name'],
            $validated['price'],
            $validated['stock'],
            $imageSrc,
            $validated['active'],
        );

        return new JsonResponse($response->toArray(), 201);
    }
}
