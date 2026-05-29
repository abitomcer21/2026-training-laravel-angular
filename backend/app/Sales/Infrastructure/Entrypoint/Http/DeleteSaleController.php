<?php

namespace App\Sales\Infrastructure\Entrypoint\Http;

use App\Sales\Application\Command\CancelSaleCommand;
use App\Sales\Application\Handler\CancelSaleHandler;
use App\Shared\Infrastructure\Http\ExceptionResponseResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeleteSaleController
{
    public function __construct(
        private CancelSaleHandler $cancelSaleHandler,
    ) {}

    public function __invoke(Request $request, string $id): JsonResponse
    {
        try {
            ($this->cancelSaleHandler)(
                CancelSaleCommand::create(saleId: $id),
            );

            return new JsonResponse(['message' => 'Venta anulada correctamente.'], 200);

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
