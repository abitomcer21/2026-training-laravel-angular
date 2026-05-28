<?php

namespace App\User\Infrastructure\Entrypoint\Http;

use App\User\Application\Command\ValidatePinCommand;
use App\User\Application\Handler\ValidatePinHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ValidatePinController
{
    public function __construct(
        private ValidatePinHandler $validatePinHandler,
    ) {}

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

        try {
            $response = ($this->validatePinHandler)(
                ValidatePinCommand::create(
                    userUuid: $userUuid,
                    pin:      $validator->validated()['pin'],
                ),
            );

            return new JsonResponse($response->toArray(), 200);

        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 401);
        }
    }
}
