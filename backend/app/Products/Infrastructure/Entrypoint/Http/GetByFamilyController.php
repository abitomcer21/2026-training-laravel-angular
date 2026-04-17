<?php

namespace App\Products\Infrastructure\Entrypoint\Http;

use App\Products\Application\GetProductByFamily\GetProductByFamily;
use Illuminate\Http\JsonResponse;

class GetByFamilyController
{
    public function __construct(
        private GetProductByFamily $getProductByFamily,
    ) {}

    public function __invoke(int $familyId): JsonResponse
    {
        $response = ($this->getProductByFamily)($familyId);

        return new JsonResponse($response->toArray(), 200);
    }
}
