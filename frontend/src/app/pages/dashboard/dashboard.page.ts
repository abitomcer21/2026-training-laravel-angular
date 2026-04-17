import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { 
  IonHeader, IonToolbar, IonTitle, IonContent, IonButtons,
  IonButton, IonIcon, IonLabel
} from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import { 
  logOutOutline, restaurantOutline, gridOutline, 
  peopleOutline, mapOutline, pricetagsOutline, cashOutline 
} from 'ionicons/icons';

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
    IonButton, IonIcon, IonLabel
  ]
})
export class DashboardPage {
  opcionSeleccionada: string = 'productos';
  
  menuItems: MenuItem[] = [
    { nombre: 'Productos', valor: 'productos', icono: 'restaurant-outline' },
    { nombre: 'Familias', valor: 'familias', icono: 'grid-outline' },
    { nombre: 'Usuarios', valor: 'usuarios', icono: 'people-outline' },
    { nombre: 'Zonas', valor: 'zonas', icono: 'map-outline' },
    { nombre: 'Mesas', valor: 'mesas', icono: 'pricetags-outline' },
    { nombre: 'Impuestos', valor: 'impuestos', icono: 'cash-outline' }
  ];

  constructor() {
    addIcons({ 
      logOutOutline, restaurantOutline, gridOutline, 
      peopleOutline, mapOutline, pricetagsOutline, cashOutline 
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