<?php

namespace App\Family\Infrastructure\Entrypoint\Http;

use App\Family\Application\Command\UpdateFamilyCommand;
use App\Family\Application\Handler\UpdateFamilyHandler;
use App\Family\Infrastructure\Persistence\Models\EloquentFamily;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PutController
{
    private const REGLAS_VALIDACION = [
        'name'   => ['nullable', 'string'],
        'active' => ['nullable', 'boolean'],
    ];

    public function __construct(
        private UpdateFamilyHandler $updateFamily,
    ) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate(self::REGLAS_VALIDACION);

        $family = EloquentFamily::where('uuid', $id)->first();
        if (!$family || $family->restaurant_id !== $request->user()->restaurant_id) {
            return new JsonResponse(['message' => 'Family not found'], 404);
        }

        try {
    $command = UpdateFamilyCommand::create(
        id: $id,
        name: $validated['name'] ?? null,
        active: $validated['active'] ?? null,
    );

    $response = ($this->updateFamily)($command);

    return new JsonResponse($response->toArray(), 200);
} catch (\Throwable $e) {
    return new JsonResponse([
        'error_class' => get_class($e),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ], 500);
}
    }
}
