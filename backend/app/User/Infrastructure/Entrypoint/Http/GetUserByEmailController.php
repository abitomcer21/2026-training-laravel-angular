<?php

namespace App\User\Infrastructure\Entrypoint\Http;

use App\User\Application\GetUserByEmail\GetUserByEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GetUserByEmailController
{
    public function __construct(
        private GetUserByEmail $getUserByEmail,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        $email = $request->input('email');
        $response = ($this->getUserByEmail)($email);

        if ($response === null) {
            return new JsonResponse([
                'message' => 'User not found',
            ], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}
