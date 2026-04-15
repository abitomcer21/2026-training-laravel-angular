<?php

namespace App\Tax\Infrastructure\Entrypoint\Http;

use App\Tax\Application\GetTaxById\GetTaxById;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class GetByIdController
{
    public function __construct(
        private GetTaxById $getTaxById,
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

        $response = ($this->getTaxById)($id);

        if ($response === null) {
            return new JsonResponse([
                'message' => 'Tax not found',
            ], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}
