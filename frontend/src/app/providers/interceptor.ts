import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { HttpEvent, HttpHandler, HttpInterceptor, HttpRequest } from '@angular/common/http';

@Injectable()
export class InterceptorProvider implements HttpInterceptor {

  /**
   * Intercepta las peticiones HTTP y les añade las cabeceras por defecto
   * 
   */
  intercept(request: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    return next.handle(this.setHeader(request));
  }


  /**
   * Clona la petición añadiendo las cabeceras
   * 
   */
  private setHeader(request: HttpRequest<any>): HttpRequest<any> {
    // Obtener el token del localStorage
    const token = localStorage.getItem('token');
    
    // Headers base
    let headers: any = {
      Accept: 'application/json',
      'Accept-Language': 'es',
    };
    
    // Añadir token si existe
    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }
    
    return request.clone({
      setHeaders: headers
    });
  }

}