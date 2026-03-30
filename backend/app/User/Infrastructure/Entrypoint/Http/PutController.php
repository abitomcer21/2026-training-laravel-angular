<?php

namespace App\User\Infrastructure\Entrypoint\Http;

use App\User\Application\UpdateUser\UpdateUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PutController
{
    public function __construct(
        private UpdateUser $updateUser,
    ) {
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make([
            ...$request->all(),
            'id' => $id,
        ], [
            'id' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'in:admin,cashier,waiter,chef'],
            'pin' => ['required', 'string', 'regex:/^\d{4}$/'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $validated = $validator->validated();

        $response = ($this->updateUser)(
            $id,
            $validated['name'],
            $validated['role'],
            $validated['pin'],
        );

        if ($response === null) {
            return new JsonResponse([
                'message' => 'User not found',
            ], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}