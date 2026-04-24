import { Injectable, Injector } from '@angular/core';
import { Observable } from 'rxjs';
import { BaseApiService, ApiResponse } from './base-api.service';

export interface Product {
  id: string | number;
  uuid?: string;
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

  constructor(protected override injector: Injector) {
    super(injector);
  }

  getProducts(): Observable<ApiResponse> {
    return this.httpCall(this.endpoint, null, 'get');
  }

  getProduct(id: string): Observable<ApiResponse> {
    return this.httpCall(`${this.endpoint}/${id}`, null, 'get');
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

  updateProduct(id: string, product: any): Observable<ApiResponse> {
    return this.httpCall(`${this.endpoint}/${id}`, product, 'put');
  }

  deleteProduct(id: string): Observable<ApiResponse> {
    return this.httpCall(`${this.endpoint}/${id}`, null, 'delete');
  }
}
