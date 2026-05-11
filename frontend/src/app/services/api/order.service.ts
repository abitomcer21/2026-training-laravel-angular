import { Injectable, Injector } from '@angular/core';
import { Observable } from 'rxjs';
import { BaseApiService } from './base-api.service';

@Injectable({ providedIn: 'root' })
export class OrderService extends BaseApiService {
  constructor(injector: Injector) {
    super(injector);
  }

  createOrder(payload: any): Observable<any> {
    return this.httpCall('/orders', payload, 'post');
  }

  addOrderLines(orderId: string, payload: any): Observable<any> {
    return this.httpCall(`/orders/${orderId}/lines`, payload, 'post');
  }
}