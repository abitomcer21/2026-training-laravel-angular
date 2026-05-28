<?php

namespace App\User\Infrastructure\Entrypoint\Http;

use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use App\User\Application\Command\CreateUserCommand;
use App\User\Application\Handler\CreateUserHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController
{
    private const REGLAS_VALIDACION = [
        'name'          => ['required', 'string', 'max:255'],
        'email'         => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        'password'      => ['required', 'string', 'min:8', 'confirmed'],
        'role'          => ['required', 'string', 'in:admin,supervisor,camarero,chef'],
        'pin'           => ['required', 'string', 'regex:/^\d{4}$/'],
        'image_src'     => ['nullable', 'string'],
        'restaurant_id' => ['required', 'integer'],
    ];

    public function __construct(
        private CreateUserHandler $createUserHandler,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), self::REGLAS_VALIDACION);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        try {
            $validated = $validator->validated();

            $response = ($this->createUserHandler)(
                CreateUserCommand::create(
                    email:        $validated['email'],
                    name:         $validated['name'],
                    plainPassword: $validated['password'],
                    role:         $validated['role'],
                    pin:          $validated['pin'],
                    restaurantId: $validated['restaurant_id'],
                    imageSrc:     $validated['image_src'] ?? null,
                ),
            );

            return new JsonResponse($response->toArray(), 201);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}
