<?php

namespace App\Family\Infrastructure\Entrypoint\Http;

use App\Family\Application\GetFamilyById\GetFamilyById;
use App\Family\Domain\Exceptions\FamilyNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class GetByIdController
{
    public function __construct(
        private GetFamilyById $getFamilyById,
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
            $response = ($this->getFamilyById)($id);
            return new JsonResponse($response->toArray(), 200);

        } catch (FamilyNotFoundException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}