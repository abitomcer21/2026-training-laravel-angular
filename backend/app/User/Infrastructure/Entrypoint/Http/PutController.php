<?php

namespace App\User\Infrastructure\Entrypoint\Http;

use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use App\User\Application\Command\UpdateUserCommand;
use App\User\Application\Handler\UpdateUserHandler;
use App\User\Infrastructure\Persistence\Models\EloquentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PutController
{
    private const REGLAS_VALIDACION = [
        'email'     => ['nullable', 'string', 'email', 'max:255'],
        'name'      => ['nullable', 'string', 'max:255'],
        'password'  => ['nullable', 'string', 'min:8', 'confirmed'],
        'role'      => ['nullable', 'string', 'in:admin,supervisor,camarero,chef'],
        'image_src' => ['nullable', 'string'],
        'pin'       => ['nullable', 'string', 'regex:/^\d{4}$/'],
    ];

    public function __construct(
        private UpdateUserHandler $updateUserHandler,
    ) {}

    public function __invoke(string $id, Request $request): JsonResponse
    {
        try {
            $userToUpdate = EloquentUser::where('uuid', $id)->first();
            if (!$userToUpdate || $userToUpdate->restaurant_id !== $request->user()->restaurant_id) {
                return new JsonResponse(['message' => 'User not found'], 404);
            }

            $validated = $request->validate(self::REGLAS_VALIDACION);

            $response = ($this->updateUserHandler)(
                UpdateUserCommand::create(
                    id:            $id,
                    email:         $validated['email'] ?? null,
                    name:          $validated['name'] ?? null,
                    plainPassword: $validated['password'] ?? null,
                    role:          $validated['role'] ?? null,
                    imageSrc:      $validated['image_src'] ?? null,
                    pin:           $validated['pin'] ?? null,
                ),
            );

            return new JsonResponse($response->toArray(), 200);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}