import { Component, OnInit, Output, EventEmitter } from '@angular/core';
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
import { gridOutline, closeOutline, arrowBackOutline, arrowForwardOutline, backspaceOutline } from 'ionicons/icons';
import { TableService, Table } from '../../../../services/api/table.service';
import { UserService, User } from '../../../../services/api/user.service';
import { OrderStateService } from '../../../../services/order-state.service';
import { AuthService } from '../../../../services/auth/auth.service';
import { ZoneService, Zone } from '../../../../services/api/zone.service';

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
  @Output() vistaChange = new EventEmitter<string>();

  mesas: Table[] = [];
  mesasFiltradas: Table[] = [];
  zonas: Zone[] = [];
  usuarios: User[] = [];
  cargando = false;
  mostrarModalPin = false;
  mostrarModalComensales = false;
  selectedTable: Table | null = null;
  selectedUser: User | null = null;
  pinIngresado = '';
  cantidadComensalesIngresada = '';
  mensajeError = '';
  zonaSeleccionada: Zone | null = null;
  mesaEsNueva = false;

  constructor(
    private tableService: TableService,
    private userService: UserService,
    private orderStateService: OrderStateService,
    private authService: AuthService,
    private zoneService: ZoneService
  ) {
    addIcons({ gridOutline, closeOutline, arrowBackOutline, arrowForwardOutline, backspaceOutline });
  }

  ngOnInit() {
    this.cargarZonas();
    this.cargarMesas();
    this.cargarUsuarios();
  }

  cargarZonas() {
    const userData = this.authService.getUserData();
    const restaurantId = userData?.restaurant_id;

    if (!restaurantId) {
      return;
    }

    this.zoneService.getZones().subscribe({
      next: (response: any) => {
        const todasLasZonas = response.zones || [];
        this.zonas = todasLasZonas.filter((zona: Zone) => zona.restaurant_id === restaurantId);
      },
      error: (error) => {
      },
    });
  }

  cargarMesas() {
    this.cargando = true;
    const userData = this.authService.getUserData();
    const restaurantId = userData?.restaurant_id;

    if (!restaurantId) {
      this.cargando = false;
      return;
    }

    this.tableService.getTables().subscribe({
      next: (response: any) => {
        const todasLasMesas = response.tables || [];
        this.mesas = todasLasMesas.filter((mesa: Table) => mesa.restaurant_id === restaurantId);
        this.filtrarMesasPorZona(this.zonaSeleccionada);
        this.cargando = false;
      },
      error: (error) => {
        this.cargando = false;
      },
    });
  }

  filtrarMesasPorZona(zona: Zone | null) {
    this.zonaSeleccionada = zona;
    if (!zona) {
      this.mesasFiltradas = this.mesas;
    } else {
      this.mesasFiltradas = this.mesas.filter((mesa: Table) => mesa.zone_id === zona.id || mesa.zone_id === zona.database_id);
    }
  }

  limpiarFiltroZona() {
    this.filtrarMesasPorZona(null);
  }

  cargarUsuarios() {
    this.userService.getUsers().subscribe({
      next: (response: any) => {
        this.usuarios = response.users || [];
      },
      error: (error) => {
      },
    });
  }

  seleccionarMesa(mesa: Table) {
    this.selectedTable = mesa;
    this.mesaEsNueva = !this.isTableOccupied(mesa);
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

    if (this.pinIngresado !== this.selectedUser.pin) {
      this.mensajeError = 'PIN inválido';
      return;
    }

    if (this.selectedTable && this.selectedUser) {
      this.orderStateService.setTableAndUser(this.selectedTable, this.selectedUser);
    }

    // Limpiar estado del modal PIN
    this.mostrarModalPin = false;
    this.vistaChange.emit('productos');
  }

  agregarDigito(digito: string) {
    if (this.pinIngresado.length < 4) {
      this.pinIngresado += digito;
      this.mensajeError = '';

    }
  }

  borrarDigito() {
    this.pinIngresado = this.pinIngresado.slice(0, -1);
  }

  seleccionarUsuario(usuario: User) {
    this.selectedUser = usuario;
    this.pinIngresado = '';
    this.mensajeError = '';
  }

  volverAUsuarios() {
    this.selectedUser = null;
    this.pinIngresado = '';
    this.mensajeError = '';
  }

  getKeyboardNumbers(row: number): number[] {
    const rows = [
      [1, 2, 3],
      [4, 5, 6],
      [7, 8, 9],
    ];
    return rows[row] || [];
  }

  agregarDigitoComensales(digito: string) {
    if (this.cantidadComensalesIngresada.length < 2) {
      this.cantidadComensalesIngresada += digito;
    }
  }

  borrarDigitoComensales() {
    this.cantidadComensalesIngresada = this.cantidadComensalesIngresada.slice(0, -1);
  }



  cerrarModal() {
    this.mostrarModalPin = false;
    this.mostrarModalComensales = false;
    this.selectedTable = null;
    this.selectedUser = null;
    this.pinIngresado = '';
    this.cantidadComensalesIngresada = '';
    this.mensajeError = '';
    this.mesaEsNueva = false;
  }

  confirmarComensales() {
    if (!this.cantidadComensalesIngresada) {
      return;
    }

    const cantidad = parseInt(this.cantidadComensalesIngresada, 10);
    if (isNaN(cantidad) || cantidad <= 0) {
      return;
    }

    // Guardar comensales
    this.orderStateService.setComensales(cantidad);
    
    // Cerrar modal
    this.mostrarModalComensales = false;
    
    // Limpiar estado
    this.cantidadComensalesIngresada = '';
    this.selectedTable = null;
    this.selectedUser = null;
    this.pinIngresado = '';
    this.mensajeError = '';
    this.mesaEsNueva = false;
    
    // Emitir evento para cambiar a productos
    setTimeout(() => {
      this.vistaChange.emit('productos');
    }, 50);
  }

  trackByMesa(index: number, mesa: Table): string {
    return mesa.uuid;
  }

  isTableOccupied(mesa: Table): boolean {
    return this.orderStateService.hasActiveOrderForTable(String(mesa.id));
  }

  getTableOccupiedInfo(mesa: Table): { comensales: number; total: number } | null {
    return this.orderStateService.getTableOccupiedInfo(String(mesa.id));
  }

  refrescarMesas() {
    this.cargarMesas();
  }
}

