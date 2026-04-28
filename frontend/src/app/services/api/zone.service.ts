// src/app/services/api/zone.service.ts
import { Injectable, Injector } from '@angular/core';
import { Observable } from 'rxjs';
import { shareReplay } from 'rxjs/operators';
import { BaseApiService, ApiResponse } from './base-api.service';

export interface Zone {
  id: string;
  uuid?: string;
  database_id?: number;
  name: string;
  restaurant_id: number;
  created_at?: string;
  updated_at?: string;
}

export interface ZoneCreateRequest {
  name: string;
  restaurant_id: number;
}

export interface ZoneUpdateRequest {
  name: string;
}

@Injectable({
  providedIn: 'root'
})
export class ZoneService extends BaseApiService {
  private endpoint = '/zones';
  private zonesCache$: Observable<ApiResponse> | null = null;

  constructor(protected override injector: Injector) {
    super(injector);
  }

  getZones(): Observable<ApiResponse> {
    if (!this.zonesCache$) {
      this.zonesCache$ = this.httpCall(this.endpoint, null, 'get').pipe(
        shareReplay(1)
      );
    }
    return this.zonesCache$;
  }

  getZoneById(id: string): Observable<ApiResponse> {
    return this.httpCall(`${this.endpoint}/${id}`, null, 'get');
  }

  createZone(data: ZoneCreateRequest): Observable<ApiResponse> {
    return this.httpCall(this.endpoint, data, 'post');
  }

  updateZone(id: string, data: ZoneUpdateRequest): Observable<ApiResponse> {
    return this.httpCall(`${this.endpoint}/${id}`, data, 'put');
  }

  deleteZone(id: string): Observable<ApiResponse> {
    return this.httpCall(`${this.endpoint}/${id}`, null, 'delete');
  }

  invalidateZonesCache(): void {
    this.zonesCache$ = null;
  }
}
