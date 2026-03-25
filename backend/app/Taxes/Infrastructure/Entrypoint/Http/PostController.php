<?php

namespace App\Taxes\Infrastructure\Entrypoint\Http;

use App\Taxes\Application\CreateTaxes\CreateTaxes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController
{
    public function __construct(
        private CreateTaxes $createTaxes,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'percentage' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $response = ($this->createTaxes)(
            $validated['name'],
            $validated['percentage'],
        );

        return new JsonResponse($response->toArray(), 201);
    }
}
