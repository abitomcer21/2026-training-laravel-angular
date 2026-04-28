import { Injectable, Injector } from '@angular/core';
import { Observable } from 'rxjs';
import { shareReplay } from 'rxjs/operators';
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
  private usersCache$: Observable<ApiResponse> | null = null;

  constructor(protected override injector: Injector) {
    super(injector);
  }

  getUsers(): Observable<ApiResponse> {
    if (!this.usersCache$) {
      this.usersCache$ = this.httpCall(this.endpoint, null, 'get').pipe(
        shareReplay(1)
      );
    }
    return this.usersCache$;
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

  validatePin(userUuid: string, pin: string): Observable<ApiResponse> {
    return this.httpCall(
      `${this.endpoint}/${userUuid}/validate-pin`,
      { pin },
      'post'
    );
  }

  invalidateUsersCache(): void {
    this.usersCache$ = null;
  }
}