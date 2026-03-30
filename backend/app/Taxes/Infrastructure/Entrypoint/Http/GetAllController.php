<?php

namespace App\Taxes\Infrastructure\Entrypoint\Http;

use App\Taxes\Application\GetAllTaxes\GetAllTaxes;
use Illuminate\Http\JsonResponse;

final class GetAllController
{
    public function __invoke(GetAllTaxes $getAllTaxes): JsonResponse
    {
        $response = $getAllTaxes();

        return new JsonResponse($response->toArray(), 200);
    }
}