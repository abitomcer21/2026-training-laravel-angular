import { Component, EventEmitter, OnInit, Output } from '@angular/core';
import { CommonModule } from '@angular/common';
import {
  IonBadge,
  IonButton,
  IonContent,
  IonIcon,
  IonModal,
} from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';

import {
  printOutline,
  closeCircleOutline,
  closeOutline,
  returnDownBackOutline,
} from 'ionicons/icons';
import {
  listOutline,
  refreshOutline,
  checkmarkCircleOutline,
  fastFoodOutline,
} from 'ionicons/icons';
import {
  OrderStateService,
  OrderItem,
} from '../../../../services/order-state.service';
import { SalesService, Sale } from '../../../../services/api/sales.service';
import { UserService, User } from '../../../../services/api/user.service';

interface PedidoActivo {
  key: string;
  tableName: string;
  tableId: string;
  comensales: number;
  total: number;
  itemsCount: number;
  estado: 'En cocina' | 'Pagado';
  pedidoInicialEnviado: boolean;
  items: OrderItem[];
  order?: any;
}

@Component({
  selector: 'app-pedidos',
  templateUrl: './panel-pedidos.component.html',
  styleUrls: ['./panel-pedidos.component.scss'],
  standalone: true,
  imports: [CommonModule, IonIcon, IonBadge, IonModal, IonContent],
})
export class PedidosComponent implements OnInit {
  @Output() openTable = new EventEmitter<string>();

  kitchenOrders: PedidoActivo[] = [];
  paidOrders: Sale[] = [];
  loadingPaidOrders = false;
  loadingKitchenOrders = false;

  showPinModal = false;
  selectedOrderForPin: PedidoActivo | null = null;
  pinIngresado = '';
  pinError = '';

  showSaleModal = false;
  selectedSale: Sale | null = null;
  saleActionMessage = '';
  saleActionIsError = false;
  adminUsers: User[] = [];

  cancelledLines = new Set<string>();

  constructor(
    private orderStateService: OrderStateService,
    private salesService: SalesService,
    private userService: UserService,
  ) {
    addIcons({
      listOutline,
      refreshOutline,
      checkmarkCircleOutline,
      fastFoodOutline,
      printOutline,
      closeCircleOutline,
      closeOutline,
      returnDownBackOutline,
    });
  }

  ngOnInit() {
    this.refreshOrders();
    this.orderStateService.getActiveOrdersChanged().subscribe(() => {
      this.loadKitchenOrders();
    });
    this.userService.getUsers().subscribe({
      next: (res: any) => {
        this.adminUsers = (res.users ?? []).filter((u: User) =>
          ['admin', 'supervisor'].includes(u.role ?? ''),
        );
      },
    });
  }

  refreshOrders(): void {
    this.loadKitchenOrders();
    this.loadPaidOrders();
  }

  private loadKitchenOrders(): void {
    this.loadingKitchenOrders = true;
    const activos = this.orderStateService.getActivos();
    const pedidos: PedidoActivo[] = [];

    Object.entries(activos).forEach(([key, value]) => {
      if (!value || typeof value !== 'object') {
        return;
      }

      const estadoPedido = value as any;
      const order = estadoPedido.order;
      if (!order || !Array.isArray(order.items) || order.items.length === 0) {
        return;
      }

      const tableName =
        order.table?.name ||
        String(order.table?.id || key.replace('pedido_', ''));
      const tableId = String(order.table?.id || key.replace('pedido_', ''));
      const comensales = order.comensales || 1;
      const total = Number(estadoPedido.totalPorPagar || order.total || 0);
      const itemsCount = order.items.reduce(
        (sum: number, item: OrderItem) => sum + Number(item.quantity || 0),
        0,
      );
      const estado = total === 0 ? 'Pagado' : 'En cocina';

      pedidos.push({
        key,
        tableName,
        tableId,
        comensales,
        total,
        itemsCount,
        estado,
        pedidoInicialEnviado: Boolean(estadoPedido.pedidoInicialEnviado),
        items: order.items,
        order,
      });
    });

    this.kitchenOrders = pedidos.sort((a, b) =>
      a.tableId.localeCompare(b.tableId),
    );
    this.loadingKitchenOrders = false;
  }

