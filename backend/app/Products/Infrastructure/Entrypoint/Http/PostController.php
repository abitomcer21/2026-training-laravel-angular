<?php

namespace App\Products\Infrastructure\Entrypoint\Http;

use App\Products\Application\Command\CreateProductCommand;
use App\Products\Application\Handler\CreateProductHandler;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController
{
    private const VALIDATION_RULES = [
        'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
        'family_id'     => ['required', 'string', 'uuid'],
        'tax_id'        => ['required', 'string', 'uuid'],
        'name'          => ['required', 'string', 'max:255'],
        'price'         => ['required', 'integer', 'min:0'],
        'stock'         => ['required', 'integer', 'min:0'],
        'image_src'     => ['nullable', 'string', 'max:255'],
        'active'        => ['required', 'boolean'],
    ];

    public function __construct(
        private CreateProductHandler $createProductHandler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), self::VALIDATION_RULES);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        try {
            $validated = $validator->validated();

            $response = ($this->createProductHandler)(
                CreateProductCommand::create(
                    familyId:     $validated['family_id'],
                    taxId:        $validated['tax_id'],
                    restaurantId: $validated['restaurant_id'],
                    name:         $validated['name'],
                    price:        $validated['price'],
                    stock:        $validated['stock'],
                    imageSrc:     $validated['image_src'] ?? null,
                    active:       $validated['active'],
                ),
            );

            return new JsonResponse($response->toArray(), 201);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}