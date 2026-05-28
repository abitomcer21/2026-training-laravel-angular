<?php

namespace App\User\Infrastructure\Entrypoint\Http;

use App\User\Application\Command\LoginUserCommand;
use App\User\Application\Handler\LoginUserHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoginController
{
    private const REGLAS_VALIDACION = [
        'email'    => ['required', 'string', 'email'],
        'password' => ['required', 'string'],
    ];

    public function __construct(
        private LoginUserHandler $loginUserHandler,
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

            $response = ($this->loginUserHandler)(
                LoginUserCommand::create(
                    email:         $validated['email'],
                    plainPassword: $validated['password'],
                ),
            );

            return new JsonResponse($response->toArray(), 200);

        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 401);
        }
    }
}
