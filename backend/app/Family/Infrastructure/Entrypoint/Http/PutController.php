<?php

namespace App\Family\Infrastructure\Entrypoint\Http;

use App\Family\Application\Command\UpdateFamilyCommand;
use App\Family\Application\Handler\UpdateFamilyHandler;
use App\Family\Domain\Exceptions\EmptyFamilyNameException;
use App\Family\Domain\Exceptions\FamilyNameTooLongException;
use App\Family\Domain\Exceptions\FamilyNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PutController
{
    public function __construct(
        private UpdateFamilyHandler $updateFamily,
    ) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name'   => ['nullable', 'string'],
                'active' => ['nullable', 'boolean'],
            ]);

            $command = new UpdateFamilyCommand(
                id:     $id,
                name:   $validated['name'] ?? null,
                status: $validated['active'] ?? null,
            );

            $response = ($this->updateFamily)($command);

            return new JsonResponse($response->toArray(), 200);

        } catch (FamilyNotFoundException $e) {
            return new JsonResponse(['message' => $e->getMessage()], $e->getCode());

        } catch (EmptyFamilyNameException | FamilyNameTooLongException $e) {
            return new JsonResponse(['message' => $e->getMessage()], $e->getCode());
        }
    }
}