import { Injectable } from '@angular/core';
import { OrderItem, CurrentOrder } from './order-state.service';

@Injectable({ providedIn: 'root' })
export class TicketService {
  private numeroTicketActual: string | null = null;

  getNumeroTicketActual(): string | null {
    return this.numeroTicketActual;
}

  generarYGuardarNumeroTicket(): string {
    const ultimo = localStorage.getItem('ultimoNumeroTicket');
    const numero = ultimo ? parseInt(ultimo, 10) + 1 : 1;
    this.setNumeroTicketActual(numero);

    return this.numeroTicketActual ?? '';
  }

  setNumeroTicketActual(numero: number | string): void {
    const valor = Number(numero);
    if (!Number.isFinite(valor) || valor < 1) return;

    localStorage.setItem('ultimoNumeroTicket', valor.toString());
    this.numeroTicketActual = `T-${valor.toString().padStart(3, '0')}`;
  }

  resetNumeroTicket(): void {
    this.numeroTicketActual = null;
  }

  generarTicket(order: CurrentOrder, tipoTicket?: 'PROVISIONAL'): string {
    const items: OrderItem[] = order?.items ?? [];

    if (tipoTicket !== 'PROVISIONAL' && !this.numeroTicketActual) {
      this.generarYGuardarNumeroTicket();
    }

    let ticket = '================================\n';
    ticket += '           RESTAURANTE\n';
    ticket += '================================\n';

    if (tipoTicket === 'PROVISIONAL') {
      ticket += 'Ticket: PROVISIONAL\n';
    } else if (this.numeroTicketActual) {
      ticket += `Ticket: ${this.numeroTicketActual}\n`;
    }

    ticket += `Mesa: ${order?.table?.name ?? 'N/A'}\n`;
    ticket += `Usuario: ${order?.user?.name ?? 'N/A'}\n`;
    ticket += `Fecha: ${new Date().toLocaleString()}\n`;
    ticket += '================================\n';
    ticket += 'Producto       Cant Precio IVA%\n';
    ticket += '--------------------------------\n';

    let subtotalSinIva = 0;
    let totalIva = 0;

    if (items.length > 0) {
      items.forEach((item) => {
        const ivaRate = (item.iva || 0) / 100;
        const precioConIva = item.price;
        const precioSinIva =
          ivaRate > 0 ? precioConIva / (1 + ivaRate) : precioConIva;
        const ivaItem = precioConIva - precioSinIva;

        subtotalSinIva += precioSinIva * item.quantity;
        totalIva += ivaItem * item.quantity;

        const nombre =
          item.productName.length > 12
            ? item.productName.substring(0, 11) + '.'
            : item.productName;
        const ivaDisplay = (item.iva || 0).toString().padStart(3);

        ticket += `${nombre.padEnd(12)} ${item.quantity.toString().padStart(2)}   ${precioConIva.toFixed(2).padStart(5)} ${ivaDisplay}%\n`;
      });
    } else {
      ticket += 'Sin items\n';
    }

    const totalConIva = subtotalSinIva + totalIva;
    ticket += '--------------------------------\n';
    ticket += `Subtotal: ${subtotalSinIva.toFixed(2).padStart(17)} €\n`;
    ticket += `IVA:      ${totalIva.toFixed(2).padStart(17)} €\n`;
    ticket += `TOTAL:    ${totalConIva.toFixed(2).padStart(17)} €\n`;
    ticket += '================================\n';
    ticket += 'Gracias por su visita\n';
    ticket += '================================\n';

    return ticket;
  }
}
