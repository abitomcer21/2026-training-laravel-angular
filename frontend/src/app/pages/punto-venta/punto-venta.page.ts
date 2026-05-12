import { Component, OnInit, ViewChild } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import {
  IonContent, IonIcon
} from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import {
  gridOutline, restaurantOutline, listOutline, cashOutline, logOutOutline, optionsOutline
} from 'ionicons/icons';

import { MesasComponent } from '../../features/punto-venta/componentes/mesas/mesas.component';
import { ProductosComponent } from '../../features/punto-venta/componentes/productos/productos.component';
import { PedidosComponent } from '../../features/punto-venta/componentes/pedidos/pedidos.component';
import { CajaComponent } from '../../features/punto-venta/componentes/caja/caja.component';
import { RestaurantService } from '../../services/api/restaurant.service';

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
  imports: [
    CommonModule,
    FormsModule,
    IonContent,
    IonIcon,
    MesasComponent,
    ProductosComponent,
    PedidosComponent,
    CajaComponent
  ]
})
export class PuntoVentaPage implements OnInit {
  @ViewChild(MesasComponent) mesasComponent?: MesasComponent;

  currentView: string = 'mesas';
  restaurantName: string = 'Restaurante';

  userRole: string = '';

  menuItems: MenuItem[] = [
    { nombre: 'Mesas', valor: 'mesas', icono: 'grid-outline' },
    { nombre: 'Pedidos', valor: 'pedidos', icono: 'list-outline' },
    { nombre: 'Caja', valor: 'caja', icono: 'cash-outline' }
  ];

  constructor(private restaurantService: RestaurantService, private router: Router) {
    addIcons({
      gridOutline,
      restaurantOutline,
      listOutline,
      cashOutline,
      logOutOutline,
      optionsOutline
    });
  }

  ngOnInit() {
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
      error: (error) => {
        console.error('Error al cargar nombre del restaurante:', error);
        this.restaurantName = 'Restaurante';
      }
    });
  }

  selectView(valor: string) {
    this.currentView = valor;
    
    if (valor === 'mesas' && this.mesasComponent) {
      setTimeout(() => {
        this.mesasComponent?.refrescarMesas();
      }, 0);
    }
  }

  logout() {
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
    this.router.navigate(['/dashboard']);
  }
  
}
