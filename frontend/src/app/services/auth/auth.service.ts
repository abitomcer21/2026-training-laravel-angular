import { Injectable, Injector } from '@angular/core';
import { Observable } from 'rxjs';
import { BaseApiService, ApiResponse } from '../api/base-api.service';

@Injectable({ providedIn: 'root' })
export class AuthService extends BaseApiService {
  constructor(protected override injector: Injector) {
    super(injector);
  }

  login(email: string, password: string): Observable<ApiResponse> {
    return this.httpCall('/login', { email, password }, 'post');
  }

  logout(): Observable<ApiResponse> {
    return this.httpCall('/logout', null, 'post');
  }

  saveToken(token: string): void {
    localStorage.setItem('token', token);
  }

  getToken(): string | null {
    return localStorage.getItem('token');
  }

  getUserData(): any {
    const userData = localStorage.getItem('userData');
    return userData ? JSON.parse(userData) : null;
  }

  isLoggedIn(): boolean {
    return !!this.getToken();
  }
}
