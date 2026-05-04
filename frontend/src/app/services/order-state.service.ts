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
  iva?: number;  // IVA en porcentaje (10, 21, etc)
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
  
  // Subject para notificar cambios en el estado de mesas
  private activeOrdersChanged$ = new BehaviorSubject<void>(undefined);

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
    // Normalizar IDs para consistencia
    const tableId = String(table.id);
    const userId = String(user.id);
    const key = `${tableId}_${userId}`;
    const savedState = this.loadStateFromStorage(key);
    
    if (savedState) {
      // Cargar estado guardado (no guardar de nuevo, solo cargar)
      this.currentOrder$.next(savedState.order);
      this.pedidoInicialEnviado = savedState.pedidoInicialEnviado;
      this.itemsEnviadosACocina = savedState.itemsEnviadosACocina;
      this.articulosPagados = savedState.articulosPagados;
      this.totalPagado = savedState.totalPagado;
      this.totalPorPagar = savedState.totalPorPagar;
    } else {
      // Nuevo pedido (no se ha iniciado aún)
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
  }

  initializeTableOrder(table: Table, user: User): void {
    // Normalizar IDs para consistencia
    const tableId = String(table.id);
    const userId = String(user.id);
    const key = `${tableId}_${userId}`;
    const order = { ...this.initialState, table, user };
    this.currentOrder$.next(order);
    this.pedidoInicialEnviado = false;
    this.itemsEnviadosACocina = [];
    this.articulosPagados = {};
    this.totalPagado = 0;
    this.totalPorPagar = 0;
    
    // Emitir los estados adicionales
    this.pedidoInicialEnviado$.next(false);
    this.itemsEnviadosACocina$.next([]);
    this.articulosPagados$.next({});
    
    // Guardar el estado inicial (marca la mesa como en uso)
    this.saveCurrentStateToStorage(key);
    
    // Notificar adicional para asegurar que se propaguen cambios a las mesas
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
    // Guardar la clave antes de resetear (para borrar de localStorage)
    if (this.currentOrder$.value.table && this.currentOrder$.value.user) {
      const key = `${this.currentOrder$.value.table.id}_${this.currentOrder$.value.user.id}`;
      localStorage.removeItem(`pedido_${key}`);
    }

    this.currentOrder$.next(this.initialState);
    this.pedidoInicialEnviado = false;
    this.itemsEnviadosACocina = [];
    this.articulosPagados = {};
    this.totalPagado = 0;
    this.totalPorPagar = 0;
    
    this.pedidoInicialEnviado$.next(false);
    this.itemsEnviadosACocina$.next([]);
    this.articulosPagados$.next({});
    
    // Notificar TRES VECES para asegurar que se propague correctamente
    this.activeOrdersChanged$.next();
    
    setTimeout(() => {
      this.activeOrdersChanged$.next();
    }, 50);
    
    setTimeout(() => {
      this.activeOrdersChanged$.next();
    }, 150);
  }

  clearTableOrder(tableId: string, userId: string | number): void {
    // Normalizar IDs para consistencia
    const normalizedTableId = String(tableId);
    const normalizedUserId = String(userId);
    const key = `${normalizedTableId}_${normalizedUserId}`;
    
    // Eliminar el pedido del usuario actual
    localStorage.removeItem(`pedido_${key}`);
    
    // TAMBIÉN eliminar todos los demás pedidos de esta mesa (de otros usuarios)
    // para evitar inconsistencias
    const prefix = `pedido_${normalizedTableId}_`;
    const keysAEliminar: string[] = [];
    
    try {
      for (let i = 0; i < localStorage.length; i++) {
        const storedKey = localStorage.key(i);
        if (storedKey && storedKey.startsWith(prefix) && storedKey !== `pedido_${key}`) {
          keysAEliminar.push(storedKey);
        }
      }
    } catch (e) {
      console.error('Error clearing all table orders:', e);
    }
    
    keysAEliminar.forEach(k => {
      localStorage.removeItem(k);
    });
    
    // Si es el pedido actual, también limpiar el estado
    if (this.currentOrder$.value.table?.id === tableId && 
        this.currentOrder$.value.user?.id === userId) {
      this.currentOrder$.next(this.initialState);
      this.pedidoInicialEnviado = false;
      this.itemsEnviadosACocina = [];
      this.articulosPagados = {};
      this.totalPagado = 0;
      this.totalPorPagar = 0;
      
      this.pedidoInicialEnviado$.next(false);
      this.itemsEnviadosACocina$.next([]);
      this.articulosPagados$.next({});
    }
    
    // Notificar TRES VECES para asegurar que se propague correctamente:
    // - Inmediatamente
    // - Después de 50ms
    // - Después de 150ms
    this.activeOrdersChanged$.next();
    
    setTimeout(() => {
      this.activeOrdersChanged$.next();
    }, 50);
    
    setTimeout(() => {
      this.activeOrdersChanged$.next();
    }, 150);
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

  hasOrderForTableAndUser(tableId: string, userId: string | number): boolean {
    const normalizedTableId = String(tableId);
    const normalizedUserId = String(userId);
    const key = `${normalizedTableId}_${normalizedUserId}`;
    const stored = localStorage.getItem(`pedido_${key}`);
    return stored !== null;
  }

  hasUnpaidItemsForTableAndUser(tableId: string, userId: string | number): boolean {
    const normalizedTableId = String(tableId);
    const normalizedUserId = String(userId);
    const key = `${normalizedTableId}_${normalizedUserId}`;
    const savedState = this.loadStateFromStorage(key);
    
    // Una mesa está ocupada si tiene un pedido activo (con items sin pagar)
    return savedState !== null && savedState.totalPorPagar > 0;
  }

  hasActiveOrderForTableAndUser(tableId: string, userId: string | number): boolean {
    const normalizedTableId = String(tableId);
    const normalizedUserId = String(userId);
    const key = `${normalizedTableId}_${normalizedUserId}`;
    const savedState = this.loadStateFromStorage(key);
    
    // Una mesa tiene un pedido activo si existe en localStorage (incluso sin items)
    return savedState !== null;
  }

  hasActiveOrderForTable(tableId: string): boolean {
    // Normalizar el tableId a string
    const normalizedTableId = String(tableId);
    const prefix = `pedido_${normalizedTableId}_`;
    
    try {
      for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (key && key.startsWith(prefix)) {
          const stored = localStorage.getItem(key);
          if (stored) {
            try {
              const state = JSON.parse(stored) as EstadoPedidoCompleto;
              // Una mesa está OCUPADA solo si tiene AL MENOS 1 ITEM
              // (aunque todos estén pagados, si hay items, está siendo usada)
              if (state.order?.items && state.order.items.length > 0) {
                return true;
              }
              // Si no tiene items, está vacía - eliminar registro
              localStorage.removeItem(key);
            } catch (e) {
              // JSON inválido, eliminar registro corrupto
              console.warn(`Registro corrupto en localStorage: ${key}`, e);
              localStorage.removeItem(key);
            }
          }
        }
      }
    } catch (e) {
      console.error('Error checking active orders:', e);
    }
    
    return false;
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
      const userId = String(this.currentOrder$.value.user?.id);
      const key = `${tableId}_${userId}`;
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
    
    // Notificar inmediatamente para que las mesas se actualicen
    this.activeOrdersChanged$.next();
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

  /**
   * Obtiene el primer pedido activo de una mesa (de cualquier usuario)
   * Útil para permitir que otro usuario continúe con un pedido existente
   */
  public getActiveOrderForTable(tableId: string): { key: string; data: EstadoPedidoCompleto } | null {
    const normalizedTableId = String(tableId);
    const prefix = `pedido_${normalizedTableId}_`;
    
    try {
      for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (key && key.startsWith(prefix)) {
          const stored = localStorage.getItem(key);
          if (stored) {
            try {
              const state = JSON.parse(stored) as EstadoPedidoCompleto;
              // Si tiene items, devolver este pedido
              if (state.order?.items && state.order.items.length > 0) {
                return { key, data: state };
              }
            } catch (e) {
              console.warn(`Registro corrupto: ${key}`);
            }
          }
        }
      }
    } catch (e) {
      console.error('Error getting active order for table:', e);
    }
    
    return null;
  }

  /**
   * Carga un pedido existente para un usuario diferente
   * Permite que otro usuario continúe con un pedido que fue iniciado por otro usuario
   */
  public loadExistingOrderForCurrentUser(table: Table, user: User, existingOrderData: EstadoPedidoCompleto): void {
    // Cargar el pedido existente pero cambiar el usuario a quien lo abre ahora
    const normalizedTableId = String(table.id);
    const normalizedUserId = String(user.id);
    const newKey = `${normalizedTableId}_${normalizedUserId}`;
    
    // Copiar el pedido existente pero con la nueva combinación mesa+usuario
    const newOrderData: EstadoPedidoCompleto = {
      ...existingOrderData,
      order: {
        ...existingOrderData.order,
        table,
        user
      }
    };
    
    // Guardar bajo la nueva clave
    localStorage.setItem(`pedido_${newKey}`, JSON.stringify(newOrderData));
    
    // Actualizar el estado del servicio
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
  }

  /**
   * Limpia todos los pedidos residuales (sin items)
   * Se ejecuta automáticamente al cargar las mesas
   */
  public limpiarPedidosResiduales(): void {
    const keysAEliminar: string[] = [];
    
    try {
      for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (key && key.startsWith('pedido_')) {
          const stored = localStorage.getItem(key);
          if (stored) {
            try {
              const state = JSON.parse(stored) as EstadoPedidoCompleto;
              // Eliminar si NO tiene items (una mesa sin items no está realmente ocupada)
              if (!state.order?.items || state.order.items.length === 0) {
                keysAEliminar.push(key);
              }
            } catch (e) {
              // Registro corrupto, eliminarlo
              console.warn(`Registro corrupto eliminado: ${key}`);
              keysAEliminar.push(key);
            }
          }
        }
      }
    } catch (e) {
      console.error('Error cleaning up orders:', e);
    }
    
    keysAEliminar.forEach(key => {
      localStorage.removeItem(key);
    });
    
    if (keysAEliminar.length > 0) {
      this.activeOrdersChanged$.next();
    }
  }

  /**
   * Devuelve un listado de todos los pedidos activos (para debugging)
   */
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
      console.error('Error getting active orders:', e);
    }
    
    return activos;
  }
}