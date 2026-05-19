import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';

export interface Order {
  id: number;
  ticketNumber: number;
  total: number;
  paymentMethod: string;
  userId: number;
  userName?: string;
  status: string;
  createdAt: Date;
  updatedAt: Date;
}

export interface OrderResponse {
  data: Order[];
  total: number;
  message?: string;
}

@Injectable({
  providedIn: 'root',
})
export class OrderService {
  private apiUrl: string;

  constructor(private http: HttpClient) {
    this.apiUrl = environment.apiUrl || 'http://localhost:3000/api';
  }

  getOrders(): Observable<OrderResponse> {
    return this.http.get<OrderResponse>(`${this.apiUrl}/orders`);
  }

  getOrderById(id: number): Observable<Order> {
    return this.http.get<Order>(`${this.apiUrl}/orders/${id}`);
  }

  getOrdersByDate(date: Date): Observable<OrderResponse> {
    const formattedDate = this.formatDate(date);
    return this.http.get<OrderResponse>(
      `${this.apiUrl}/orders?date=${formattedDate}`,
    );
  }

  getOrdersByDateRange(
    startDate: Date,
    endDate: Date,
  ): Observable<OrderResponse> {
    const params = new HttpParams()
      .set('startDate', this.formatDate(startDate))
      .set('endDate', this.formatDate(endDate));

    return this.http.get<OrderResponse>(`${this.apiUrl}/orders/range`, {
      params,
    });
  }

  getTodayOrders(): Observable<OrderResponse> {
    const today = new Date();
    return this.getOrdersByDate(today);
  }

  getOrdersByUser(userId: number): Observable<OrderResponse> {
    return this.http.get<OrderResponse>(
      `${this.apiUrl}/orders?userId=${userId}`,
    );
  }

  getOrdersByPaymentMethod(paymentMethod: string): Observable<OrderResponse> {
    return this.http.get<OrderResponse>(
      `${this.apiUrl}/orders?paymentMethod=${paymentMethod}`,
    );
  }

  getTodaySalesSummary(): Observable<any> {
    const today = new Date();
    return this.http.get(
      `${this.apiUrl}/orders/summary?date=${this.formatDate(today)}`,
    );
  }

  createOrder(orderData: Partial<Order>): Observable<Order> {
    return this.http.post<Order>(`${this.apiUrl}/orders`, orderData);
  }

  updateOrder(id: number, orderData: Partial<Order>): Observable<Order> {
    return this.http.put<Order>(`${this.apiUrl}/orders/${id}`, orderData);
  }

  deleteOrder(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/orders/${id}`);
  }

  getOrdersWithUsers(): Observable<any> {
    return this.http.get(`${this.apiUrl}/orders?include=user`);
  }

  getTodayOrdersWithUsers(): Observable<any> {
    const today = this.formatDate(new Date());
    return this.http.get(`${this.apiUrl}/orders?date=${today}&include=user`);
  }

  private formatDate(date: Date): string {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  registrarCierre(cierreData: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/cierre-caja`, cierreData);
  }

  addOrderLines(orderId: string, lines: any[]): Observable<any> {
    return this.http.post(`${this.apiUrl}/orders/${orderId}/lines`, {
      order_lines: lines,
    });
  }

  etOrdersByDateRange(startDate: Date, endDate: Date): Observable<any> {
  const params = {
    startDate: startDate.toISOString(),
    endDate: endDate.toISOString()
  };
  return this.http.get(`${this.apiUrl}/orders/range`, { params });
}

}
