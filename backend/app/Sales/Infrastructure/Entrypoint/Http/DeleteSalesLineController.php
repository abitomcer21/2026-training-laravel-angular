<?php

namespace App\Sales\Infrastructure\Entrypoint\Http;

use App\Sales\Application\Command\CancelSalesLineCommand;
use App\Sales\Application\Handler\CancelSalesLineHandler;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeleteSalesLineController
{
    public function __construct(
        private CancelSalesLineHandler $cancelSalesLineHandler,
    ) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {
        try {
            ($this->cancelSalesLineHandler)(
                CancelSalesLineCommand::create(salesLineId: $id),
            );

            return new JsonResponse(['message' => 'Línea de venta anulada correctamente.'], 200);

        } catch (\RuntimeException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 404);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
                'file'    => $e->getFile() . ':' . $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ], 500);
        }
    }
}
