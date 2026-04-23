// src/app/services/api/table.service.ts
import { Injectable, Injector } from '@angular/core';
import { Observable } from 'rxjs';
import { BaseApiService, ApiResponse } from './base-api.service';

export interface Table {
  id: string;
  uuid: string;
  name: string;
  zone_id: number | string;
  restaurant_id: number;
  created_at?: string;
  updated_at?: string;
}

export interface TableCreateRequest {
  name: string;
  zone_id: number | string;
  restaurant_id: number;
}

export interface TableUpdateRequest {
  name: string;
}

@Injectable({
  providedIn: 'root'
})
export class TableService extends BaseApiService {
  private endpoint = '/tables';

  constructor(protected override injector: Injector) {
    super(injector);
  }

  getTables(): Observable<ApiResponse> {
    return this.httpCall(this.endpoint, null, 'get');
  }

  getTableById(id: string): Observable<ApiResponse> {
    return this.httpCall(`${this.endpoint}/${id}`, null, 'get');
  }

  createTable(data: TableCreateRequest): Observable<ApiResponse> {
    return this.httpCall(this.endpoint, data, 'post');
  }

  updateTable(id: string, data: TableUpdateRequest): Observable<ApiResponse> {
    return this.httpCall(`${this.endpoint}/${id}`, data, 'put');
  }

  deleteTable(id: string): Observable<ApiResponse> {
    return this.httpCall(`${this.endpoint}/${id}`, null, 'delete');
  }

  getTablesByZone(zoneId: number | string): Observable<ApiResponse> {
    return this.httpCall(`${this.endpoint}?zone_id=${zoneId}`, null, 'get');
  }
}
