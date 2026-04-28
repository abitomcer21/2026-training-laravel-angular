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

  private currentOrder$ = new BehaviorSubject<CurrentOrder>(this.initialState);

  constructor() {
    this.loadFromStorage();
  }

  getCurrentOrder(): Observable<CurrentOrder> {
    return this.currentOrder$.asObservable();
  }

  getCurrentOrderValue(): CurrentOrder {
    return this.currentOrder$.value;
  }

  setTableAndUser(table: Table, user: User): void {
    const order = this.currentOrder$.value;
    order.table = table;
    order.user = user;
    this.currentOrder$.next(order);
    this.saveToStorage();
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
    this.saveToStorage();
  }

  removeItem(productId: string): void {
    const order = this.currentOrder$.value;
    order.items = order.items.filter((i) => i.productId !== productId);
    this.calculateTotal();
    this.currentOrder$.next(order);
    this.saveToStorage();
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
        this.saveToStorage();
      }
    }
  }

  clearOrder(): void {
    this.currentOrder$.next(this.initialState);
    localStorage.removeItem('currentOrder');
  }

  private calculateTotal(): void {
    const order = this.currentOrder$.value;
    order.total = order.items.reduce((sum, item) => sum + item.total, 0);
  }

  private saveToStorage(): void {
    localStorage.setItem('currentOrder', JSON.stringify(this.currentOrder$.value));
  }

  private loadFromStorage(): void {
    const stored = localStorage.getItem('currentOrder');
    if (stored) {
      try {
        const order = JSON.parse(stored);
        this.currentOrder$.next(order);
      } catch (error) {
        console.error('Error loading order from storage:', error);
      }
    }
  }
}
