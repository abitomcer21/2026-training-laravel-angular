import { Injectable, Injector } from '@angular/core';
import { Observable } from 'rxjs';
import { BaseApiService } from './base-api.service';

@Injectable({ providedIn: 'root' })
export class SalesService extends BaseApiService {
  constructor(injector: Injector) {
    super(injector);
  }

  createSale(payload: any): Observable<any> {
    return this.httpCall('/sales', payload, 'post');
  }
}
