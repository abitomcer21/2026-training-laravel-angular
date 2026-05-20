import { Injectable, signal } from '@angular/core';

export interface ActiveWaiter {
  uuid: string;
  name: string;
  pin: string;
}

@Injectable({ providedIn: 'root' })

export class SesiónCamareroService {
  private _camarero = signal<ActiveWaiter | null>(null);
  private _timer: ReturnType<typeof setTimeout> | null = null;
  private readonly TIMEOUT_MS = 15_000;

  readonly camarero = this._camarero.asReadonly();

  establecerCamarero(camarero: ActiveWaiter): void {
    this._camarero.set(camarero);
    this._reiniciarTimer();
  }

  renovarSesion(): void {
    if (this._camarero()) this._reiniciarTimer();
  }

  limpiar(): void {
    this._limpiarTimer();
    this._camarero.set(null);
  }

  obtenerCamareroActual(): ActiveWaiter | null {
    return this._camarero();
  }

  tieneCamareroActivo(): boolean {
    return this._camarero() !== null;
  }

  iniciarSesion(camarero: ActiveWaiter): void {
    this.establecerCamarero(camarero);
  }

  cerrarSesion(): void {
    this.limpiar();
  }

  private _reiniciarTimer(): void {
    this._limpiarTimer();
    this._timer = setTimeout(() => this._camarero.set(null), this.TIMEOUT_MS);
  }

  private _limpiarTimer(): void {
    if (this._timer) {
      clearTimeout(this._timer);
      this._timer = null;
    }
  }
}
