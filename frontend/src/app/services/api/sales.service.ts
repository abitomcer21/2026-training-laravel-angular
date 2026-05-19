import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';

export interface Sale {
  id: string;
  ticket_number: number;
  total: number;
  payment_method: string;
  user_id: number;
  user_name: string;
  created_at: string;
}

@Injectable({
  providedIn: 'root'
})
export class SalesService {
  private apiUrl: string;

  constructor(private http: HttpClient) {
    this.apiUrl = environment.apiUrl || 'http://localhost:3000/api';
  }

  getTodaySales(): Observable<{ data: Sale[] }> {
    return this.http.get<{ data: Sale[] }>(`${this.apiUrl}/sales/today`);
  }

  createSale(saleData: { order_id: string; user_id: number }): Observable<any> {
    return this.http.post(`${this.apiUrl}/sales`, saleData);
  }
}