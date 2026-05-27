import { Component, OnInit, CUSTOM_ELEMENTS_SCHEMA } from '@angular/core';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { UserService, User } from '../../../../services/api/user.service';
import { OrderService } from '../../../../services/api/order.service';
import { SalesService, Sale } from '../../../../services/api/sales.service';
import { AuthService } from '../../../../services/auth/auth.service';
import { ToastController } from '@ionic/angular/standalone';
import { SalesUpdateService } from '../../../../services/shared/sales-update.service';

import { addIcons } from 'ionicons';
import { 
  cashOutline, 
  cardOutline, 
  personCircleOutline, 
  receiptOutline, 
  statsChartOutline, 
  clipboardOutline,
  calendarOutline,
  documentTextOutline,
  checkmarkCircleOutline,
  alertCircleOutline,
  refreshOutline,
  warningOutline,
  personOutline
} from 'ionicons/icons';

@Component({
  selector: 'app-caja',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule], 
  schemas: [CUSTOM_ELEMENTS_SCHEMA],  
  templateUrl: './panel-caja.component.html',
  styleUrls: ['./panel-caja.component.scss']
})
export class CajaComponent implements OnInit {
  
  cierreForm: FormGroup;
  supervisorUser: User | null = null;
  isLoading = false;
  cierreExitoso = false;
  fechaActual: Date = new Date();
  
  resumenTurno = {
    turno: 'Mañana',
    usuario: 'Cargando...',
    fechaApertura: new Date(),
    ventasTotales: 0,
    cantidadOperaciones: 0,
    efectivoEsperado: 0,
    efectivoRegistrado: 0,
    tarjeta: 0,
    diferencia: 0
  };
  
  metodosPago = [
    { nombre: 'Efectivo', total: 0, icono: 'cash-outline' },
    { nombre: 'Tarjeta', total: 0, icono: 'card-outline' }
  ];

  ventasRegistro: Array<{ 
    ticketNumber: number;
    vendedor: string;
    vendedorId?: number;
    total: number;
    paymentMethod: string;
    paymentMethodIcon: string;
    paymentMethodName: string;
  }> = [];
  
  usuariosMap: Map<number, string> = new Map();
  
  constructor(
    private fb: FormBuilder,
    private userService: UserService,
    private orderService: OrderService,
    private salesService: SalesService,
    private authService: AuthService,
    private toastController: ToastController,
    private salesUpdateService: SalesUpdateService
  ) {
    addIcons({
      'cash-outline': cashOutline,
      'card-outline': cardOutline,
      'person-circle-outline': personCircleOutline,
      'receipt-outline': receiptOutline,
      'stats-chart-outline': statsChartOutline,
      'clipboard-outline': clipboardOutline,
      'calendar-outline': calendarOutline,
      'document-text-outline': documentTextOutline,
      'checkmark-circle-outline': checkmarkCircleOutline,
      'alert-circle-outline': alertCircleOutline,
      'refresh-outline': refreshOutline,
      'warning-outline': warningOutline,
      'person-outline': personOutline
    });
    
    this.cierreForm = this.fb.group({
      efectivoRegistrado: ['', [Validators.required, Validators.min(0)]],
      observaciones: ['']
    });
  }
  
  ngOnInit(): void {
    this.cargarSupervisor();
    this.cargarTodosLosUsuarios();
    this.cargarVentasDelDia();
    
    this.salesUpdateService.ventaCreada$.subscribe(() => {
      console.log('Nueva venta detectada, refrescando listado...');
      this.refrescarVentas();
    });
    
    this.cierreForm.get('efectivoRegistrado')?.valueChanges.subscribe(valor => {
      this.resumenTurno.efectivoRegistrado = valor || 0;
      this.actualizarDiferencia();
    });
  }

  async mostrarToast(mensaje: string, color: string = 'success', duracion: number = 3000) {
    const toast = await this.toastController.create({
      message: mensaje,
      duration: duracion,
      position: 'top',
      color: color,
      buttons: [{ text: 'Cerrar', role: 'cancel' }]
    });
    await toast.present();
  }

  refrescarVentas(): void {
    this.cargarVentasDelDia();
  }

  cargarSupervisor(): void {
    this.userService.getUsers().subscribe({
      next: (response: any) => {
        const usuarios = response.data || response.users || response;
        
        const supervisor = Array.isArray(usuarios) 
          ? usuarios.find((user: any) => user.role === 'supervisor')
          : null;

        if (supervisor) {
          this.supervisorUser = supervisor;
          this.resumenTurno.usuario = supervisor.name;
        }
      },
      error: (error) => {
        console.error('Error al cargar supervisor:', error);
        this.resumenTurno.usuario = 'Supervisor no disponible';
      }
    });
  }

