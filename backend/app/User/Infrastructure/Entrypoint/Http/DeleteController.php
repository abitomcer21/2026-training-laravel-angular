<?php

namespace App\User\Infrastructure\Entrypoint\Http;

use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use App\User\Application\Command\DeleteUserCommand;
use App\User\Application\Handler\DeleteUserHandler;
use App\User\Infrastructure\Persistence\Models\EloquentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeleteController
{
    public function __construct(
        private DeleteUserHandler $deleteUserHandler,
    ) {}

    public function __invoke(string $id, Request $request): JsonResponse
    {
        $validator = Validator::make(['id' => $id], [
            'id' => ['required', 'uuid'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        $userToDelete = EloquentUser::where('uuid', $id)->first();
        if (!$userToDelete || $userToDelete->restaurant_id !== $request->user()->restaurant_id) {
            return new JsonResponse(['message' => 'User not found'], 404);
        }

        try {
            ($this->deleteUserHandler)(DeleteUserCommand::create($id));

            return new JsonResponse(null, 204);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}