<?php

namespace App\User\Infrastructure\Entrypoint\Http;

use App\User\Infrastructure\Persistence\Models\EloquentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ValidatePinController
{
    public function __invoke(Request $request, string $userUuid): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pin' => ['required', 'string', 'regex:/^\d{4}$/'],
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()->toArray(),
            ], 422);
        }

        $user = EloquentUser::where('uuid', $userUuid)->first();
        if (!$user) {
            return new JsonResponse(['valid' => false, 'message' => 'User not found'], 404);
        }

        $valid = $user->pin === $request->pin;
        return new JsonResponse(['valid' => $valid]);
    }
}