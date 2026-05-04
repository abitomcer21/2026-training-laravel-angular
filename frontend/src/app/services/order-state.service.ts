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
  comensales?: number;  // Número de comensales en la mesa
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
  
  // Flag para bloquear almacenamiento temporal (usado después de limpiar)
  private blockStorageUntil = 0;
  
  // Flag para bloquear CARGA/RECARGA de órdenes después de limpiar (evita ciclos de reaparición)
  private blockReloadUntil = 0;

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
    
    console.log(`\n\n🟢🟢🟢 === ABRIENDO NUEVA MESA ===`);
    console.log(`🔄 [setTableAndUser] Configurando mesa y usuario`);
    console.log(`   table.id: ${table.id} (tipo: ${typeof table.id})`);
    console.log(`   table.uuid: ${table.uuid}`);
    console.log(`   user.id: ${user.id} (tipo: ${typeof user.id})`);
    console.log(`   user.uuid: ${user.uuid}`);
    console.log(`   → tableId normalizado: ${tableId}`);
    console.log(`   → userId normalizado: ${userId}`);
    console.log(`   → Clave construida: ${key}`);
    console.log(`   → Buscando en localStorage: pedido_${key}`);
    
    // 🔐 PROTECCIÓN: Revisar estado del bloqueo
    const ahora = Date.now();
    const tiempoRestante = this.blockReloadUntil - ahora;
    console.log(`\n🔍 ESTADO DEL BLOQUEO:`);
    console.log(`   blockReloadUntil: ${this.blockReloadUntil}`);
    console.log(`   Ahora: ${ahora}`);
    console.log(`   Tiempo restante: ${tiempoRestante}ms (${Math.round(tiempoRestante / 1000)}s)`);
    
    // 🔐 PROTECCIÓN: Si hay un bloqueo de recarga activo, simplemente crear nuevo pedido sin cargar
    if (this.blockReloadUntil > ahora) {
      console.log(`\n🚫 BLOQUEO DE RECARGA ACTIVO - NO se cargará orden anterior`);
      console.log(`   Tiempo restante: ${Math.round(tiempoRestante / 1000)}s`);
      
      // Crear nuevo objeto explícitamente (no shallow copy)
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
      
      console.log(`   Mesa NUEVA (bloqueada de recarga) - lista para nuevo pedido`);
      
      // Emitir los estados adicionales
      this.pedidoInicialEnviado$.next(this.pedidoInicialEnviado);
      this.itemsEnviadosACocina$.next(this.itemsEnviadosACocina);
      this.articulosPagados$.next(this.articulosPagados);
      
      console.log(`✅ [setTableAndUser] Completado\n`);
      return;
    }
    
    console.log(`\n✅ Bloqueo EXPIRADO - procediendo a cargar orden si existe\n`);
    
    const savedState = this.loadStateFromStorage(key);
    
    if (savedState) {
      console.log(`\n🔴 ADVERTENCIA: Se encontró estado guardado - cargando pedido existente`);
      console.log(`   ESTADO CARGADO - continuando con pedido existente`);
      console.log(`   Items cargados: ${savedState.order?.items?.length || 0}`);
      
      // Cargar estado guardado (no guardar de nuevo, solo cargar)
      this.currentOrder$.next(savedState.order);
      this.pedidoInicialEnviado = savedState.pedidoInicialEnviado;
      this.itemsEnviadosACocina = savedState.itemsEnviadosACocina;
      this.articulosPagados = savedState.articulosPagados;
      this.totalPagado = savedState.totalPagado;
      this.totalPorPagar = savedState.totalPorPagar;
      
      console.log(`   📦 Orden cargada - Total: ${this.currentOrder$.value.total}, Pagado: ${this.totalPagado}, Por pagar: ${this.totalPorPagar}`);
    } else {
      console.log(`\n✅ NO se encontró estado guardado - CREANDO NUEVO PEDIDO VACÍO`);
      
      // Nuevo pedido (no se ha iniciado aún) - crear objeto explícitamente
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
      
      console.log(`   Mesa lista para nuevo pedido`);
    }
    
    // Emitir los estados adicionales
    this.pedidoInicialEnviado$.next(this.pedidoInicialEnviado);
    this.itemsEnviadosACocina$.next(this.itemsEnviadosACocina);
    this.articulosPagados$.next(this.articulosPagados);
    
    console.log(`✅ [setTableAndUser] Completado\n`);
  }

  initializeTableOrder(table: Table, user: User): void {
    // Normalizar IDs para consistencia
    const tableId = String(table.id);
    const userId = String(user.id);
    const key = `${tableId}_${userId}`;
    
    // Obtener comensales del estado actual si ya existen
    const comensalesActual = this.currentOrder$.value?.comensales || 1;
    
    // Crear orden explícitamente (no shallow copy)
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
    console.log(`\n➕ [addItem] Agregando producto: ${item.productName} x${item.quantity}`);
    
    const order = this.currentOrder$.value;
    console.log(`   table.id: ${order.table?.id} (tipo: ${typeof order.table?.id})`);
    console.log(`   user.id: ${order.user?.id} (tipo: ${typeof order.user?.id})`);
    
    const existingItem = order.items.find((i) => i.productId === item.productId);

    if (existingItem) {
      existingItem.quantity += item.quantity;
      existingItem.total = existingItem.quantity * existingItem.price;
      console.log(`   (actualizada cantidad existente)`);
    } else {
      order.items.push(item);
      console.log(`   (nuevo producto agregado)`);
    }

    this.calculateTotal();
    this.currentOrder$.next(order);
    console.log(`   Items totales en orden: ${order.items.length}, Total: ${this.currentOrder$.value.total}€`);
    
    this.saveCurrentState();
    console.log();
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
    console.log(`\n🗑️ [clearOrder] Limpiando orden completamente`);
    
    // BLOQUEAR ALMACENAMIENTO por 3 segundos
    this.blockStorageUntil = Date.now() + 3000;
    console.log(`   🔒 Almacenamiento bloqueado por 3 segundos`);
    
    // Guardar la clave antes de resetear (para borrar de localStorage)
    if (this.currentOrder$.value.table && this.currentOrder$.value.user) {
      const key = `${this.currentOrder$.value.table.id}_${this.currentOrder$.value.user.id}`;
      localStorage.removeItem(`pedido_${key}`);
      console.log(`   ✓ Eliminada de localStorage: pedido_${key}`);
    }

    // Crear nuevo objeto limpio (no usar referencia a initialState para evitar referencias compartidas)
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
    
    // Notificar CUATRO VECES para asegurar que se propague correctamente
    this.activeOrdersChanged$.next();
    console.log(`   📢 Notificación 1/4`);
    
    setTimeout(() => {
      this.activeOrdersChanged$.next();
      console.log(`   📢 Notificación 2/4`);
    }, 50);
    
    setTimeout(() => {
      this.activeOrdersChanged$.next();
      console.log(`   📢 Notificación 3/4`);
    }, 150);
    
    setTimeout(() => {
      // EJECUTAR LIMPIEZA RESIDUAL después de todo
      this.limpiarPedidosResiduales();
      console.log(`   📢 Notificación 4/4 (después de limpieza residual)`);
      this.activeOrdersChanged$.next();
    }, 300);
    
    console.log(`✅ [clearOrder] Completado\n`);
  }

  clearTableOrder(tableId: string, userId: string | number): void {
    // Normalizar IDs para consistencia
    const normalizedTableId = String(tableId);
    const normalizedUserId = String(userId);
    const key = `${normalizedTableId}_${normalizedUserId}`;
    
    console.log(`\n🗑️ [clearTableOrder] Iniciando limpieza COMPLETA`);
    console.log(`   TableID: ${normalizedTableId}, UserID: ${normalizedUserId}`);
    console.log(`   Clave del usuario actual: pedido_${key}`);
    
    // BLOQUEAR ALMACENAMIENTO durante 3 segundos para evitar que se guarde nuevamente
    this.blockStorageUntil = Date.now() + 3000;
    console.log(`   🔒 Almacenamiento bloqueado por 3 segundos`);
    
    // 🔐 BLOQUEAR RECARGA DE ÓRDENES durante 10 segundos para evitar ciclos de reaparición
    this.blockReloadUntil = Date.now() + 10000;
    console.log(`   🔐 RECARGA DE ÓRDENES BLOQUEADA por 10 segundos (prevenir reaparición)`);
    console.log(`   blockReloadUntil timestamp: ${this.blockReloadUntil}`);
    
    
    // Eliminar el pedido del usuario actual
    localStorage.removeItem(`pedido_${key}`);
    console.log(`   ✓ Eliminada clave del usuario actual`);
    
    // TAMBIÉN eliminar TODAS las demás órdenes de esta mesa (de todos los usuarios)
    const prefix = `pedido_${normalizedTableId}_`;
    const keysAEliminar: string[] = [];
    
    console.log(`   Buscando todas las órdenes de la mesa (prefijo: ${prefix})`);
    
    try {
      for (let i = 0; i < localStorage.length; i++) {
        const storedKey = localStorage.key(i);
        if (storedKey && storedKey.startsWith(prefix)) {
          console.log(`   Encontrada: ${storedKey}`);
          keysAEliminar.push(storedKey);
        }
      }
    } catch (e) {
      console.error('❌ Error iterando localStorage:', e);
    }
    
    console.log(`   Total de claves a eliminar: ${keysAEliminar.length}`);
    
    keysAEliminar.forEach(k => {
      localStorage.removeItem(k);
      console.log(`   ✓ Eliminada: ${k}`);
    });
    
    // Si es el pedido actual, también limpiar el estado del servicio
    const currentTableId = this.currentOrder$.value.table?.id;
    const currentUserId = this.currentOrder$.value.user?.id;
    const currentTableIdStr = currentTableId ? String(currentTableId) : null;
    const currentUserIdStr = currentUserId ? String(currentUserId) : null;
    
    console.log(`   Estado actual - TableID: ${currentTableIdStr}, UserID: ${currentUserIdStr}`);
    
    if (currentTableIdStr === normalizedTableId && currentUserIdStr === normalizedUserId) {
      console.log(`   ✓ Limpiando estado del servicio (es el orden actual)`);
      
      // Crear nuevo objeto limpio (no usar referencia a initialState para evitar referencias compartidas)
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
    
    // Notificar CUATRO VECES para asegurar que se propague correctamente
    this.activeOrdersChanged$.next();
    console.log(`   📢 Notificación 1/4`);
    
    setTimeout(() => {
      this.activeOrdersChanged$.next();
      console.log(`   📢 Notificación 2/4`);
    }, 50);
    
    setTimeout(() => {
      this.activeOrdersChanged$.next();
      console.log(`   📢 Notificación 3/4`);
    }, 150);
    
    setTimeout(() => {
      // EJECUTAR LIMPIEZA RESIDUAL después de todo
      this.limpiarPedidosResiduales();
      console.log(`   📢 Notificación 4/4 (después de limpieza residual)`);
      this.activeOrdersChanged$.next();
    }, 300);
    
    console.log(`✅ [clearTableOrder] Limpieza completada\n`);
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

  // Obtener información de mesa ocupada (comensales y total)
  getTableOccupiedInfo(tableId: string): { comensales: number; total: number } | null {
    const normalizedTableId = String(tableId);
    const prefix = `pedido_${normalizedTableId}_`;
    
    let totalComensales = 0;
    let totalCuenta = 0;
    let found = false;

    try {
      for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (key && key.startsWith(prefix)) {
          const stored = localStorage.getItem(key);
          if (stored) {
            try {
              const state = JSON.parse(stored) as EstadoPedidoCompleto;
              if (state.order?.items && state.order.items.length > 0) {
                found = true;
                // Usar el número real de comensales guardado en cada pedido
                totalComensales += (state.order.comensales || 1);
                // Sumar total de la cuenta
                if (state.order.total) {
                  totalCuenta += state.order.total;
                }
              }
            } catch (e) {
              // JSON inválido, ignorar
              console.warn(`Registro corrupto en localStorage: ${key}`, e);
            }
          }
        }
      }
    } catch (e) {
      console.error('Error getting table occupied info:', e);
    }

    return found ? { comensales: totalComensales, total: totalCuenta } : null;
  }

  // Guardar el número de comensales en el pedido actual
  setComensales(comensales: number): void {
    const currentOrder = this.currentOrder$.value;
    if (currentOrder) {
      const updatedOrder: CurrentOrder = {
        ...currentOrder,
        comensales: comensales,
      };
      this.currentOrder$.next(updatedOrder);
      
      // Guardar en localStorage
      if (currentOrder.table && currentOrder.user) {
        const tableId = String(currentOrder.table.id);
        const userId = String(currentOrder.user.id);
        const key = `${tableId}_${userId}`;
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
      const userId = String(this.currentOrder$.value.user?.id);
      const key = `${tableId}_${userId}`;
      
      console.log(`💾 [saveCurrentState] Guardando estado`);
      console.log(`   TableID: ${tableId}`);
      console.log(`   UserID: ${userId}`);
      console.log(`   Clave: ${key}`);
      console.log(`   Clave completa: pedido_${key}`);
      console.log(`   Items: ${this.currentOrder$.value.items.length}`);
      
      this.saveCurrentStateToStorage(key);
    }
  }

  private saveCurrentStateToStorage(key: string): void {
    // Si el almacenamiento está bloqueado (ej: después de limpiar), no guardar
    if (Date.now() < this.blockStorageUntil) {
      console.log(`   🔒 BLOQUEADO - no se guarda (${Math.round(this.blockStorageUntil - Date.now())}ms restantes)`);
      return;
    }
    
    // 🚫 PROTECCIÓN: NO guardar órdenes vacías (sin items)
    const itemsCount = this.currentOrder$.value.items?.length || 0;
    if (itemsCount === 0) {
      console.log(`   🚫 NO SE GUARDA - orden vacía (0 items). Solo se guardará cuando haya items.`);
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
    
    console.log(`   💾 GUARDADO en localStorage:`);
    console.log(`      Clave: ${storageKey}`);
    console.log(`      Items: ${estadoCompleto.order?.items?.length || 0}`);
    console.log(`      Total: ${estadoCompleto.order?.total}€`);
    
    // Notificar inmediatamente para que las mesas se actualicen
    this.activeOrdersChanged$.next();
  }

  private loadStateFromStorage(key: string): EstadoPedidoCompleto | null {
    const searchKey = `pedido_${key}`;
    const stored = localStorage.getItem(searchKey);
    
    if (stored) {
      try {
        const state = JSON.parse(stored) as EstadoPedidoCompleto;
        
        console.log(`   📖 ENCONTRADO en localStorage: ${searchKey}`);
        console.log(`      Items: ${state.order?.items?.length || 0}, Pagado: ${state.totalPagado}, Por pagar: ${state.totalPorPagar}`);
        
        // VALIDACIÓN 1: Si no hay items, considerarlo como un pedido vacío/eliminado
        if (!state.order?.items || state.order.items.length === 0) {
          console.log(`      ⚠️ Sin items - RECHAZADO`);
          return null;
        }
        
        // VALIDACIÓN 2: Si tiene items PERO totalPorPagar === 0, es un residuo de pago
        if (state.order.items.length > 0 && state.totalPorPagar === 0) {
          console.log(`      ⚠️ RECHAZADO: residuo de pago (${state.order.items.length} items sin deuda)`);
          return null;
        }
        
        console.log(`      ✅ ACEPTADO - orden válida`);
        return state;
      } catch (error) {
        console.error('❌ Error parsing order state:', error);
        return null;
      }
    }
    
    console.log(`   📖 NO ENCONTRADO: ${searchKey}`);
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
    console.log('\n📂 [loadFromStorage] Inspeccionando localStorage al iniciar servicio...');
    this.debugLocalStorage();
  }

  public debugLocalStorage(): void {
    console.log('\n🔍 === DEBUG COMPLETO DE localStorage ===');
    console.log(`Total de claves en localStorage: ${localStorage.length}`);
    
    console.log('\n📋 Todas las claves con prefijo "pedido_":');
    let encontradas = 0;
    
    try {
      for (let i = 0; i < localStorage.length; i++) {
        const storedKey = localStorage.key(i);
        if (storedKey && storedKey.startsWith('pedido_')) {
          encontradas++;
          const content = localStorage.getItem(storedKey);
          
          try {
            const parsed = JSON.parse(content || '{}');
            console.log(`\n${encontradas}. ${storedKey}`);
            console.log(`   table.id: ${parsed.order?.table?.id}`);
            console.log(`   table.uuid: ${parsed.order?.table?.uuid}`);
            console.log(`   user.id: ${parsed.order?.user?.id}`);
            console.log(`   user.uuid: ${parsed.order?.user?.uuid}`);
            console.log(`   items: ${parsed.order?.items?.length || 0}`);
            console.log(`   totalPagado: ${parsed.totalPagado}`);
            console.log(`   totalPorPagar: ${parsed.totalPorPagar}`);
          } catch (e) {
            console.log(`${encontradas}. ${storedKey} [contenido inválido]`);
          }
        }
      }
      
      if (encontradas === 0) {
        console.log('   (vacío - no hay pedidos guardados)');
      }
    } catch (e) {
      console.error('❌ Error leyendo localStorage:', e);
    }
    
    console.log('\n=== FIN DEBUG ===\n');
  }

  public debugBlockStatus(): void {
    const ahora = Date.now();
    console.log('\n🔐 === DEBUG ESTADO DE BLOQUEOS ===');
    console.log(`Hora actual: ${ahora}`);
    console.log(`blockStorageUntil: ${this.blockStorageUntil}`);
    console.log(`blockReloadUntil: ${this.blockReloadUntil}`);
    console.log(`\nAlmacenamiento bloqueado: ${this.blockStorageUntil > ahora ? `SÍ (${Math.round((this.blockStorageUntil - ahora) / 1000)}s)` : 'NO'}`);
    console.log(`Recarga bloqueada: ${this.blockReloadUntil > ahora ? `SÍ (${Math.round((this.blockReloadUntil - ahora) / 1000)}s)` : 'NO'}`);
    console.log('=== FIN DEBUG BLOQUEOS ===\n');
  }

  public limpiarPedidosResiduales(): void {
    console.log(`\n🧹 [limpiarPedidosResiduales] Limpiando pedidos sin deuda pendiente...`);
    
    const keysAEliminar: string[] = [];
    const currentTableId = this.currentOrder$.value.table?.id;
    const currentUserId = this.currentOrder$.value.user?.id;
    
    try {
      for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (key && key.startsWith('pedido_')) {
          const stored = localStorage.getItem(key);
          if (stored) {
            try {
              const state = JSON.parse(stored) as EstadoPedidoCompleto;
              
              // CRITERIO 1: Sin items - definitivamente debe eliminarse
              if (!state.order?.items || state.order.items.length === 0) {
                console.log(`   ✓ ${key} - sin items`);
                keysAEliminar.push(key);
              }
              // CRITERIO 2: Tiene items PERO totalPorPagar === 0 (fue pagado completamente pero no limpiado)
              else if (state.order.items.length > 0 && state.totalPorPagar === 0) {
                console.log(`   ✓ ${key} - ${state.order.items.length} items SIN deuda (totalPorPagar=0) - RESIDUO DE PAGO`);
                keysAEliminar.push(key);
              }
            } catch (e) {
              console.warn(`   ⚠️ ${key} - contenido corrupto`);
            }
          }
        }
      }
      
      if (keysAEliminar.length > 0) {
        console.log(`\n   Eliminando ${keysAEliminar.length} pedido(s) residual(es)...`);
        
        keysAEliminar.forEach(k => {
          localStorage.removeItem(k);
          console.log(`   ✓ Eliminada de localStorage: ${k}`);
          
          // 🔑 Si este residuo es el pedido actual en memoria, también resetear el estado en memoria
          // Extraer tableId y userId del formato: pedido_[tableId]_[userId]
          const lastUnderscoreIndex = k.lastIndexOf('_');
          const tableIdFromKey = k.substring('pedido_'.length, lastUnderscoreIndex);
          const userIdFromKey = k.substring(lastUnderscoreIndex + 1);
          
          const currentTableIdStr = currentTableId ? String(currentTableId) : null;
          const currentUserIdStr = currentUserId ? String(currentUserId) : null;
          
          if (currentTableIdStr === tableIdFromKey && currentUserIdStr === userIdFromKey) {
            console.log(`   ⚠️ Este es el pedido actual en memoria (${tableIdFromKey}_${userIdFromKey}) - reseteando...`);
            
            // 🔑 IMPORTANTE: Crear nuevo objeto EXPLÍCITAMENTE (no shallow copy) para evitar referencias compartidas
            const currentOrder = this.currentOrder$.value;
            const cleanedOrder: CurrentOrder = {
              table: currentOrder.table,  // ← Preservar table
              user: currentOrder.user,    // ← Preservar user
              items: [],                  // ← EXPLÍCITAMENTE nuevo array vacío
              total: 0,                   // ← EXPLÍCITAMENTE 0
              comensales: currentOrder.comensales  // ← Preservar comensales
            };
            
            console.log(`   Reseteando con order: table=${cleanedOrder.table?.id}, user=${cleanedOrder.user?.id}, items=${cleanedOrder.items.length}`);
            
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
        
        console.log(`✅ Limpieza completada - notificando cambios`);
        
        // Notificar DESPUÉS de resetear todo para que `suscribirseAorden()` vea el estado nuevo
        setTimeout(() => {
          this.activeOrdersChanged$.next();
          console.log(`📢 Notificación enviada después de limpieza\n`);
        }, 10);
      } else {
        console.log(`   (ningún residuo encontrado)\n`);
      }
    } catch (e) {
      console.error('❌ Error limpiando pedidos:', e);
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