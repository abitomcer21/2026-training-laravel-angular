import { Component, OnInit, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import {
  IonIcon,
  IonLoading,
  IonModal,
  IonContent,
} from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import {
  gridOutline,
  listOutline,
  searchOutline,
  closeOutline,
  arrowForwardOutline,
  backspaceOutline,
  peopleOutline,
  timeOutline,
} from 'ionicons/icons';
import { TableService, Table } from '../../../../services/api/table.service';
import { UserService, User } from '../../../../services/api/user.service';
import { OrderStateService } from '../../../../services/order-state.service';
import { AuthService } from '../../../../services/auth/auth.service';
import { ZoneService, Zone } from '../../../../services/api/zone.service';
import {
  SesiónCamareroService,
  ActiveWaiter,
} from '../../../../services/sesion-camarero.service';
import { MesaSearchPipe } from '../../../../pipes/mesa-search.pipe';

@Component({
  selector: 'app-mesas',
  templateUrl: './panel-mesas.component.html',
  styleUrls: ['./panel-mesas.component.scss'],
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    IonIcon,
    IonLoading,
    IonModal,
    IonContent,
    MesaSearchPipe,
  ],
})
export class MesasComponent implements OnInit {
  @Output() vistaChange = new EventEmitter<string>();
  @Output() waiterSessionChange = new EventEmitter<ActiveWaiter | null>();

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

  viewMode: 'grid' | 'list' = 'grid';
  searchQuery = '';

  private tableOpenTimes: Map<string, Date> = new Map();

  private readonly ZONA_OCUPADAS_ID = -1;

  constructor(
    private tableService: TableService,
    private userService: UserService,
    private orderStateService: OrderStateService,
    private authService: AuthService,
    private zoneService: ZoneService,
    private SesiónCamareroService: SesiónCamareroService,
  ) {
    addIcons({
      gridOutline,
      listOutline,
      searchOutline,
      closeOutline,
      arrowForwardOutline,
      backspaceOutline,
      peopleOutline,
      timeOutline,
    });
  }

  ngOnInit() {
    this.cargarZonas();
    this.cargarMesas();
    this.cargarUsuarios();
  }

