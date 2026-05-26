import { Injectable, signal } from '@angular/core';

export interface ActiveWaiter { uuid: string; name: string; pin: string; role?: string; }
export type TpvView = 'mesas' | 'productos' | 'pedidos' | 'caja';

@Injectable({ providedIn: 'root' })
export class SesiónCamareroService {
  private _camarero = signal<ActiveWaiter | null>(null);
  private _tiempoRestante = signal<number>(0);
  private _onExpire?: () => void;
  private _timer: ReturnType<typeof setTimeout> | null = null;
  private _intervalo: ReturnType<typeof setInterval> | null = null;
  private readonly TIMEOUT_MS = 15_000;

  readonly camarero = this._camarero.asReadonly();
  readonly tiempoRestante = this._tiempoRestante.asReadonly();

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
    this._tiempoRestante.set(0);
  }

  iniciarSesion(camarero: ActiveWaiter): void { this.establecerCamarero(camarero); }
  cerrarSesion(): void { this.limpiar(); }
  obtenerCamareroActual(): ActiveWaiter | null { return this._camarero(); }
  tieneCamareroActivo(): boolean { return this._camarero() !== null; }

  private _reiniciarTimer(): void {
    this._limpiarTimer();
    this._tiempoRestante.set(this.TIMEOUT_MS / 1000);

    this._intervalo = setInterval(() => {
      const actual = this._tiempoRestante();
      if (actual > 1) {
        this._tiempoRestante.set(actual - 1);
      }
    }, 1000);

    this._timer = setTimeout(() => {
      this._limpiarTimer();
      this._tiempoRestante.set(0);
      this._camarero.set(null);
      this._onExpire?.();
    }, this.TIMEOUT_MS);
  }

  private _limpiarTimer(): void {
    if (this._timer) { clearTimeout(this._timer); this._timer = null; }
    if (this._intervalo) { clearInterval(this._intervalo); this._intervalo = null; }
  }
}