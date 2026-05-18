import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';

@Injectable({
    providedIn: 'root',
})
export class SalesUpdateService {
    private ventaCreadaSource = new Subject<void>();
    ventaCreada$ = this.ventaCreadaSource.asObservable();

    notificarVentaCreada(): void {
        this.ventaCreadaSource.next();
    }
}
