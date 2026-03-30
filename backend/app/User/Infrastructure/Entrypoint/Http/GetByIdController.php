<?php

namespace App\User\Infrastructure\Entrypoint\Http;

use App\User\Application\GetUserById\GetUserById;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class GetByIdController
{
    public function __construct(
        private GetUserById $getUserById,
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

        $response = ($this->getUserById)($id);

        if ($response === null) {
            return new JsonResponse([
                'message' => 'User not found',
            ], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}
