import { Component, OnInit, Output, EventEmitter, ChangeDetectorRef, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Subject } from 'rxjs';
import { takeUntil } from 'rxjs/operators';
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
export class MesasComponent implements OnInit, OnDestroy {
  mostrarModalComensales = false;
  cantidadComensalesIngresada = '';
  @Output() vistaChange = new EventEmitter<string>();

  mesas: Table[] = [];
  mesasFiltradas: Table[] = [];
  zonas: Zone[] = [];
  usuarios: User[] = [];
  cargando = false;
  mostrarModalPin = false;
  selectedTable: Table | null = null;
  selectedUser: User | null = null;
  pinIngresado = '';
  mensajeError = '';
  zonaSeleccionada: Zone | null = null;
  
  private destroy$ = new Subject<void>();

  constructor(
    private tableService: TableService,
    private userService: UserService,
    private orderStateService: OrderStateService,
    private authService: AuthService,
    private zoneService: ZoneService,
    private changeDetector: ChangeDetectorRef,
  ) {
    addIcons({ gridOutline, closeOutline, arrowBackOutline, arrowForwardOutline, backspaceOutline });
    
    // Exponer métodos de debug en la consola
    (window as any).debugPedidos = () => {
      console.log('📂 Llamando debugLocalStorage()...');
      this.orderStateService.debugLocalStorage();
    };
    
    (window as any).debugBlockStatus = () => {
      console.log('🔐 Llamando debugBlockStatus()...');
      this.orderStateService.debugBlockStatus();
    };
  }

  ngOnInit() {
    // Limpiar datos residuales antes de hacer nada
    this.orderStateService.limpiarPedidosResiduales();
    
    // Cargar datos iniciales
    this.tableService.invalidateTablesCache();
    this.cargarZonas();
    this.cargarMesas();
    this.cargarUsuarios();
    
    // Suscribirse a cambios en pedidos activos para actualizar colores y estado
    // Cuando se limpia un pedido (mesa se libera), forzar detección de cambios
    this.orderStateService.getActiveOrdersChanged()
      .pipe(takeUntil(this.destroy$))
      .subscribe(() => {
        // Limpiar residuales cada vez que hay un cambio
        this.orderStateService.limpiarPedidosResiduales();
        // Forzar que Angular recalcule los bindings de isTableOccupied
        this.changeDetector.markForCheck();
        // También hacer un segundo chequeo con delay para asegurar sincronización
        setTimeout(() => {
          this.changeDetector.markForCheck();
        }, 50);
      });
  }

  ngOnDestroy() {
    this.destroy$.next();
    this.destroy$.complete();
  }

  refrescarMesas() {
    // Recargar las mesas (se llama cuando se vuelve desde productos)
    // Forzar que se recalcule el estado de ocupación de todas las mesas
    this.tableService.invalidateTablesCache();
    this.cargarMesas();
    // Forzar detección de cambios después de recargar
    setTimeout(() => {
      this.changeDetector.markForCheck();
    }, 100);
  }

  cargarZonas() {
    const userData = this.authService.getUserData();
    const restaurantId = userData?.restaurant_id;

    if (!restaurantId) {
      console.error('No se encontró restaurant_id del usuario');
      return;
    }

    this.zoneService.getZones().subscribe({
      next: (response: any) => {
        const todasLasZonas = response.zones || [];
        this.zonas = todasLasZonas.filter((zona: Zone) => zona.restaurant_id === restaurantId);
      },
      error: (error) => {
        console.error('Error al cargar zonas:', error);
      },
    });
  }

