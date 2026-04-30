import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';
import { Table } from './api/table.service';
import { User } from './api/user.service';

export interface OrderItem {
  productId: string;
  productName: string;
  quantity: number;
  price: number;
  total: number;
}

export interface CurrentOrder {
  table: Table | null;
  user: User | null;
  items: OrderItem[];
  total: number;
}

export interface EstadoPedidoCompleto {
  order: CurrentOrder;
  pedidoInicialEnviado: boolean;
  itemsEnviadosACocina: OrderItem[];
  articulosPagados: { [key: string]: boolean };
  totalPagado: number;
  totalPorPagar: number;
}

@Injectable({
  providedIn: 'root',
})
export class OrderStateService {
  private readonly initialState: CurrentOrder = {
    table: null,
    user: null,
    items: [],
    total: 0,
  };

  private currentOrder$ = new BehaviorSubject<CurrentOrder>(this.initialState);
  
  // Estado adicional del pedido
  private pedidoInicialEnviado = false;
  private itemsEnviadosACocina: OrderItem[] = [];
  private articulosPagados: { [key: string]: boolean } = {};
  private totalPagado = 0;
  private totalPorPagar = 0;

  // Subjects para los estados adicionales
  private pedidoInicialEnviado$ = new BehaviorSubject<boolean>(false);
  private itemsEnviadosACocina$ = new BehaviorSubject<OrderItem[]>([]);
  private articulosPagados$ = new BehaviorSubject<{ [key: string]: boolean }>({});

  constructor() {
    this.loadFromStorage();
  }

  // Getters para los estados adicionales
  getPedidoInicialEnviado(): Observable<boolean> {
    return this.pedidoInicialEnviado$.asObservable();
  }

  getItemsEnviadosACocina(): Observable<OrderItem[]> {
    return this.itemsEnviadosACocina$.asObservable();
  }

  getArticulosPagados(): Observable<{ [key: string]: boolean }> {
    return this.articulosPagados$.asObservable();
  }

  getCurrentOrder(): Observable<CurrentOrder> {
    return this.currentOrder$.asObservable();
  }

  getCurrentOrderValue(): CurrentOrder {
    return this.currentOrder$.value;
  }

  getPedidoInicialEnviadoValue(): boolean {
    return this.pedidoInicialEnviado;
  }

  getItemsEnviadosACocinaValue(): OrderItem[] {
    return this.itemsEnviadosACocina;
  }

  getArticulosPagadosValue(): { [key: string]: boolean } {
    return this.articulosPagados;
  }

  setTableAndUser(table: Table, user: User): void {
    // Generar clave única para identificar el pedido de esta mesa+usuario
    const key = `${table.id}_${user.id}`;
    const savedState = this.loadStateFromStorage(key);
    
    if (savedState) {
      // Cargar estado guardado
      this.currentOrder$.next(savedState.order);
      this.pedidoInicialEnviado = savedState.pedidoInicialEnviado;
      this.itemsEnviadosACocina = savedState.itemsEnviadosACocina;
      this.articulosPagados = savedState.articulosPagados;
      this.totalPagado = savedState.totalPagado;
      this.totalPorPagar = savedState.totalPorPagar;
    } else {
      // Nuevo pedido
      const order = { ...this.initialState, table, user };
      this.currentOrder$.next(order);
      this.pedidoInicialEnviado = false;
      this.itemsEnviadosACocina = [];
      this.articulosPagados = {};
      this.totalPagado = 0;
      this.totalPorPagar = 0;
    }
    
    // Emitir los estados adicionales
    this.pedidoInicialEnviado$.next(this.pedidoInicialEnviado);
    this.itemsEnviadosACocina$.next(this.itemsEnviadosACocina);
    this.articulosPagados$.next(this.articulosPagados);
    
    this.saveCurrentStateToStorage(key);
  }

  addItem(item: OrderItem): void {
    const order = this.currentOrder$.value;
    const existingItem = order.items.find((i) => i.productId === item.productId);

    if (existingItem) {
      existingItem.quantity += item.quantity;
      existingItem.total = existingItem.quantity * existingItem.price;
    } else {
      order.items.push(item);
    }

    this.calculateTotal();
    this.currentOrder$.next(order);
    this.saveCurrentState();
  }

