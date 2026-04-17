<?php

namespace App\User\Infrastructure\Entrypoint\Http;

use App\User\Application\UpdateUser\UpdateUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PutController
{
    public function __construct(
        private UpdateUser $updateUser,
    ) {}

    public function __invoke(string $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['nullable', 'string', 'in:admin,cashier,waiter,chef'],
            'image_src' => ['nullable', 'string'],
            'pin' => ['nullable', 'string', 'regex:/^\d{4}$/'],
        ]);

        $response = ($this->updateUser)(
            $id,
            $validated['email'] ?? null,
            $validated['name'] ?? null,
            $validated['password'] ?? null,
            $validated['role'] ?? null,
            $validated['image_src'] ?? null,
            $validated['pin'] ?? null,
        );

        if ($response === null) {
            return new JsonResponse(['message' => 'User not found'], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}
