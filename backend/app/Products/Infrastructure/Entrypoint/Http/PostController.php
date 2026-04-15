<?php

namespace App\Products\Infrastructure\Entrypoint\Http;

use App\Products\Application\CreateProduct\CreateProduct;
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
            'Family_id' => ['required', 'integer', 'exists:Family,id'],
            'tax_id' => ['required', 'integer', 'exists:tax,id'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image_src' => ['required', 'string', 'max:255'],
            'active' => ['required', 'boolean'],
        ]);

        $response = ($this->createProduct)(
            $validated['Family_id'],
            $validated['tax_id'],
            $validated['restaurant_id'],
            $validated['name'],
            $validated['price'],
            $validated['stock'],
            $validated['image_src'],
            $validated['active'],
        );

        return new JsonResponse($response->toArray(), 201);
    }
}
