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
  iva?: number;
}

export interface CurrentOrder {
  table: Table | null;
  user: User | null;
  items: OrderItem[];
  total: number;
  comensales?: number;
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
    comensales: 1,
  };

  private currentOrder$ = new BehaviorSubject<CurrentOrder>(this.initialState);

  private pedidoInicialEnviado = false;
  private itemsEnviadosACocina: OrderItem[] = [];
  private articulosPagados: { [key: string]: boolean } = {};
  private totalPagado = 0;
  private totalPorPagar = 0;

  private pedidoInicialEnviado$ = new BehaviorSubject<boolean>(false);
  private itemsEnviadosACocina$ = new BehaviorSubject<OrderItem[]>([]);
  private articulosPagados$ = new BehaviorSubject<{ [key: string]: boolean }>({});

  private activeOrdersChanged$ = new BehaviorSubject<void>(undefined);

  private blockStorageUntil = 0;

  private blockReloadUntil = 0;

  constructor() {
    this.loadFromStorage();
  }

  getPedidoInicialEnviado(): Observable<boolean> {
    return this.pedidoInicialEnviado$.asObservable();
  }

  getItemsEnviadosACocina(): Observable<OrderItem[]> {
    return this.itemsEnviadosACocina$.asObservable();
  }

  getArticulosPagados(): Observable<{ [key: string]: boolean }> {
    return this.articulosPagados$.asObservable();
  }

  getActiveOrdersChanged(): Observable<void> {
    return this.activeOrdersChanged$.asObservable();
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
    const tableId = String(table.id);
    const key = `${tableId}`;

    const ahora = Date.now();
    const tiempoRestante = this.blockReloadUntil - ahora;
    if (this.blockReloadUntil > ahora) {

      const order: CurrentOrder = {
        table,
        user,
        items: [],
        total: 0,
        comensales: 1
      };
      this.currentOrder$.next(order);
      this.pedidoInicialEnviado = false;
      this.itemsEnviadosACocina = [];
      this.articulosPagados = {};
      this.totalPagado = 0;
      this.totalPorPagar = 0;

      this.pedidoInicialEnviado$.next(this.pedidoInicialEnviado);
      this.itemsEnviadosACocina$.next(this.itemsEnviadosACocina);
      this.articulosPagados$.next(this.articulosPagados);

      return;
    }
    const savedState = this.loadStateFromStorage(key);

    if (savedState) {
      const updatedOrder: CurrentOrder = {
        ...savedState.order,
        table,
        user,
      };
      this.currentOrder$.next(updatedOrder);
      this.pedidoInicialEnviado = savedState.pedidoInicialEnviado;
      this.itemsEnviadosACocina = savedState.itemsEnviadosACocina;
      this.articulosPagados = savedState.articulosPagados;
      this.totalPagado = savedState.totalPagado;
      this.totalPorPagar = savedState.totalPorPagar;

    } else {

      const order: CurrentOrder = {
        table,
        user,
        items: [],
        total: 0,
        comensales: 1
      };
      this.currentOrder$.next(order);
      this.pedidoInicialEnviado = false;
      this.itemsEnviadosACocina = [];
      this.articulosPagados = {};
      this.totalPagado = 0;
      this.totalPorPagar = 0;

    }

    this.pedidoInicialEnviado$.next(this.pedidoInicialEnviado);
    this.itemsEnviadosACocina$.next(this.itemsEnviadosACocina);
    this.articulosPagados$.next(this.articulosPagados);

  }

  initializeTableOrder(table: Table, user: User): void {
    const tableId = String(table.id);
    const key = `${tableId}`;

    const comensalesActual = this.currentOrder$.value?.comensales || 1;

    const order: CurrentOrder = {
      table,
      user,
      items: [],
      total: 0,
      comensales: comensalesActual
    };
    this.currentOrder$.next(order);
    this.pedidoInicialEnviado = false;
    this.itemsEnviadosACocina = [];
    this.articulosPagados = {};
    this.totalPagado = 0;
    this.totalPorPagar = 0;

    this.pedidoInicialEnviado$.next(false);
    this.itemsEnviadosACocina$.next([]);
    this.articulosPagados$.next({});

    this.saveCurrentStateToStorage(key);
    setTimeout(() => {
      this.activeOrdersChanged$.next();
    }, 50);
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

    this.blockStorageUntil = Date.now() + 3000;

    if (this.currentOrder$.value.table) {
      const key = `${this.currentOrder$.value.table.id}`;
      localStorage.removeItem(`pedido_${key}`);
    }

    const cleanState: CurrentOrder = {
      table: null,
      user: null,
      items: [],
      total: 0
    };
    this.currentOrder$.next(cleanState);
    this.pedidoInicialEnviado = false;
    this.itemsEnviadosACocina = [];
    this.articulosPagados = {};
    this.totalPagado = 0;
    this.totalPorPagar = 0;

    this.pedidoInicialEnviado$.next(false);
    this.itemsEnviadosACocina$.next([]);
    this.articulosPagados$.next({});

    this.activeOrdersChanged$.next();

    setTimeout(() => {
      this.activeOrdersChanged$.next();
    }, 50);

    setTimeout(() => {
      this.activeOrdersChanged$.next();
    }, 150);

    setTimeout(() => {
      this.limpiarPedidosResiduales();
      this.activeOrdersChanged$.next();
    }, 300);

  }

  clearTableOrder(tableId: string): void {
    const normalizedTableId = String(tableId);
    const key = `${normalizedTableId}`;

    this.blockStorageUntil = Date.now() + 3000;
    this.blockReloadUntil = Date.now() + 10000;

    localStorage.removeItem(`pedido_${key}`);

    const currentTableId = this.currentOrder$.value.table?.id;
    const currentTableIdStr = currentTableId ? String(currentTableId) : null;

    if (currentTableIdStr === normalizedTableId) {
      const cleanState: CurrentOrder = {
        table: null,
        user: null,
        items: [],
        total: 0
      };
      this.currentOrder$.next(cleanState);
      this.pedidoInicialEnviado = false;
      this.itemsEnviadosACocina = [];
      this.articulosPagados = {};
      this.totalPagado = 0;
      this.totalPorPagar = 0;

      this.pedidoInicialEnviado$.next(false);
      this.itemsEnviadosACocina$.next([]);
      this.articulosPagados$.next({});
    }
    this.activeOrdersChanged$.next();

    setTimeout(() => {
      this.activeOrdersChanged$.next();
    }, 50);

    setTimeout(() => {
      this.activeOrdersChanged$.next();
    }, 150);

    setTimeout(() => {
      this.limpiarPedidosResiduales();
      this.activeOrdersChanged$.next();
    }, 300);
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

  hasOrderForTableAndUser(tableId: string): boolean {
    const normalizedTableId = String(tableId);
    const key = `${normalizedTableId}`;
    const stored = localStorage.getItem(`pedido_${key}`);
    return stored !== null;
  }

  hasUnpaidItemsForTableAndUser(tableId: string): boolean {
    const normalizedTableId = String(tableId);
    const key = `${normalizedTableId}`;
    const savedState = this.loadStateFromStorage(key);
    return savedState !== null && savedState.totalPorPagar > 0;
  }

  hasActiveOrderForTableAndUser(tableId: string): boolean {
    const normalizedTableId = String(tableId);
    const key = `${normalizedTableId}`;
    const savedState = this.loadStateFromStorage(key);
    return savedState !== null;
  }

  hasActiveOrderForTable(tableId: string): boolean {
    const normalizedTableId = String(tableId);
    const key = `pedido_${normalizedTableId}`;

    try {
      const stored = localStorage.getItem(key);
      if (stored) {
        try {
          const state = JSON.parse(stored) as EstadoPedidoCompleto;
          if (state.order?.items && state.order.items.length > 0) {
            return true;
          }
          localStorage.removeItem(key);
        } catch (e) {
          localStorage.removeItem(key);
        }
      }
    } catch (e) {
    }

    return false;
  }
  getTableOccupiedInfo(tableId: string): { comensales: number; total: number } | null {
    const normalizedTableId = String(tableId);
    const key = `pedido_${normalizedTableId}`;

    try {
      const stored = localStorage.getItem(key);
      if (stored) {
        try {
          const state = JSON.parse(stored) as EstadoPedidoCompleto;
          if (state.order?.items && state.order.items.length > 0) {
            const totalComensales = state.order.comensales || 1;
            const totalCuenta = state.order.total || 0;
            return { comensales: totalComensales, total: totalCuenta };
          }
        } catch (e) {
        }
      }
    } catch (e) {
    }

    return null;
  }

  setComensales(comensales: number): void {
    const currentOrder = this.currentOrder$.value;
    if (currentOrder) {
      const updatedOrder: CurrentOrder = {
        ...currentOrder,
        comensales: comensales,
      };
      this.currentOrder$.next(updatedOrder);

      // Guardar en localStorage
      if (currentOrder.table) {
        const tableId = String(currentOrder.table.id);
        const key = `${tableId}`;
        this.saveCurrentStateToStorage(key);
      }
    }
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
      const tableId = String(this.currentOrder$.value.table.id);
      const key = `${tableId}`;

      this.saveCurrentStateToStorage(key);
    }
  }

  private saveCurrentStateToStorage(key: string): void {
    if (Date.now() < this.blockStorageUntil) {
      return;
    }
    const itemsCount = this.currentOrder$.value.items?.length || 0;
    if (itemsCount === 0) {
      return;
    }

    const estadoCompleto: EstadoPedidoCompleto = {
      order: this.currentOrder$.value,
      pedidoInicialEnviado: this.pedidoInicialEnviado,
      itemsEnviadosACocina: this.itemsEnviadosACocina,
      articulosPagados: this.articulosPagados,
      totalPagado: this.totalPagado,
      totalPorPagar: this.totalPorPagar
    };

    const storageKey = `pedido_${key}`;
    localStorage.setItem(storageKey, JSON.stringify(estadoCompleto));
    this.activeOrdersChanged$.next();
  }

  private loadStateFromStorage(key: string): EstadoPedidoCompleto | null {
    const searchKey = `pedido_${key}`;
    const stored = localStorage.getItem(searchKey);

    if (stored) {
      try {
        const state = JSON.parse(stored) as EstadoPedidoCompleto;
      if (!state.order?.items || state.order.items.length === 0) {
          return null;
        }
        if (state.order.items.length > 0 && state.totalPorPagar === 0) {
          return null;
        }

        return state;
      } catch (error) {
        return null;
      }
    }
    return null;
  }

  public getActiveOrderForTable(tableId: string): { key: string; data: EstadoPedidoCompleto } | null {
    const normalizedTableId = String(tableId);
    const key = `pedido_${normalizedTableId}`;

    try {
      const stored = localStorage.getItem(key);
      if (stored) {
        try {
          const state = JSON.parse(stored) as EstadoPedidoCompleto;
          if (state.order?.items && state.order.items.length > 0) {
            return { key, data: state };
          }
        } catch (e) {
          console.warn(`Registro corrupto: ${key}`);
        }
      }
    } catch (e) {
      console.error('Error obteniendo pedido activo para la mesa:', e);
    }

    return null;
  }


  public loadExistingOrderForCurrentUser(table: Table, user: User, existingOrderData: EstadoPedidoCompleto): void {
    const normalizedTableId = String(table.id);
    const newKey = `${normalizedTableId}`;

    const newOrderData: EstadoPedidoCompleto = {
      ...existingOrderData,
      order: {
        ...existingOrderData.order,
        table,
        user
      }
    };

    localStorage.setItem(`pedido_${newKey}`, JSON.stringify(newOrderData));

    this.currentOrder$.next(newOrderData.order);
    this.pedidoInicialEnviado = newOrderData.pedidoInicialEnviado;
    this.itemsEnviadosACocina = newOrderData.itemsEnviadosACocina;
    this.articulosPagados = newOrderData.articulosPagados;
    this.totalPagado = newOrderData.totalPagado;
    this.totalPorPagar = newOrderData.totalPorPagar;

    // Emitir los estados
    this.pedidoInicialEnviado$.next(this.pedidoInicialEnviado);
    this.itemsEnviadosACocina$.next(this.itemsEnviadosACocina);
    this.articulosPagados$.next(this.articulosPagados);
  }

  private loadFromStorage(): void {
    this.debugLocalStorage();
  }

  public debugLocalStorage(): void {
    let encontradas = 0;

    try {
      for (let i = 0; i < localStorage.length; i++) {
        const storedKey = localStorage.key(i);
        if (storedKey && storedKey.startsWith('pedido_')) {
          encontradas++;
          const content = localStorage.getItem(storedKey);

          try {
            const parsed = JSON.parse(content || '{}');
          } catch (e) {
          }
        }
      }

      if (encontradas === 0) {
      }
    } catch (e) {
    }
  }

  public limpiarPedidosResiduales(): void {

    const keysAEliminar: string[] = [];
    const currentTableId = this.currentOrder$.value.table?.id;

    try {
      for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (key && key.startsWith('pedido_')) {
          const stored = localStorage.getItem(key);
          if (stored) {
            try {
              const state = JSON.parse(stored) as EstadoPedidoCompleto;

              if (!state.order?.items || state.order.items.length === 0) {
                keysAEliminar.push(key);
              }
              else if (state.order.items.length > 0 && state.totalPorPagar === 0) {
                keysAEliminar.push(key);
              }
            } catch (e) {
            }
          }
        }
      }

      if (keysAEliminar.length > 0) {

        keysAEliminar.forEach(k => {
          localStorage.removeItem(k);
          const tableIdFromKey = k.substring('pedido_'.length);
          const currentTableIdStr = currentTableId ? String(currentTableId) : null;

          if (currentTableIdStr === tableIdFromKey) {

            const currentOrder = this.currentOrder$.value;
            const cleanedOrder: CurrentOrder = {
              table: currentOrder.table,  
              user: currentOrder.user,  
              items: [],
              total: 0,
              comensales: currentOrder.comensales
            };


            this.currentOrder$.next(cleanedOrder);
            this.pedidoInicialEnviado = false;
            this.itemsEnviadosACocina = [];
            this.articulosPagados = {};
            this.totalPagado = 0;
            this.totalPorPagar = 0;

            this.pedidoInicialEnviado$.next(false);
            this.itemsEnviadosACocina$.next([]);
            this.articulosPagados$.next({});
          }
        });

        setTimeout(() => {
          this.activeOrdersChanged$.next();
        }, 10);
      } else {
      }
    } catch (e) {
    }
  }

  public getActivos(): { [key: string]: any } {
    const activos: { [key: string]: any } = {};

    try {
      for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (key && key.startsWith('pedido_')) {
          const stored = localStorage.getItem(key);
          if (stored) {
            try {
              activos[key] = JSON.parse(stored);
            } catch (e) {
              activos[key] = { error: 'JSON corrupto' };
            }
          }
        }
      }
    } catch (e) {
    }

    return activos;
  }
}