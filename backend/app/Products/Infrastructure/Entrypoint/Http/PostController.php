<?php

namespace App\Products\Infrastructure\Entrypoint\Http;

use App\Products\Application\CreateProducts\CreateProducts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController
{
    public function __construct(
        private CreateProducts $createProducts,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'family_id' => ['required', 'uuid'],
            'tax_id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image_src' => ['required', 'string', 'max:255'],
            'active' => ['required', 'boolean'],
        ]);

        $response = ($this->createProducts)(
            $validated['family_id'],
            $validated['tax_id'],
            $validated['name'],
            $validated['price'],
            $validated['stock'],
            $validated['image_src'],
            $validated['active'],
        );

        return new JsonResponse($response->toArray(), 201);
    }
}