  cargarZonas() {
    const userData = this.authService.getUserData();
    const restaurantId = userData?.restaurant_id;

    if (!restaurantId) return;

    this.zoneService.getZones().subscribe({
      next: (response: any) => {
        const todasLasZonas = response.zones || [];
        const zonasFiltradas = todasLasZonas.filter(
          (zona: Zone) => zona.restaurant_id === restaurantId,
        );

        this.zonas = [
          ...zonasFiltradas,
          { id: '-1', name: 'OCUPADAS', restaurant_id: restaurantId } as Zone,
        ];

        if (this.zonas.length > 0 && !this.zonaSeleccionada) {
          this.filtrarMesasPorZona(this.zonas[0]);
        }
      },
      error: (error) => console.error('Error cargando zonas:', error),
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
        this.mesas = todasLasMesas.filter(
          (mesa: Table) => mesa.restaurant_id === restaurantId,
        );
        this.filtrarMesasPorZona(this.zonaSeleccionada);
        this.cargando = false;
      },
      error: (error) => {
        console.error('Error cargando mesas:', error);
        this.cargando = false;
      },
    });
  }

  filtrarMesasPorZona(zona: Zone | null) {
    this.zonaSeleccionada = zona;
    if (!zona) {
      this.mesasFiltradas = this.mesas;
    } else if (zona.id === '-1') {
      this.mesasFiltradas = this.mesas.filter((mesa) =>
        this.isTableOccupied(mesa),
      );
    } else {
      this.mesasFiltradas = this.mesas.filter(
        (mesa) => mesa.zone_id === zona.id || mesa.zone_id === zona.database_id,
      );
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
      error: (error) => console.error('Error cargando usuarios:', error),
    });
  }

  seleccionarMesa(mesa: Table) {
    this.selectedTable = mesa;
    const camareroActual = this.SesiónCamareroService.obtenerCamareroActual();

    if (camareroActual) {
      this.SesiónCamareroService.renovarSesion();
      const camarero = this.usuarios.find(
        (u) =>
          String(u.id) === camareroActual.uuid ||
          u.name === camareroActual.name,
      );

      if (camarero) {
        this.selectedUser = camarero;
        this.mesaEsNueva = !this.isTableOccupied(mesa);
        this.orderStateService.setTableAndUser(mesa, this.selectedUser);

        if (this.mesaEsNueva) {
          this.mostrarModalComensales = true;
          this.cantidadComensalesIngresada = '';
        } else {
          this.vistaChange.emit('productos');
        }

        return;
      }
    }

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
      this.pinIngresado = '';
      return;
    }

    const camarero: ActiveWaiter = {
      uuid: String(this.selectedUser.id),
      name: this.selectedUser.name,
      pin: this.selectedUser.pin,
      role: this.selectedUser.role,
    };

    this.SesiónCamareroService.iniciarSesion(camarero);
    this.waiterSessionChange.emit(camarero);

    if (this.selectedTable && this.selectedUser) {
      this.orderStateService.setTableAndUser(
        this.selectedTable,
        this.selectedUser,
      );
    }

    this.mostrarModalPin = false;

    if (this.mesaEsNueva) {
      this.mostrarModalComensales = true;
      this.cantidadComensalesIngresada = '';
    } else {
      this.vistaChange.emit('productos');
    }
  }

  agregarDigito(digito: string) {
    if (this.pinIngresado.length < 4) {
      this.pinIngresado += digito;
      this.mensajeError = '';
      if (this.pinIngresado.length === 4) {
        setTimeout(() => this.validarPin(), 150);
      }
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
    this.cantidadComensalesIngresada = this.cantidadComensalesIngresada.slice(
      0,
      -1,
    );
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
    if (!this.cantidadComensalesIngresada) return;
    const cantidad = parseInt(this.cantidadComensalesIngresada, 10);
    if (isNaN(cantidad) || cantidad <= 0) return;

    if (this.selectedTable) {
      this.tableOpenTimes.set(String(this.selectedTable.id), new Date());
    }

    this.SesiónCamareroService.renovarSesion();
    this.orderStateService.setComensales(cantidad);
    this.mostrarModalComensales = false;
    this.cantidadComensalesIngresada = '';
    this.selectedTable = null;
    this.selectedUser = null;
    this.pinIngresado = '';
    this.mensajeError = '';
    this.mesaEsNueva = false;

    setTimeout(() => this.vistaChange.emit('productos'), 50);
  }

  isTableOccupied(mesa: Table): boolean {
    return this.orderStateService.hasActiveOrderForTable(String(mesa.id));
  }

  isTablePendingBill(mesa: Table): boolean {
    return false;
  }

  getTableOccupiedInfo(
    mesa: Table,
  ): { comensales: number; total: number } | null {
    return this.orderStateService.getTableOccupiedInfo(String(mesa.id));
  }

  getTableTime(mesa: Table): string {
    const openTime = this.tableOpenTimes.get(String(mesa.id));
    if (!openTime) return '00:00';
    const diff = Math.floor((Date.now() - openTime.getTime()) / 1000);
    const h = Math.floor(diff / 3600);
    const m = Math.floor((diff % 3600) / 60);
    return h > 0
      ? `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`
      : `${String(m).padStart(2, '0')}:${String(diff % 60).padStart(2, '0')}`;
  }

  openTable(tableId: string): void {
    const mesa = this.mesas.find(
      (m) => String(m.id) === tableId || String(m.uuid) === tableId,
    );
    if (mesa) {
      this.seleccionarMesa(mesa);
      return;
    }
    if (!this.cargando) this.cargarMesas();
    setTimeout(() => {
      const found = this.mesas.find(
        (m) => String(m.id) === tableId || String(m.uuid) === tableId,
      );
      if (found) this.seleccionarMesa(found);
    }, 300);
  }

  refrescarMesas() {
    this.cargarMesas();
  }

  cerrarSesionCamarero() {
    this.SesiónCamareroService.cerrarSesion();
    this.waiterSessionChange.emit(null);
    this.selectedUser = null;
  }

  trackByMesa(index: number, mesa: Table): string {
    return mesa.uuid;
  }
}
