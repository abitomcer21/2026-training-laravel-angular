<?php

namespace App\Family\Infrastructure\Entrypoint\Http;

use App\Family\Application\UpdateFamily\UpdateFamily;
use App\Family\Domain\Exceptions\EmptyFamilyNameException;
use App\Family\Domain\Exceptions\FamilyNameTooLongException;
use App\Family\Domain\Exceptions\FamilyNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PutController
{
    public function __construct(
        private UpdateFamily $updateFamily,
    ) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => ['nullable', 'string'],
                'active' => ['nullable', 'boolean'],
            ]);

            $response = ($this->updateFamily)(
                $id,
                $validated['name'] ?? null,
                $validated['active'] ?? null,
            );

            return new JsonResponse($response->toArray(), 200);

        } catch (FamilyNotFoundException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], $e->getCode());

        } catch (EmptyFamilyNameException | FamilyNameTooLongException $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}