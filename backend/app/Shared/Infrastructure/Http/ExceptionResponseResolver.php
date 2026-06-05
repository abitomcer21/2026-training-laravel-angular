<?php

namespace App\Shared\Infrastructure\Http;

use Illuminate\Http\JsonResponse;
use App\Tax\Domain\Exceptions\TaxNotFoundException;


class ExceptionResponseResolver
{
    private const EXCEPTION_MAP = [
        \InvalidArgumentException::class => 422,
        \DomainException::class          => 400,
    ];

    public static function resolve(\Throwable $e): JsonResponse
    {

        if ($e instanceof TaxNotFoundException) {
        return new JsonResponse(['message' => $e->getMessage()], 404);
        }
        if ($e instanceof \InvalidArgumentException) {
            return new JsonResponse([
                'message' => 'Validation failed',
                'errors'  => ['name' => [$e->getMessage()]],
            ], 422);
        }

        if ($e instanceof \DomainException) {
            $status = $e->getCode() > 0 ? (int) $e->getCode() : 400;

            return new JsonResponse(['message' => $e->getMessage()], $status);
        }

        return new JsonResponse(['message' => 'Server error'], 500);
    }
}