  private loadPaidOrders(): void {
    this.loadingPaidOrders = true;
    this.salesService.getTodaySales().subscribe({
      next: (response) => {
        this.paidOrders = Array.isArray(response?.data) ? response.data : [];
        this.loadingPaidOrders = false;
      },
      error: () => {
        this.paidOrders = [];
        this.loadingPaidOrders = false;
      },
    });
  }

  getPaidLabel(sale: Sale): string {
    return `${sale.payment_method || 'efectivo'} • #${sale.ticket_number}`;
  }

  goToTable(tableId: string): void {
    const pedido = this.kitchenOrders.find((item) => item.tableId === tableId);
    if (!pedido) {
      return;
    }

    this.selectedOrderForPin = pedido;
    this.pinIngresado = '';
    this.pinError = '';
    this.showPinModal = true;
  }

  confirmPin(): void {
    const match = this.adminUsers.find((u) => u.pin === this.pinIngresado);

    if (!match) {
      this.pinError = 'PIN inválido';
      this.pinIngresado = '';
      return;
    }

    if (!this.selectedOrderForPin?.order?.table) {
      this.pinError = 'No se encontró la mesa del pedido.';
      return;
    }

    this.orderStateService.setTableAndUser(
      this.selectedOrderForPin.order.table,
      this.selectedOrderForPin.order.user,
    );

    this.showPinModal = false;
    const tableId = this.selectedOrderForPin.tableId;
    this.selectedOrderForPin = null;
    this.openTable.emit(tableId);
  }

  addDigit(digit: string): void {
    if (this.pinIngresado.length >= 4) {
      return;
    }

    this.pinIngresado += digit;
  }

  removeDigit(): void {
    this.pinIngresado = this.pinIngresado.slice(0, -1);
  }

  closePinModal(): void {
    this.showPinModal = false;
    this.selectedOrderForPin = null;
    this.pinIngresado = '';
    this.pinError = '';
  }

  formatCurrency(amount: number): string {
    return new Intl.NumberFormat('es-ES', {
      style: 'currency',
      currency: 'EUR',
    }).format(amount);
  }

  formatTime(dateString: string): string {
    const date = new Date(dateString);
    if (Number.isNaN(date.getTime())) {
      return dateString;
    }
    return date.toLocaleTimeString('es-ES', {
      hour: '2-digit',
      minute: '2-digit',
    });
  }

  openSaleDetail(sale: Sale): void {
    this.selectedSale = sale;
    this.cancelledLines.clear();
    this.saleActionMessage = '';
    this.saleActionIsError = false;
    this.showSaleModal = true;
  }
  reprintTicket(): void {
    this.saleActionMessage = `Ticket #${this.selectedSale?.ticket_number} enviado a impresora.`;
    this.saleActionIsError = false;
  }

  refundSale(): void {
    this.saleActionMessage = `Devolución de ${this.formatCurrency(this.selectedSale?.total ?? 0)} iniciada.`;
    this.saleActionIsError = false;
  }

  cancelSale(): void {
    if (!this.selectedSale) {
      return;
    }

    const saleId = this.selectedSale.id;

    this.salesService.cancelSale(saleId).subscribe({
      next: () => {
        this.paidOrders = this.paidOrders.filter((s) => s.id !== saleId);
        this.closeSaleModal();
      },
      error: () => {
        this.saleActionMessage = 'Error al anular la venta.';
        this.saleActionIsError = true;
      },
    });
  }

  cancelLine(lineId: string): void {
    this.salesService.cancelSalesLine(lineId).subscribe({
      next: () => {
        this.cancelledLines.add(lineId);

        if (this.selectedSale?.lines) {
          this.selectedSale = {
            ...this.selectedSale,
            lines: this.selectedSale.lines.filter((l) => l.id !== lineId),
          };

          const saleInList = this.paidOrders.find(
            (s) => s.id === this.selectedSale!.id,
          );
          if (saleInList) {
            saleInList.lines = this.selectedSale.lines;
          }
        }

        this.saleActionMessage = 'Línea anulada.';
        this.saleActionIsError = false;
      },
      error: () => {
        this.saleActionMessage = 'Error al anular la línea.';
        this.saleActionIsError = true;
      },
    });
  }

  closeSaleModal(): void {
    this.showSaleModal = false;
    this.selectedSale = null;
    this.saleActionMessage = '';
    this.cancelledLines.clear();
  }
}
