import { Component, OnInit, CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-caja',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule], 
  schemas: [CUSTOM_ELEMENTS_SCHEMA],  
  templateUrl: './caja.component.html',
  styleUrls: ['./caja.component.scss']
})
export class CajaComponent implements OnInit {
  
  cierreForm: FormGroup;
  turnoActual: any;
  isLoading = false;
  cierreExitoso = false;
  fechaActual: Date = new Date();
  
  // Datos de ejemplo
  resumenTurno = {
    turno: 'Mañana',
    usuario: 'María González',
    fechaApertura: new Date(2024, 0, 15, 8, 30),
    ventasTotales: 1250.75,
    cantidadOperaciones: 48,
    efectivoEsperado: 1250.75,
    efectivoRegistrado: 0,
    tarjeta: 850.50,
    transferencias: 400.25,
    diferencia: 0
  };
  
  metodosPago = [
    { nombre: 'Efectivo', monto: 0, icono: 'cash-outline' },
    { nombre: 'Tarjeta', monto: 850.50, icono: 'card-outline' },
    { nombre: 'Transferencia', monto: 400.25, icono: 'swap-horizontal-outline' }
  ];
  
  constructor(private fb: FormBuilder) {
    this.cierreForm = this.fb.group({
      efectivoRegistrado: ['', [Validators.required, Validators.min(0)]],
      observaciones: ['']
    });
  }
  
  ngOnInit(): void {
    this.resumenTurno.efectivoEsperado = this.resumenTurno.ventasTotales;
    this.actualizarDiferencia();
    
    this.cierreForm.get('efectivoRegistrado')?.valueChanges.subscribe(valor => {
      this.resumenTurno.efectivoRegistrado = valor || 0;
      this.actualizarDiferencia();
    });
  }
  
  actualizarDiferencia(): void {
    const efectivoReal = this.resumenTurno.efectivoRegistrado;
    const efectivoEsperado = this.resumenTurno.efectivoEsperado;
    this.resumenTurno.diferencia = efectivoReal - efectivoEsperado;
  }
  
  registrarCierre(): void {
    if (this.cierreForm.invalid) {
      Object.keys(this.cierreForm.controls).forEach(key => {
        this.cierreForm.get(key)?.markAsTouched();
      });
      return;
    }
    
    this.isLoading = true;
    
    setTimeout(() => {
      this.isLoading = false;
      this.cierreExitoso = true;
      
      console.log('Cierre registrado:', {
        ...this.resumenTurno,
        observaciones: this.cierreForm.value.observaciones,
        fechaCierre: new Date()
      });
      
      setTimeout(() => {
        this.cierreExitoso = false;
      }, 4000);
    }, 1500);
  }
  
  formatearFecha(fecha: Date): string {
    return fecha.toLocaleString('es-ES', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }
}