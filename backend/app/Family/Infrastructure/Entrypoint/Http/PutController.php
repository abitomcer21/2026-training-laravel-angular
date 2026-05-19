<?php

namespace App\Family\Infrastructure\Entrypoint\Http;

use App\Family\Application\Command\UpdateFamilyCommand;
use App\Family\Application\Handler\UpdateFamilyHandler;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PutController
{
    private const VALIDATION_RULES = [
        'name'   => ['nullable', 'string'],
        'active' => ['nullable', 'boolean'],
    ];

    public function __construct(
        private UpdateFamilyHandler $updateFamily,
    ) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validate(self::VALIDATION_RULES);

            $command = UpdateFamilyCommand::create(
                id:     $id,
                name:   $validated['name'] ?? null,
                active: $validated['active'] ?? null,
            );

            $response = ($this->updateFamily)($command);

            return new JsonResponse($response->toArray(), 200);

        } catch (\Throwable $e) {
            return ExceptionResponseResolver::resolve($e);
        }
    }
}