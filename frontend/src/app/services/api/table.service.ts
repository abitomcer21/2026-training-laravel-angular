// src/app/services/api/table.service.ts
import { Injectable, Injector } from '@angular/core';
import { Observable } from 'rxjs';
import { shareReplay } from 'rxjs/operators';
import { BaseApiService, ApiResponse } from './base-api.service';

export interface Table {
  id: string;
  uuid: string;
  name: string;
  zone_id: number | string;
  restaurant_id: number;
  status?: 'available' | 'occupied';
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
  status?: 'available' | 'occupied';
}

@Injectable({
  providedIn: 'root'
})
export class TableService extends BaseApiService {
  private endpoint = '/tables';
  private tablesCache$: Observable<ApiResponse> | null = null;

  constructor(protected override injector: Injector) {
    super(injector);
  }

  getTables(): Observable<ApiResponse> {
    if (!this.tablesCache$) {
      this.tablesCache$ = this.httpCall(this.endpoint, null, 'get').pipe(
        shareReplay(1)
      );
    }
    return this.tablesCache$;
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

  invalidateTablesCache(): void {
    this.tablesCache$ = null;
  }
}
