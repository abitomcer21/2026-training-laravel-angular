<?php

namespace App\Products\Infrastructure\Entrypoint\Http;

use App\Products\Application\Command\UpdateProductCommand;
use App\Products\Application\Handler\UpdateProductHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PutController
{
    private const VALIDATION_RULES = [
        'name'      => ['nullable', 'string', 'max:255'],
        'family_id' => ['nullable', 'string', 'max:255'],
        'tax_id'    => ['nullable', 'string', 'max:255'],
        'price'     => ['nullable', 'integer', 'min:0'],
        'stock'     => ['nullable', 'integer', 'min:0'],
        'image_src' => ['nullable', 'string', 'max:255'],
        'active'    => ['nullable', 'boolean'],
    ];

    public function __construct(
        private UpdateProductHandler $updateProduct,
    ) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validate(self::VALIDATION_RULES);

            $command = UpdateProductCommand::create(
                id:       $id,
                familyId: $validated['family_id'] ?? null,
                taxId:    $validated['tax_id'] ?? null,
                name:     $validated['name'] ?? null,
                price:    $validated['price'] ?? null,
                stock:    $validated['stock'] ?? null,
                imageSrc: $validated['image_src'] ?? null,
                active:   $validated['active'] ?? null,
            );

            $response = ($this->updateProduct)($command);

            return new JsonResponse($response->toArray(), 200);

        } catch (\Throwable $e) {
            return new JsonResponse(['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()], 500);
        }
    }
}

