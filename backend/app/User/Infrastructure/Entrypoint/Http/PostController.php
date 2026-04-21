<?php

namespace App\User\Infrastructure\Entrypoint\Http;

use App\User\Application\CreateUser\CreateUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController
{
    public function __construct(
        private CreateUser $createUser,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:admin,supervisor,camarero,chef'],
            'pin' => ['required', 'string', 'regex:/^\d{4}$/'],
            'image_src' => ['nullable', 'string'],
            'restaurant_id' => ['required', 'integer'],
        ]);

        $response = ($this->createUser)(
            $validated['email'],
            $validated['name'],
            $validated['password'],
            $validated['role'],
            $validated['pin'],
            $validated['restaurant_id'],
            $validated['image_src'] ?? null,
        );

        return new JsonResponse($response->toArray(), 201);
    }
}
