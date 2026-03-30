<?php

namespace App\Taxes\Infrastructure\Entrypoint\Http;

use App\Taxes\Application\GetTaxesById\GetTaxesById;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class GetByIdController
{
    public function __construct(
        private GetTaxesById $getTaxesById,
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

        $response = ($this->getTaxesById)($id);

        if ($response === null) {
            return new JsonResponse([
                'message' => 'Taxes not found',
            ], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}
