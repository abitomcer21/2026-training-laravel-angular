import { Injectable, Injector } from '@angular/core';
import { Observable } from 'rxjs';
import { BaseApiService, ApiResponse } from './base-api.service';

export interface Tax {
  id: number | string;
  uuid?: string;
  name: string;
  percentage: number;
  restaurant_id: number;
  created_at?: string;
  updated_at?: string;
}

@Injectable({ providedIn: 'root' })
export class TaxService extends BaseApiService {
  private endpoint = '/tax';

  constructor(protected override injector: Injector) {
    super(injector);
  }

  getTaxes(): Observable<ApiResponse> {
    return this.httpCall(this.endpoint, null, 'get');
  }

  getTax(id: string): Observable<ApiResponse> {
    return this.httpCall(`${this.endpoint}/${id}`, null, 'get');
  }

  createTax(tax: any): Observable<ApiResponse> {
    return this.httpCall(this.endpoint, tax, 'post');
  }

  updateTax(id: string, tax: any): Observable<ApiResponse> {
    return this.httpCall(`${this.endpoint}/${id}`, tax, 'put');
  }

  deleteTax(id: string): Observable<ApiResponse> {
    return this.httpCall(`${this.endpoint}/${id}`, null, 'delete');
  }
}
