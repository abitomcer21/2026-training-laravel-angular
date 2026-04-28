import { Injectable, Injector } from '@angular/core';
import { Observable } from 'rxjs';
import { shareReplay } from 'rxjs/operators';
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
  private familiesCache$: Observable<ApiResponse> | null = null;

  constructor(protected override injector: Injector) {
    super(injector);
  }

  getFamilies(): Observable<ApiResponse> {
    if (!this.familiesCache$) {
      this.familiesCache$ = this.httpCall(this.endpoint, null, 'get').pipe(
        shareReplay(1)
      );
    }
    return this.familiesCache$;
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

  invalidateFamiliesCache(): void {
    this.familiesCache$ = null;
  }
}
