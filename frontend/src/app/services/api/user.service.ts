// src/app/services/api/user.service.ts
import { Injectable, Injector } from '@angular/core';
import { Observable } from 'rxjs';
import { BaseApiService, ApiResponse } from './base-api.service';

export interface User {
  id: number;
  uuid: string;
  name: string;
  email: string;
  role: string;
  pin: string;
  image_src?: string | null;
  restaurant_id: number;
}

@Injectable({ providedIn: 'root' })
export class UserService extends BaseApiService {
  private endpoint = '/users';

  constructor(protected override injector: Injector) {
    super(injector);
  }

  getUsers(): Observable<ApiResponse> {
    return this.httpCall(this.endpoint, null, 'get');
  }

  getUser(uuid: string): Observable<ApiResponse> {
    return this.httpCall(`${this.endpoint}/${uuid}`, null, 'get');
  }

  createUser(user: any): Observable<ApiResponse> {
    return this.httpCall(this.endpoint, user, 'post');
  }

  updateUser(uuid: string, user: any): Observable<ApiResponse> {
    return this.httpCall(`${this.endpoint}/${uuid}`, user, 'put');
  }

  deleteUser(uuid: string): Observable<ApiResponse> {
    return this.httpCall(`${this.endpoint}/${uuid}`, null, 'delete');
  }
}