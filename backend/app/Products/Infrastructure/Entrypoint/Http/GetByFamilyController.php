<?php
namespace App\Products\Infrastructure\Entrypoint\Http;

use App\Products\Application\GetProductByFamily\GetProductByFamily;
use Illuminate\Http\JsonResponse;

class GetByFamilyController
{
    public function __construct(
        private GetProductByFamily $getProductByFamily,
    ) {}

    public function __invoke(int $FamilyId): JsonResponse
    {
        $response = ($this->getProductByFamily)($FamilyId);

        return new JsonResponse($response->toArray(), 200);
    }
}