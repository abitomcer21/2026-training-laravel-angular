import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import {
  IonHeader, IonToolbar, IonTitle, IonContent, IonButtons,
  IonButton, IonIcon, IonLabel
} from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import {
  logOutOutline, restaurantOutline, peopleOutline,
  mapOutline, cashOutline, gridOutline, albumsOutline
} from 'ionicons/icons';

import { RestaurantService } from '../../services/api/restaurant.service';
import { AuthService } from '../../services/auth/auth.service';
import { UsuariosComponent } from '../../components/usuarios/usuarios.component';
import { FamiliasComponent } from '../../components/familias/familias.component';
import { ProductosComponent } from '../../components/productos/productos.component';
import { ImpuestosComponent } from '../../components/impuestos/impuestos.component';
import { ZonasComponent } from '../../components/zonas/zonas.component';
import { MesasComponent } from '../../components/mesas/mesas.component';

interface MenuItem {
  nombre: string;
  valor: string;
  icono: string;
}

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.page.html',
  styleUrls: ['./dashboard.page.scss'],
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    IonHeader, IonToolbar, IonTitle, IonContent, IonButtons,
    IonButton, IonIcon, IonLabel,
    UsuariosComponent, FamiliasComponent, ProductosComponent,
    ImpuestosComponent, ZonasComponent, MesasComponent
  ]
})
export class DashboardPage implements OnInit {
  opcionSeleccionada: string = 'usuarios';
  restaurantName: string = 'Yurest TPV';

  menuItems: MenuItem[] = [
    { nombre: 'Usuarios', valor: 'usuarios', icono: 'people-outline' },
    { nombre: 'Impuestos', valor: 'impuestos', icono: 'cash-outline' },
    { nombre: 'Familias', valor: 'familias', icono: 'albums-outline' },
    { nombre: 'Productos', valor: 'productos', icono: 'restaurant-outline' },
    { nombre: 'Zonas', valor: 'zonas', icono: 'map-outline' },
    { nombre: 'Mesas', valor: 'mesas', icono: 'grid-outline' }
  ];

  constructor(
    private restaurantService: RestaurantService,
    private authService: AuthService
  ) {
    addIcons({
      logOutOutline, restaurantOutline, peopleOutline,
      mapOutline, cashOutline, gridOutline, albumsOutline
    });
  }


  ngOnInit() {
    // Cargar nombre del restaurante
    this.cargarRestaurantName();
  }

  cargarRestaurantName() {
    this.restaurantService.getMyRestaurant().subscribe({
      next: (response: any) => {
        this.restaurantName = response?.name || 'Yurest TPV';
      },
      error: (error) => {
        console.error('Error al cargar nombre del restaurante:', error);
        this.restaurantName = 'Yurest TPV';
      }
    });
  }

  seleccionarOpcion(valor: string) {
    this.opcionSeleccionada = valor;
  }


  cerrarSesion() {
    localStorage.removeItem('isLoggedIn');
    localStorage.removeItem('token');
    localStorage.removeItem('userData');
    window.location.href = '/login';
  }
}