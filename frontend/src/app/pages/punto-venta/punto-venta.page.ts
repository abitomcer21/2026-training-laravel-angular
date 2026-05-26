import { Component, OnInit, ViewChild, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { IonContent, IonIcon } from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import { gridOutline, restaurantOutline, listOutline, cashOutline, logOutOutline, optionsOutline } from 'ionicons/icons';
import { MesasComponent } from '../../features/punto-venta/componentes/mesas/panel-mesas.component';
import { ProductosComponent } from '../../features/punto-venta/componentes/productos/panel-productos.component';
import { PedidosComponent } from '../../features/punto-venta/componentes/pedidos/panel-pedidos.component';
import { CajaComponent } from '../../features/punto-venta/componentes/caja/panel-caja.component';
import { RestaurantService } from '../../services/api/restaurant.service';
import { SesiónCamareroService } from '../../services/sesion-camarero.service';
import { UserService } from '../../services/api/user.service';

interface MenuItem {
  nombre: string;
  valor: string;
  icono: string;
}

@Component({
  selector: 'app-punto-venta',
  templateUrl: './punto-venta.page.html',
  styleUrls: ['./punto-venta.page.scss'],
  standalone: true,
  imports: [CommonModule, FormsModule, IonContent, IonIcon, MesasComponent, ProductosComponent, PedidosComponent, CajaComponent],
})
export class PuntoVentaPage implements OnInit {
  @ViewChild(MesasComponent) mesasComponent?: MesasComponent;

  currentView: string = 'mesas';
  restaurantName: string = 'Restaurante';
  userRole: string = '';

  pendingView: string | null = null;

  readonly activeWaiter = this.sesionCamarero.camarero;

  showPinGuard = false;
  adminUsers: any[] = [];
  pinGuardDigits = '';
  pinGuardError = '';

  menuItems: MenuItem[] = [
    { nombre: 'Mesas', valor: 'mesas', icono: 'grid-outline' },
    { nombre: 'Pedidos', valor: 'pedidos', icono: 'list-outline' },
    { nombre: 'Caja', valor: 'caja', icono: 'cash-outline' },
  ];

  

  constructor(
    private restaurantService: RestaurantService,
    private router: Router,
    private sesionCamarero: SesiónCamareroService,
    private userService: UserService,
  ) {
    addIcons({ gridOutline, restaurantOutline, listOutline, cashOutline, logOutOutline, optionsOutline });
  }

  ngOnInit() {
    this.sesionCamarero.onSessionExpire(() => this.selectView('mesas'));
    this.cargarRestaurantName();
    const userData = localStorage.getItem('userData');
    if (userData) {
      try {
        const user = JSON.parse(userData);
        this.userRole = user.role || '';
      } catch (e) {
        this.userRole = '';
      }
    }
  }

  cargarRestaurantName() {
    this.restaurantService.getMyRestaurant().subscribe({
      next: (response: any) => {
        this.restaurantName = response?.name || 'Restaurante';
      },
      error: () => {
        this.restaurantName = 'Restaurante';
      },
    });
  }

selectView(valor: string) {
  const restricted = ['pedidos', 'caja'];
  if (restricted.includes(valor) && this.needsPinGuard()) {
    this.abrirPinGuard(valor);
    return;
  }
  this._doSelectView(valor);
}

  openTableFromOrder(tableId: string) {
    this.currentView = 'productos';
  }

  clearWaiter() {
    this.sesionCamarero.limpiar();
      this.currentView = 'mesas';
  }

  logout() {
    this.sesionCamarero.limpiar();
    localStorage.removeItem('isLoggedIn');
    localStorage.removeItem('token');
    localStorage.removeItem('userData');
    window.location.href = '/login';
  }

  closeSession() {
    if (confirm('¿Estás seguro de que deseas cerrar esta sesión?')) {
      this.logout();
    }
  }

volverAlDashboard() {
  if (this.needsPinGuard()) {
    this.abrirPinGuard(null);
    return;
  }
  this.router.navigate(['/dashboard']);
}

  addPinGuardDigit(d: string) {
    if (this.pinGuardDigits.length >= 4) return;
    this.pinGuardDigits += d;
    this.pinGuardError = '';
    if (this.pinGuardDigits.length === 4) setTimeout(() => this.confirmPinGuard(), 150);
  }

  removePinGuardDigit() {
    this.pinGuardDigits = this.pinGuardDigits.slice(0, -1);
    this.pinGuardError = '';
  }

  closePinGuard() {
    this.showPinGuard = false;
    this.pinGuardDigits = '';
    this.pinGuardError = '';
  }

  getPinGuardKeyboardRow(row: number): number[] {
    return [[1,2,3],[4,5,6],[7,8,9]][row] || [];
  }

  confirmPinGuard() {
    const match = this.adminUsers.find(u => u.pin === this.pinGuardDigits);
    if (!match) {
      this.pinGuardError = 'PIN inválido';
      this.pinGuardDigits = '';
      return;
    }
    this.showPinGuard = false;
    if (this.pendingView) {
      this._doSelectView(this.pendingView);
      this.pendingView = null;
    } else {
      this.router.navigate(['/dashboard']);
    }
  }

  private _doSelectView(valor: string) {
    this.currentView = valor;
    if (valor === 'mesas' && this.mesasComponent) {
      setTimeout(() => this.mesasComponent?.refrescarMesas(), 0);
    }
  }

  private needsPinGuard(): boolean {
  const waiter = this.sesionCamarero.obtenerCamareroActual();
  console.log('waiter:', waiter);
  return !waiter || !['admin', 'supervisor'].includes(waiter.role ?? '');
}

private abrirPinGuard(destino: string | null) {
  this.pinGuardDigits = '';
  this.pinGuardError = '';
  this.pendingView = destino;
  this.userService.getUsers().subscribe({
    next: (res: any) => {
      this.adminUsers = (res.users ?? []).filter((u: any) =>
        ['admin', 'supervisor'].includes(u.role)
      );
      this.showPinGuard = true;
    },
  });
}

readonly tiempoRestante = this.sesionCamarero.tiempoRestante;

}