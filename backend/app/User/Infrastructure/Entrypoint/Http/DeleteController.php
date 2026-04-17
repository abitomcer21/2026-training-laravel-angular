<?php

namespace App\User\Infrastructure\Entrypoint\Http;

use App\User\Application\DeleteUser\DeleteUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DeleteController
{
    public function __construct(
        private DeleteUser $deleteUser,
    ) {}

    public function __invoke(string $id): JsonResponse
    {
        $validator = Validator::make([
            'id' => $id,
        ], [
            'id' => ['required', 'uuid'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $deleted = ($this->deleteUser)($id);

        if (! $deleted) {
            return new JsonResponse([
                'message' => 'User not found',
            ], 404);
        }

        return new JsonResponse(null, 204);
    }
}
