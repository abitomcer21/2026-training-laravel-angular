<?php

namespace App\User\Infrastructure\Entrypoint\Http;

use App\User\Application\Auth\ValidatePin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ValidatePinController
{
    public function __construct(
        private ValidatePin $validatePin,
    ) {}

    public function __invoke(Request $request, string $userUuid): JsonResponse
    {
        $validated = $request->validate([
            'pin' => ['required', 'string', 'regex:/^\d{4}$/'],
        ]);

        try {
            $response = ($this->validatePin)(
                $userUuid,
                $validated['pin'],
            );
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 401);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}
