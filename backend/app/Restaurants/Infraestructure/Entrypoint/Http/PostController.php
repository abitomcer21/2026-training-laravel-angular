<?php

namespace App\Restaurants\Infraestructure\Entrypoint\Http;

use App\Restaurants\Application\CreateRestaurantes\CreateRestaurantes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController
{
    public function __construct(
        private CreateRestaurantes $createRestaurantes,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['required', 'string', 'max:255'],
            'tax_id' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        $response = ($this->createRestaurantes)(
            $validated['name'],
            $validated['legal_name'],
            $validated['tax_id'],
            $validated['email'],
            $validated['password'],
        );

        return new JsonResponse($response->toArray(), 201);
    }
}