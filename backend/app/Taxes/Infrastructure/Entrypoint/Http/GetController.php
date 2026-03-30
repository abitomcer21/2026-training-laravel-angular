<?php

namespace App\Taxes\Infrastructure\Entrypoint\Http;

use App\Taxes\Application\GetTaxes\GetTaxes;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class GetController
{
    public function __construct(
        private GetTaxes $getTaxes,
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

        $response = ($this->getTaxes)($id);

        if ($response === null) {
            return new JsonResponse([
                'message' => 'Taxes not found',
            ], 404);
        }

        return new JsonResponse($response->toArray(), 200);
    }
}
