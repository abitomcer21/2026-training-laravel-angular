import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import {
  IonIcon,
  IonLoading,
  IonModal,
  IonHeader,
  IonToolbar,
  IonTitle,
  IonContent,
  IonButton,
  IonInput,
  IonLabel,
  IonItem,
} from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import { gridOutline, closeOutline } from 'ionicons/icons';
import { TableService, Table } from '../../../../services/api/table.service';
import { UserService, User } from '../../../../services/api/user.service';
import { OrderStateService } from '../../../../services/order-state.service';
import { AuthService } from '../../../../services/auth/auth.service';

@Component({
  selector: 'app-mesas',
  templateUrl: './mesas.component.html',
  styleUrls: ['./mesas.component.scss'],
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    IonIcon,
    IonLoading,
    IonModal,
    IonHeader,
    IonToolbar,
    IonTitle,
    IonContent,
    IonButton,
    IonInput,
    IonLabel,
    IonItem,
  ],
})
export class MesasComponent implements OnInit {
  mesas: Table[] = [];
  usuarios: User[] = [];
  cargando = false;
  mostrarModalPin = false;
  selectedTable: Table | null = null;
  selectedUser: User | null = null;
  pinIngresado = '';
  mensajeError = '';

  constructor(
    private tableService: TableService,
    private userService: UserService,
    private orderStateService: OrderStateService,
    private authService: AuthService
  ) {
    addIcons({ gridOutline, closeOutline });
  }

  ngOnInit() {
    this.cargarMesas();
    this.cargarUsuarios();
  }

  cargarMesas() {
    this.cargando = true;
    const userData = this.authService.getUserData();
    const restaurantId = userData?.restaurant_id;

    if (!restaurantId) {
      console.error('No se encontró restaurant_id del usuario');
      this.cargando = false;
      return;
    }

    this.tableService.getTables().subscribe({
      next: (response: any) => {
        // Filtrar las mesas por el restaurant_id del usuario loggeado
        const todasLasMesas = response.tables || [];
        this.mesas = todasLasMesas.filter((mesa: Table) => mesa.restaurant_id === restaurantId);
        this.cargando = false;
      },
      error: (error) => {
        console.error('Error al cargar mesas:', error);
        this.cargando = false;
      },
    });
  }

  cargarUsuarios() {
    this.userService.getUsers().subscribe({
      next: (response: any) => {
        this.usuarios = response.users || [];
      },
      error: (error) => {
        console.error('Error al cargar usuarios:', error);
      },
    });
  }

  seleccionarMesa(mesa: Table) {
    this.selectedTable = mesa;
    this.mostrarModalPin = true;
    this.pinIngresado = '';
    this.mensajeError = '';
    this.selectedUser = null;
  }

  validarPin() {
    if (!this.selectedUser || !this.pinIngresado) {
      this.mensajeError = 'Por favor selecciona un usuario e ingresa el PIN';
      return;
    }

    if (!/^\d{4}$/.test(this.pinIngresado)) {
      this.mensajeError = 'El PIN debe ser de 4 dígitos';
      return;
    }

    this.userService.validatePin(this.selectedUser.uuid, this.pinIngresado).subscribe({
      next: (response: any) => {
        // PIN válido - guardar en OrderStateService
        if (this.selectedTable && this.selectedUser) {
          this.orderStateService.setTableAndUser(this.selectedTable, this.selectedUser);
        }
        this.cerrarModal();
      },
      error: (error: any) => {
        this.mensajeError = error.error?.message || 'PIN inválido';
      },
    });
  }

  cerrarModal() {
    this.mostrarModalPin = false;
    this.selectedTable = null;
    this.selectedUser = null;
    this.pinIngresado = '';
    this.mensajeError = '';
  }

  trackByMesa(index: number, mesa: Table): string {
    return mesa.uuid;
  }
}


