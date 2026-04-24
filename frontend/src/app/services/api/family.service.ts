import { Injectable, Injector } from '@angular/core';
import { Observable } from 'rxjs';
import { BaseApiService, ApiResponse } from './base-api.service';

export interface Family {
  id: string;
  name: string;
  active: boolean;
  restaurant_id: number;
  created_at?: string;
  updated_at?: string;
}

@Injectable({ providedIn: 'root' })
export class FamilyService extends BaseApiService {
  private endpoint = '/family';

  constructor(protected override injector: Injector) {
    super(injector);
  }

  getFamilies(): Observable<ApiResponse> {
    return this.httpCall(this.endpoint, null, 'get');
  }

  getFamily(id: string): Observable<ApiResponse> {
    return this.httpCall(`${this.endpoint}/${id}`, null, 'get');
  }

  createFamily(family: any): Observable<ApiResponse> {
    return this.httpCall(this.endpoint, family, 'post');
  }

  updateFamily(id: string, family: any): Observable<ApiResponse> {
    return this.httpCall(`${this.endpoint}/${id}`, family, 'put');
  }

  deleteFamily(id: string): Observable<ApiResponse> {
    return this.httpCall(`${this.endpoint}/${id}`, null, 'delete');
  }
}
