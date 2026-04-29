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

  // Mapa de pedidos por mesa+usuario (clave: "mesaId_userId")
  private ordersMap = new Map<string, CurrentOrder>();
  
  // Pedido actualmente seleccionado
  private currentOrder$ = new BehaviorSubject<CurrentOrder>(this.initialState);
  
  // Clave del pedido actual
  private currentKey: string | null = null;

  constructor() {
    this.loadFromStorage();
  }

  // Genera clave única para una mesa+usuario
  private getKey(table: Table, user: User): string {
    return `${table.uuid || table.id}_${user.id}`;
  }

  getCurrentOrder(): Observable<CurrentOrder> {
    return this.currentOrder$.asObservable();
  }

  getCurrentOrderValue(): CurrentOrder {
    return this.currentOrder$.value;
  }

  setTableAndUser(table: Table, user: User): void {
    const key = this.getKey(table, user);
    this.currentKey = key;
    
    // Si no existe un pedido para esta mesa+usuario, crearlo
    if (!this.ordersMap.has(key)) {
      const newOrder: CurrentOrder = {
        table,
        user,
        items: [],
        total: 0,
      };
      this.ordersMap.set(key, newOrder);
    }
    
    // Cambiar al pedido de esta mesa
    this.currentOrder$.next(this.ordersMap.get(key)!);
    this.saveToStorage();
  }

  addItem(item: OrderItem): void {
    const currentOrder = this.currentOrder$.value;
    if (!currentOrder.table || !currentOrder.user || !this.currentKey) {
      console.error('No hay mesa/usuario seleccionado');
      return;
    }

    const existingItem = currentOrder.items.find((i) => i.productId === item.productId);

    if (existingItem) {
      existingItem.quantity += item.quantity;
      existingItem.total = existingItem.quantity * existingItem.price;
    } else {
      currentOrder.items.push(item);
    }

    this.calculateTotal(currentOrder);
    this.currentOrder$.next({ ...currentOrder });
    
    // Guardar en el mapa
    this.ordersMap.set(this.currentKey, currentOrder);
    this.saveToStorage();
  }

  removeItem(productId: string): void {
    const currentOrder = this.currentOrder$.value;
    if (!this.currentKey) return;

    currentOrder.items = currentOrder.items.filter((i) => i.productId !== productId);
    this.calculateTotal(currentOrder);
    this.currentOrder$.next({ ...currentOrder });
    this.ordersMap.set(this.currentKey, currentOrder);
    this.saveToStorage();
  }

  updateItemQuantity(productId: string, quantity: number): void {
    const currentOrder = this.currentOrder$.value;
    if (!this.currentKey) return;

    const item = currentOrder.items.find((i) => i.productId === productId);

    if (item) {
      if (quantity <= 0) {
        this.removeItem(productId);
      } else {
        item.quantity = quantity;
        item.total = quantity * item.price;
        this.calculateTotal(currentOrder);
        this.currentOrder$.next({ ...currentOrder });
        this.ordersMap.set(this.currentKey, currentOrder);
        this.saveToStorage();
      }
    }
  }

  // Obtener todos los pedidos (por si los necesitas en otro lado)
  getAllOrders(): Map<string, CurrentOrder> {
    return this.ordersMap;
  }

  clearOrder(): void {
    if (this.currentKey) {
      this.ordersMap.delete(this.currentKey);
    }
    this.currentKey = null;
    this.currentOrder$.next(this.initialState);
    this.saveToStorage();
  }

  private calculateTotal(order: CurrentOrder): void {
    order.total = order.items.reduce((sum, item) => sum + item.total, 0);
  }

  private saveToStorage(): void {
    // Guardar el mapa convertido a objeto
    const ordersObject: any = {};
    this.ordersMap.forEach((value, key) => {
      ordersObject[key] = value;
    });
    localStorage.setItem('ordersMap', JSON.stringify(ordersObject));
    localStorage.setItem('currentKey', this.currentKey || '');
  }

  private loadFromStorage(): void {
    const storedMap = localStorage.getItem('ordersMap');
    const storedKey = localStorage.getItem('currentKey');
    
    if (storedMap) {
      try {
        const ordersObject = JSON.parse(storedMap);
        for (const key in ordersObject) {
          this.ordersMap.set(key, ordersObject[key]);
        }
      } catch (error) {
        console.error('Error loading orders from storage:', error);
      }
    }
    
    if (storedKey && this.ordersMap.has(storedKey)) {
      this.currentKey = storedKey;
      this.currentOrder$.next(this.ordersMap.get(storedKey)!);
    }
  }
}