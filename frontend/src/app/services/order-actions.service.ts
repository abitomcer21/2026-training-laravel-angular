import { Injectable } from '@angular/core';
import {
  CurrentOrder,
  OrderItem,
  OrderStateService,
} from './order-state.service';
import { OrderService } from './api/order.service';

@Injectable({ providedIn: 'root' })
export class OrderActionsService {
  constructor(
    private orderStateService: OrderStateService,
    private orderService: OrderService,
  ) {}

  crearPedidoInicial(
    order: CurrentOrder,
    restaurantId: string,
  ): Promise<string> {
    const payload = {
      restaurant_id: restaurantId,
      table_id: order.table!.id,
      opened_by_user_id: order.user!.id,
      status: 'open',
      diners: order.comensales || 1,
      order_lines: order.items.map((item) => ({
        product_id: item.productId,
        user_id: order.user!.id,
        quantity: item.quantity,
        price: Math.round(item.price * 100),
        tax_percentage: item.iva || 0,
      })),
    };

    return new Promise((resolve, reject) => {
      this.orderService.createOrder(payload).subscribe({
        next: (response: any) => {
          const orderId =
            response.id ??
            response.order_id ??
            response.orderId ??
            response.data?.id ??
            response.order?.id;
          resolve(orderId);
        },
        error: reject,
      });
    });
  }

anadirLineas(orderId: string, nuevosItems: OrderItem[], userId: number): Promise<void> {
        const payload = nuevosItems.map((item) => ({
        product_id: item.productId,
        user_id: userId,
        quantity: item.quantity,
        price: Math.round(item.price * 100),
        tax_percentage: item.iva || 0,
        }));

        return new Promise((resolve, reject) => {
        this.orderService.addOrderLines(orderId, payload).subscribe({
            next: () => resolve(),
            error: reject,
        });
        });
    }

calcularNuevosItems(
    itemsActuales: OrderItem[],
    itemsEnviados: OrderItem[],
        ): OrderItem[] {
            const nuevos: OrderItem[] = [];

            itemsActuales.forEach((item) => {
            const enviado = itemsEnviados.find((e) => e.productId === item.productId);
            if (!enviado) {
                nuevos.push({ ...item });
            } else if (item.quantity > enviado.quantity) {
                const diferencia = item.quantity - enviado.quantity;
                nuevos.push({
                ...item,
                quantity: diferencia,
                total: diferencia * item.price,
                });
            }
    });

        return nuevos;
    }

    limpiarOrden(tableId: string | null): void {
        if (tableId) {
        this.orderStateService.clearTableOrder(tableId);
        } else {
        this.orderStateService.clearOrder();
        }
    }
}
