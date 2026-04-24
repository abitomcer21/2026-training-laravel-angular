 import { Injectable, Injector } from '@angular/core';
import { Observable } from 'rxjs';
import { BaseApiService, ApiResponse } from './base-api.service';

export interface Restaurant {
  id: string;
  name: string;
  legal_name: string;
  tax_id: string;
  email: string;
  created_at: string;
  updated_at: string;
}

@Injectable({ providedIn: 'root' })
export class RestaurantService extends BaseApiService {
  constructor(protected override injector: Injector) {
    super(injector);
  }

  getMyRestaurant(): Observable<ApiResponse> {
    return this.httpCall('/my-restaurant', null, 'get');
  }

  createRestaurant(restaurant: any): Observable<ApiResponse> {
    return this.httpCall('/restaurants', restaurant, 'post');
  }
}
