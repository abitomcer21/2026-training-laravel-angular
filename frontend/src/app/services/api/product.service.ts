import { Injectable, Injector } from '@angular/core';
import { Observable } from 'rxjs';
import { shareReplay, tap } from 'rxjs/operators';
import { BaseApiService, ApiResponse } from './base-api.service';

export interface Product {
  id: number;
  uuid: string;
  family_id: string;
  tax_id: number;
  name: string;
  price: number;
  stock: number;
  image_src: string;
  active: boolean;
  restaurant_id: number;
  created_at?: string;
  updated_at?: string;
}

@Injectable({ providedIn: 'root' })
export class ProductService extends BaseApiService {
  private endpoint = '/products';
  private productsCache$: Observable<ApiResponse> | null = null;

  constructor(protected override injector: Injector) {
    super(injector);
  }

  getProducts(): Observable<ApiResponse> {
    if (!this.productsCache$) {
      this.productsCache$ = this.httpCall(this.endpoint, null, 'get').pipe(
        shareReplay(1)
      );
    }
    return this.productsCache$;
  }

  invalidateProductsCache(): void {
    this.productsCache$ = null;
  }

  getProduct(uuid: string): Observable<ApiResponse> {
    return this.httpCall(`${this.endpoint}/${uuid}`, null, 'get');
  }

  getProductsByFamily(familyId: number): Observable<ApiResponse> {
    return this.httpCall(`${this.endpoint}/family/${familyId}`, null, 'get');
  }

  getProductsByName(name: string): Observable<ApiResponse> {
    return this.httpCall(`${this.endpoint}/name/${name}`, null, 'get');
  }

  createProduct(product: any): Observable<ApiResponse> {
    return this.httpCall(this.endpoint, product, 'post');
  }

  updateProduct(uuid: string, product: any): Observable<ApiResponse> {
    return this.httpCall(`${this.endpoint}/${uuid}`, product, 'put');
  }

  deleteProduct(uuid: string): Observable<ApiResponse> {
    return this.httpCall(`${this.endpoint}/${uuid}`, null, 'delete');
  }
}
  