  removeItem(productId: string): void {
    const order = this.currentOrder$.value;
    order.items = order.items.filter((i) => i.productId !== productId);
    this.calculateTotal();
    this.currentOrder$.next(order);
    this.saveCurrentState();
  }

  updateItemQuantity(productId: string, quantity: number): void {
    const order = this.currentOrder$.value;
    const item = order.items.find((i) => i.productId === productId);

    if (item) {
      if (quantity <= 0) {
        this.removeItem(productId);
      } else {
        item.quantity = quantity;
        item.total = quantity * item.price;
        this.calculateTotal();
        this.currentOrder$.next(order);
        this.saveCurrentState();
      }
    }
  }

  clearOrder(): void {
    this.currentOrder$.next(this.initialState);
    this.pedidoInicialEnviado = false;
    this.itemsEnviadosACocina = [];
    this.articulosPagados = {};
    this.totalPagado = 0;
    this.totalPorPagar = 0;
    
    this.pedidoInicialEnviado$.next(false);
    this.itemsEnviadosACocina$.next([]);
    this.articulosPagados$.next({});
    
    this.saveCurrentState();
  }

  setPedidoInicialEnviado(enviado: boolean, itemsEnviados?: OrderItem[]): void {
    this.pedidoInicialEnviado = enviado;
    if (itemsEnviados) {
      this.itemsEnviadosACocina = itemsEnviados;
    }
    this.pedidoInicialEnviado$.next(this.pedidoInicialEnviado);
    this.itemsEnviadosACocina$.next(this.itemsEnviadosACocina);
    this.saveCurrentState();
  }

  setArticulosPagados(pagados: { [key: string]: boolean }): void {
    this.articulosPagados = pagados;
    this.articulosPagados$.next(this.articulosPagados);
    this.calcularTotalesPendientes();
    this.saveCurrentState();
  }

  updateArticuloPagado(productId: string, pagado: boolean): void {
    this.articulosPagados[productId] = pagado;
    this.articulosPagados$.next(this.articulosPagados);
    this.calcularTotalesPendientes();
    this.saveCurrentState();
  }

  getTotalesPendientes(): { totalPagado: number; totalPorPagar: number } {
    return { totalPagado: this.totalPagado, totalPorPagar: this.totalPorPagar };
  }

  private calcularTotalesPendientes(): void {
    let pagado = 0;
    let porPagar = 0;
    
    this.currentOrder$.value.items.forEach(item => {
      if (this.articulosPagados[item.productId]) {
        pagado += item.total;
      } else {
        porPagar += item.total;
      }
    });
    
    this.totalPagado = pagado;
    this.totalPorPagar = porPagar;
  }

  private calculateTotal(): void {
    const order = this.currentOrder$.value;
    order.total = order.items.reduce((sum, item) => sum + item.total, 0);
    this.calcularTotalesPendientes();
  }

  private saveCurrentState(): void {
    if (this.currentOrder$.value.table) {
      const key = `${this.currentOrder$.value.table.id}_${this.currentOrder$.value.user?.id}`;
      this.saveCurrentStateToStorage(key);
    }
  }

  private saveCurrentStateToStorage(key: string): void {
    const estadoCompleto: EstadoPedidoCompleto = {
      order: this.currentOrder$.value,
      pedidoInicialEnviado: this.pedidoInicialEnviado,
      itemsEnviadosACocina: this.itemsEnviadosACocina,
      articulosPagados: this.articulosPagados,
      totalPagado: this.totalPagado,
      totalPorPagar: this.totalPorPagar
    };
    localStorage.setItem(`pedido_${key}`, JSON.stringify(estadoCompleto));
  }

  private loadStateFromStorage(key: string): EstadoPedidoCompleto | null {
    const stored = localStorage.getItem(`pedido_${key}`);
    if (stored) {
      try {
        return JSON.parse(stored);
      } catch (error) {
        console.error('Error loading order state:', error);
        return null;
      }
    }
    return null;
  }

  private loadFromStorage(): void {
  }
}