  cargarMesas() {
    this.cargando = true;
    
    // Limpiar pedidos residuales antes de cargar
    this.orderStateService.limpiarPedidosResiduales();
    
    const userData = this.authService.getUserData();
    const restaurantId = userData?.restaurant_id;

    if (!restaurantId) {
      console.error('No se encontró restaurant_id del usuario');
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
        console.error('Error al cargar mesas:', error);
        this.cargando = false;
      },
    });
  }

  filtrarMesasPorZona(zona: Zone | null) {
    this.zonaSeleccionada = zona;
    if (!zona) {
      this.mesasFiltradas = this.mesas;
    } else {
      this.mesasFiltradas = this.mesas.filter(
        (mesa: Table) => mesa.zone_id === zona.id || mesa.zone_id === zona.database_id
      );
    }
  }

  limpiarFiltroZona() {
    this.filtrarMesasPorZona(null);
  }

  cargarUsuarios() {
    this.userService.getUsers().subscribe({
      next: (response: any) => {
        // Filtrar usuarios para excluir 'admin' y 'restaurante'
        const allUsers = response.users || [];
        this.usuarios = allUsers.filter((u: any) => u.role !== 'restaurante' && u.role !== 'admin');
      },
      error: (error) => {
        console.error('Error al cargar usuarios:', error);
      },
    });
  }

  isTableOccupied(table: Table): boolean {
    const occupied = this.orderStateService.hasActiveOrderForTable(table.id);
    if (occupied) {
      console.debug(`Mesa ${table.name} (${table.id}) está ocupada`);
    }
    return occupied;
  }

  getTableOccupiedInfo(table: Table): { comensales: number; total: number } | null {
    return this.orderStateService.getTableOccupiedInfo(table.id);
  }

  debugPedidos() {
    // Método para ver qué hay en localStorage (solo en desarrollo)
    const activos = this.orderStateService.getActivos();
    console.log('Pedidos activos en localStorage:', activos);
    console.log('Total de mesas en pantalla:', this.mesasFiltradas.length);
    this.mesasFiltradas.forEach(mesa => {
      const ocupada = this.isTableOccupied(mesa);
      console.log(`  - Mesa ${mesa.name}: ${ocupada ? 'OCUPADA' : 'LIBRE'}`);
    });
  }

  trackByMesa(index: number, mesa: Table): string {
    return mesa.id || mesa.uuid || index.toString();
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

    if (this.pinIngresado !== this.selectedUser.pin) {
      this.mensajeError = 'PIN inválido';
      return;
    }

    if (!this.selectedTable || !this.selectedUser) {
      this.mensajeError = 'Error: selecciona mesa y usuario';
      return;
    }

    // Primero: Verificar si ya existe un pedido para ESTE usuario en ESTA mesa
    const hasUnpaidItems = this.orderStateService.hasUnpaidItemsForTableAndUser(
      this.selectedTable.id,
      this.selectedUser.id
    );

    if (hasUnpaidItems) {
      // Mesa ya está ocupada por ESTE usuario - restaurar ese pedido
      this.orderStateService.setTableAndUser(this.selectedTable, this.selectedUser);
      this.mostrarModalPin = false;
      this.vistaChange.emit('productos');
      return;
    }

    // Segundo: Verificar si hay un pedido activo de OTRO usuario en ESTA mesa
    const existingOrder = this.orderStateService.getActiveOrderForTable(this.selectedTable.id);
    
    if (existingOrder) {
      // Mesa está ocupada - cargar el pedido existente para este usuario
      // (No preguntar, simplemente cargar el pedido ya que la mesa está en uso)
      this.orderStateService.loadExistingOrderForCurrentUser(
        this.selectedTable,
        this.selectedUser,
        existingOrder.data
      );
      this.mostrarModalPin = false;
      this.vistaChange.emit('productos');
    } else {
      // Mesa está libre - pedir comensales para crear un nuevo pedido
      this.mostrarModalPin = false;
      this.mostrarModalComensales = true;
      this.cantidadComensalesIngresada = '';
    }
  }

  confirmarComensales() {
    if (!this.cantidadComensalesIngresada || parseInt(this.cantidadComensalesIngresada) < 1) {
      return;
    }
    if (!this.selectedTable || !this.selectedUser) {
      return;
    }

    // Inicializar el pedido para marcar la mesa como en uso (aunque no haya items aún)
    this.orderStateService.initializeTableOrder(this.selectedTable, this.selectedUser);
    
    // Guardar el número de comensales
    const comensales = parseInt(this.cantidadComensalesIngresada);
    this.orderStateService.setComensales(comensales);
    
    this.mostrarModalComensales = false;
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

  agregarDigitoComensales(digito: string) {
    if (this.cantidadComensalesIngresada.length < 2) {
      this.cantidadComensalesIngresada += digito;
    }
  }

  borrarDigitoComensales() {
    this.cantidadComensalesIngresada = this.cantidadComensalesIngresada.slice(0, -1);
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

  cerrarModal() {
    this.mostrarModalPin = false;
    this.selectedTable = null;
    this.selectedUser = null;
    this.pinIngresado = '';
    this.mensajeError = '';
  }

}