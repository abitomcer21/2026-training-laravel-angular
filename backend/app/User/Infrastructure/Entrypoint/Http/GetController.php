<?php

namespace App\User\Infrastructure\Entrypoint\Http;

use App\User\Application\GetUser\GetUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class GetController
{
    public function __construct(
        private GetUser $getUser,
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

        try {
            $response = ($this->getUser)($id);
            return new JsonResponse($response->toArray(), 200);
        } catch (\Exception $exception) {
            return new JsonResponse([
                'message' => 'User not found',
            ], 404);
        }
    }
}
