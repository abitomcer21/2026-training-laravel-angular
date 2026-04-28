import { Injectable } from '@angular/core';
import { BehaviorSubject } from 'rxjs';

@Injectable({ providedIn: 'root' })
export class DataCacheService {
  private familiesCache = new BehaviorSubject<any[]>([]);
  private productsCache = new BehaviorSubject<any[]>([]);
  private taxesCache = new BehaviorSubject<any[]>([]);

  getFamiliesCache$ = this.familiesCache.asObservable();
  getProductsCache$ = this.productsCache.asObservable();
  getTaxesCache$ = this.taxesCache.asObservable();

  setFamiliesCache(families: any[]): void {
    this.familiesCache.next(families);
  }

  getFamilies(): any[] {
    return this.familiesCache.value;
  }

  setProductsCache(products: any[]): void {
    this.productsCache.next(products);
  }

  getProducts(): any[] {
    return this.productsCache.value;
  }

  setTaxesCache(taxes: any[]): void {
    this.taxesCache.next(taxes);
  }

  getTaxes(): any[] {
    return this.taxesCache.value;
  }

  clearCache(): void {
    this.familiesCache.next([]);
    this.productsCache.next([]);
    this.taxesCache.next([]);
  }
}
