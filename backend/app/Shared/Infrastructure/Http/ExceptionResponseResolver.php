<?php

namespace App\Shared\Infrastructure\Http;

use Illuminate\Http\JsonResponse;

class ExceptionResponseResolver
{
    private const EXCEPTION_MAP = [
        \InvalidArgumentException::class => 422,
        \DomainException::class          => 400,
    ];

    public static function resolve(\Throwable $e): JsonResponse
    {
        foreach (self::EXCEPTION_MAP as $exceptionClass => $statusCode) {
            if ($e instanceof $exceptionClass) {
                return new JsonResponse(['message' => $e->getMessage()], $statusCode);
            }
        }

        return new JsonResponse(['message' => 'Server error'], 500);
    }
}