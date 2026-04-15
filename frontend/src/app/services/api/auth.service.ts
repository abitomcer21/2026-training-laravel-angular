import { Injectable } from '@angular/core';
import { Observable, of, throwError } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private loggedIn = false;

  isLoggedIn(): boolean {
    return this.loggedIn;
  }

  login(email: string, password: string): Observable<void> {
    if (email && password) {
      this.loggedIn = true;
      return of(void 0);
    } else {
      return throwError(() => ({ status: 401 }));
    }
  }

  logout(): void {
    this.loggedIn = false;
  }
}