  cargarTodosLosUsuarios(): void {
    this.userService.getUsers().subscribe({
      next: (response: any) => {
        const usuarios = response.data || response.users || response;
        
        if (Array.isArray(usuarios)) {
          usuarios.forEach((user: any) => {
            this.usuariosMap.set(user.id, user.name);
          });
          console.log('Usuarios cargados:', this.usuariosMap.size);
        }
      },
      error: (error) => {
        console.error('Error al cargar usuarios:', error);
      }
    });
  }

  getNombreUsuario(userId: number): string {
    return this.usuariosMap.get(userId) || `Usuario ${userId}`;
  }

  cargarVentasDelDia(): void {
    this.isLoading = true;
    
    this.salesService.getTodaySales().subscribe({
      next: (response: { data: Sale[] }) => {
        this.isLoading = false;
        
        const sales = response.data;
        
        if (Array.isArray(sales) && sales.length > 0) {
          const ventas = sales.map((sale: Sale) => ({
            ticketNumber: sale.ticket_number,
            vendedor: sale.user_name,
            vendedorId: sale.user_id,
            total: sale.total,
            paymentMethod: sale.payment_method || 'efectivo',
            paymentMethodIcon: this.getPaymentMethodIcon(sale.payment_method || 'efectivo'),
            paymentMethodName: this.getPaymentMethodName(sale.payment_method || 'efectivo')
          }));
          
          this.procesarVentas(ventas);
        } else {
          this.procesarVentas([]);
          console.log('No hay ventas registradas hoy');
        }
      },
      error: (error: any) => {
        console.error('Error al cargar ventas:', error);
        this.isLoading = false;
        this.procesarVentas([]);
        this.mostrarToast('Error al cargar las ventas del día', 'danger');
      }
    });
  }

  procesarVentas(ventas: Array<any>): void {
    this.ventasRegistro = ventas.map(venta => ({
      ticketNumber: venta.ticketNumber,
      vendedor: venta.vendedor,
      vendedorId: venta.vendedorId,
      total: venta.total,
      paymentMethod: venta.paymentMethod,
      paymentMethodIcon: this.getPaymentMethodIcon(venta.paymentMethod),
      paymentMethodName: this.getPaymentMethodName(venta.paymentMethod)
    }));

    const totales = this.calcularTotalesPorMetodoPago(ventas);
    
    this.resumenTurno.ventasTotales = totales.totalGeneral;
    this.resumenTurno.cantidadOperaciones = ventas.length;
    this.resumenTurno.efectivoEsperado = totales.totalEfectivo;
    this.resumenTurno.tarjeta = totales.totalTarjeta;
    
    this.metodosPago[0].total = totales.totalEfectivo;
    this.metodosPago[1].total = totales.totalTarjeta;

    this.actualizarDiferencia();
  }

  calcularTotalesPorMetodoPago(ventas: Array<{ paymentMethod: string; total: number }>) {
    let totalEfectivo = 0;
    let totalTarjeta = 0;
    
    ventas.forEach(venta => {
      switch (venta.paymentMethod.toLowerCase()) {
        case 'efectivo':
        case 'cash':
          totalEfectivo += venta.total;
          break;
        case 'tarjeta':
        case 'card':
          totalTarjeta += venta.total;
          break;
      }
    });
    
    return {
      totalEfectivo,
      totalTarjeta,
      totalGeneral: totalEfectivo + totalTarjeta
    };
  }

  getPaymentMethodName(method: string): string {
    const methods: { [key: string]: string } = {
      'efectivo': 'Efectivo',
      'cash': 'Efectivo',
      'tarjeta': 'Tarjeta',
      'card': 'Tarjeta'
    };
    return methods[method.toLowerCase()] || method;
  }

  getPaymentMethodIcon(method: string): string {
    const icons: { [key: string]: string } = {
      'efectivo': 'cash-outline',
      'cash': 'cash-outline',
      'tarjeta': 'card-outline',
      'card': 'card-outline'
    };
    return icons[method.toLowerCase()] || 'help-outline';
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
    
    const cierreData = {
      fecha: this.fechaActual,
      turno: this.resumenTurno.turno,
      supervisor_id: this.supervisorUser?.id,
      ventas_totales: this.resumenTurno.ventasTotales,
      cantidad_operaciones: this.resumenTurno.cantidadOperaciones,
      efectivo_esperado: this.resumenTurno.efectivoEsperado,
      efectivo_registrado: this.resumenTurno.efectivoRegistrado,
      tarjeta: this.resumenTurno.tarjeta,
      diferencia: this.resumenTurno.diferencia,
      observaciones: this.cierreForm.value.observaciones,
      ventas_detalle: this.ventasRegistro
    };
    
    setTimeout(() => {
      this.isLoading = false;
      this.cierreExitoso = true;
      console.log('Cierre registrado:', cierreData);
      
      this.cierreForm.reset();
      this.cierreForm.get('efectivoRegistrado')?.setValue('');
      
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