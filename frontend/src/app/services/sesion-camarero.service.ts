import { Injectable, signal, inject } from '@angular/core';

export interface ActiveWaiter { uuid: string; name: string; pin: string; }
export type TpvView = 'mesas' | 'productos' | 'pedidos' | 'caja';

@Injectable({ providedIn: 'root' })
export class SesiónCamareroService {
  private _camarero = signal<ActiveWaiter | null>(null);
  private _onExpire?: () => void;
  private _timer: ReturnType<typeof setTimeout> | null = null;
  private readonly TIMEOUT_MS = 15_000;

  readonly camarero = this._camarero.asReadonly();

  onSessionExpire(callback: () => void): void {
    this._onExpire = callback;
  }

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

  iniciarSesion(camarero: ActiveWaiter): void { this.establecerCamarero(camarero); }
  cerrarSesion(): void { this.limpiar(); }
  obtenerCamareroActual(): ActiveWaiter | null { return this._camarero(); }
  tieneCamareroActivo(): boolean { return this._camarero() !== null; }

  private _reiniciarTimer(): void {
    this._limpiarTimer();
    this._timer = setTimeout(() => {
      this._camarero.set(null);
      this._onExpire?.();
    }, this.TIMEOUT_MS);
  }

  private _limpiarTimer(): void {
    if (this._timer) { clearTimeout(this._timer); this._timer = null; }
  }
}