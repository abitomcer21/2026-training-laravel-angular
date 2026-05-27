import { Injectable } from '@angular/core';
import {
  CurrentOrder,
  OrderItem,
  OrderStateService,
} from './order-state.service';
import { SalesService } from './api/sales.service';
import { SalesUpdateService } from './shared/sales-update.service';

export type TipoCobro = 'completo' | 'dividir' | 'articulos';

@Injectable({ providedIn: 'root' })
export class CobroService {
  constructor(
    private orderStateService: OrderStateService,
    private salesService: SalesService,
    private salesUpdateService: SalesUpdateService,
  ) {}

  getTotalPendiente(
    order: CurrentOrder,
    articulosPagados: Record<string, boolean>,
  ): number {
    return order.items
      .filter((item) => !articulosPagados[item.productId])
      .reduce((sum, item) => sum + item.total, 0);
  }

  getTotalSeleccionado(
    order: CurrentOrder,
    articulosSeleccionados: Record<string, boolean>,
    articulosPagados: Record<string, boolean>,
  ): number {
    return order.items
      .filter(
        (i) =>
          articulosSeleccionados[i.productId] && !articulosPagados[i.productId],
      )
      .reduce((sum, i) => sum + i.total, 0);
  }

  calculartotalPorPersona(
    order: CurrentOrder,
    articulosPagados: Record<string, boolean>,
    numeroComensales: number,
  ): number {
    const totalPendiente = this.getTotalPendiente(order, articulosPagados);

    return totalPendiente / numeroComensales;
  }

  aplicarCobro(
    tipoCobro: TipoCobro,
    order: CurrentOrder,
    articulosPagados: Record<string, boolean>,
    articulosSeleccionados: Record<string, boolean>,
    totalPorPersona: number,
    numeroComensales: number,
  ): { articulosPagados: Record<string, boolean>; totalCobrado: number } {
    const pagados = { ...articulosPagados };
    let totalCobrado = 0;

    if (tipoCobro === 'completo' || tipoCobro === 'dividir') {
      totalCobrado =
        tipoCobro === 'completo'
          ? this.getTotalPendiente(order, articulosPagados)
          : totalPorPersona * numeroComensales;
      order.items.forEach((item) => {
        if (!pagados[item.productId]) pagados[item.productId] = true;
      });
    } else {
      totalCobrado = this.getTotalSeleccionado(
        order,
        articulosSeleccionados,
        articulosPagados,
      );
      order.items.forEach((item) => {
        if (
          articulosSeleccionados[item.productId] &&
          !pagados[item.productId]
        ) {
          pagados[item.productId] = true;
        }
      });
    }

    return { articulosPagados: pagados, totalCobrado };
  }

  registrarVenta(orderId: string, userId: string | number): Promise<void> {
    return new Promise((resolve, reject) => {
      this.salesService
        .createSale({ order_id: orderId, user_id: userId as number })
        .subscribe({
          next: () => {
            this.salesUpdateService.notificarVentaCreada();
            resolve();
          },
          error: reject,
        });
    });
  }
}
