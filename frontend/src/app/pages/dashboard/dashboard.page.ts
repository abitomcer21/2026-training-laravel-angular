import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { forkJoin } from 'rxjs';
import {
  IonHeader, IonToolbar, IonTitle, IonContent, IonButtons,
  IonButton, IonIcon, IonLabel, IonSpinner, IonAvatar,
  IonItem, IonInput, IonChip, IonBadge
} from '@ionic/angular/standalone';
import { AlertController } from '@ionic/angular';
import { addIcons } from 'ionicons';
import {
  logOutOutline, restaurantOutline, peopleOutline,
  mapOutline, cashOutline, gridOutline, albumsOutline,
  addOutline, searchOutline, closeOutline,
  createOutline, trashOutline, shieldCheckmarkOutline
} from 'ionicons/icons';

import { FamilyService, Family } from '../../services/api/family.service';
import { TaxService, Tax } from '../../services/api/tax.service';
import { ZoneService, Zone } from '../../services/api/zone.service';
import { TableService, Table } from '../../services/api/table.service';
import { RestaurantService, Restaurant } from '../../services/api/restaurant.service';
import { AuthService } from '../../services/auth/auth.service';
import { UsuariosComponent } from '../../components/usuarios/usuarios.component';
import { FamiliasComponent } from '../../components/familias/familias.component';
import { ProductosComponent } from '../../components/productos/productos.component';
import { ImpuestosComponent } from '../../components/impuestos/impuestos.component';
interface MenuItem {
  nombre: string;
  valor: string;
  icono: string;
}


interface TaxEditForm {
  name: string;
  percentage: number;
}

interface TaxCreateForm {
  name: string;
  percentage: number;
}

interface ZoneEditForm {
  name: string;
}

interface ZoneCreateForm {
  name: string;
}

interface TableEditForm {
  name: string;
}

interface TableCreateForm {
  name: string;
  zone_id: number | string;
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
    IonButton, IonIcon, IonLabel, IonSpinner, IonAvatar,
    IonItem, IonInput, IonChip, IonBadge, UsuariosComponent, FamiliasComponent, ProductosComponent, ImpuestosComponent
  ]
})
export class DashboardPage implements OnInit {
  opcionSeleccionada: string = 'usuarios';
  restaurantName: string = 'Yurest TPV';

  // Loading indicadores por sección
  zonasLoading: boolean = false;
  mesasLoading: boolean = false;

  // Datos globales para dropdowns
  taxes: Tax[] = [];

  // Zonas
  zones: Zone[] = [];
  zonasFiltradas: Zone[] = [];
  zonasCargadas: boolean = false;
  zonePanelMode: 'edit' | 'create' = 'create';
  editingZone: Zone | null = null;
  editZoneForm: ZoneEditForm = {
    name: '',
  };
  createZoneForm: ZoneCreateForm = {
    name: '',
  };

  // Mesas
  tables: Table[] = [];
  mesasFiltradas: Table[] = [];
  mesasCargadas: boolean = false;
  tablePanelMode: 'edit' | 'create' = 'create';
  zonaSeleccionadaFiltro: string | number | null = null;
  editingTable: Table | null = null;
  editTableForm: TableEditForm = {
    name: '',
  };
  createTableForm: TableCreateForm = {
    name: '',
    zone_id: '',
  };

  // Búsqueda
  terminoBusqueda: string = '';
  filtroActual: string = 'nombre';
  terminoBusquedaZone: string = '';
  filtroActualZone: string = 'nombre';
  terminoBusquedaTable: string = '';
  filtroActualTable: string = 'nombre';

  menuItems: MenuItem[] = [
    { nombre: 'Usuarios', valor: 'usuarios', icono: 'people-outline' },
    { nombre: 'Familias', valor: 'familias', icono: 'albums-outline' },
    { nombre: 'Productos', valor: 'productos', icono: 'restaurant-outline' },
    { nombre: 'Impuestos', valor: 'impuestos', icono: 'cash-outline' },
    { nombre: 'Zonas', valor: 'zonas', icono: 'map-outline' },
    { nombre: 'Mesas', valor: 'mesas', icono: 'grid-outline' }
  ];

  constructor(
    private familyService: FamilyService,
    private taxService: TaxService,
    private zoneService: ZoneService,
    private tableService: TableService,
    private restaurantService: RestaurantService,
    private authService: AuthService,
    private alertController: AlertController
  ) {
    addIcons({
      logOutOutline, restaurantOutline, peopleOutline,
      mapOutline, cashOutline, gridOutline, albumsOutline,
      addOutline, searchOutline, closeOutline,
      createOutline, trashOutline, shieldCheckmarkOutline
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
    if (valor === 'zonas' && !this.zonasCargadas) {
      this.cargarZonas();
    }
    if (valor === 'mesas' && !this.mesasCargadas) {
      this.cargarMesas();
    }
  }


  cerrarSesion() {
    localStorage.removeItem('isLoggedIn');
    localStorage.removeItem('token');
    localStorage.removeItem('userData');
    window.location.href = '/login';
  }

  // ========== MÉTODOS DE ZONAS ==========

  cargarZonas() {
    this.zonasLoading = true;
    const userData = this.authService.getUserData();
    const userRestaurantId = userData?.restaurant_id;

    this.zoneService.getZones().subscribe({
      next: (response: any) => {
        let zones: any[] = [];

        if (Array.isArray(response)) {
          zones = response;
        } else if (response?.zones && Array.isArray(response.zones)) {
          zones = response.zones;
        } else if (response?.Zones && Array.isArray(response.Zones)) {
          zones = response.Zones;
        } else if (response?.data?.zones && Array.isArray(response.data.zones)) {
          zones = response.data.zones;
        } else if (response?.data && Array.isArray(response.data)) {
          zones = response.data;
        } else {
          zones = [];
        }

        if (userRestaurantId) {
          this.zones = zones.filter(zone => zone.restaurant_id === userRestaurantId);
        } else {
          this.zones = zones;
        }

        this.zonasFiltradas = [...this.zones];
        this.zonasCargadas = true;
        this.zonasLoading = false;
      },
      error: (error) => {
        console.error('Error al cargar zonas:', error);
        this.zones = [];
        this.zonasFiltradas = [];
        this.zonasCargadas = false;
        this.zonasLoading = false;
      }
    });
  }

  buscarZonas() {
    if (!this.terminoBusquedaZone) {
      this.zonasFiltradas = [...this.zones];
      return;
    }

    const termino = this.terminoBusquedaZone.toLowerCase();

    this.zonasFiltradas = this.zones.filter((zone, index) => {
      switch (this.filtroActualZone) {
        case 'id':
          const displayId = (index + 1).toString();
          return displayId.includes(termino);
        case 'nombre':
        default:
          return zone.name.toLowerCase().includes(termino);
      }
    });
  }

  filtrarPorTipoZone(tipo: string) {
    this.filtroActualZone = tipo;
    this.buscarZonas();
  }

  limpiarBusquedaZone() {
    this.terminoBusquedaZone = '';
    this.zonasFiltradas = [...this.zones];
  }

  abrirEdicionZone(zone: Zone) {
    this.zonePanelMode = 'edit';
    this.editingZone = zone;
    this.editZoneForm = {
      name: zone.name,
    };
  }

  salirEdicionZone() {
    this.zonePanelMode = 'create';
    this.editingZone = null;
    this.editZoneForm = {
      name: '',
    };
  }

  creatEmptyZoneForm(): ZoneCreateForm {
    return {
      name: '',
    };
  }

  guardarZonaPanel() {
    if (this.zonePanelMode === 'edit') {
      this.guardarEdicionZone();
      return;
    }

    if (this.zonePanelMode === 'create') {
      this.guardarNuevoZone();
    }
  }

  async guardarEdicionZone() {
    if (this.editingZone === null) {
      return;
    }

    const payload: any = {
      name: this.editZoneForm.name.trim() || this.editingZone.name,
    };

    this.zoneService.updateZone(this.editingZone.id.toString(), payload).subscribe({
      next: (response: any) => {
        const zoneIndex = this.zones.findIndex(z => z.id?.toString() === this.editingZone?.id?.toString());

        if (zoneIndex >= 0) {
          this.zones[zoneIndex] = {
            ...this.zones[zoneIndex],
            name: response?.name ?? this.zones[zoneIndex].name,
            updated_at: response?.updated_at ?? this.zones[zoneIndex].updated_at,
          };
          this.zonasFiltradas = [...this.zones];
        }

        this.mostrarConfirmacionGuardadoZone();
        this.salirEdicionZone();
      },
      error: (error) => {
        console.error('Error al actualizar:', error);
        this.mostrarErrorGuardadoZone();
      }
    });
  }

  guardarNuevoZone() {
    const userData = this.authService.getUserData();
    const restaurantId = userData?.restaurant_id;

    if (!restaurantId) {
      console.error('No se pudo obtener el restaurant_id del usuario autenticado');
      return;
    }

    if (!this.createZoneForm.name.trim()) {
      console.error('Faltan campos obligatorios');
      return;
    }

    const payload: any = {
      name: this.createZoneForm.name.trim(),
      restaurant_id: Number(restaurantId),
    };

    this.zoneService.createZone(payload).subscribe({
      next: (response: any) => {
        const createdZone: Zone = {
          id: response?.id ?? response?.uuid,
          uuid: response?.id ?? response?.uuid,
          database_id: response?.database_id,
          name: response?.name ?? this.createZoneForm.name.trim(),
          restaurant_id: response?.restaurant_id ?? restaurantId,
        };

        this.zones = [...this.zones, createdZone];

        if (this.terminoBusquedaZone) {
          this.buscarZonas();
        } else {
          this.zonasFiltradas = [...this.zones];
        }

        this.createZoneForm = this.creatEmptyZoneForm();
        this.mostrarConfirmacionGuardadoZone();
      },
      error: (error) => {
        console.error('Error al crear:', error);
        this.mostrarErrorGuardadoZone();
      }
    });
  }

  async confirmarEliminarZone(zone: Zone) {
    const alert = await this.alertController.create({
      header: 'Eliminar zona',
      message: `¿Estás seguro de que quieres eliminar <strong>${zone.name}</strong>?`,
      buttons: [
        {
          text: 'Cancelar',
          role: 'cancel',
          cssClass: 'secondary'
        },
        {
          text: 'Eliminar',
          handler: () => {
            this.eliminarZone(zone.id);
          }
        }
      ]
    });
    await alert.present();
  }

  eliminarZone(id: string | number) {
    this.zoneService.deleteZone(id.toString()).subscribe({
      next: () => {
        this.cargarZonas();
      },
      error: (error) => {
        console.error('Error al eliminar:', error);
      }
    });
  }

  editarZone(zone: Zone) {
    this.abrirEdicionZone(zone);
  }

  async mostrarConfirmacionGuardadoZone() {
    const alert = await this.alertController.create({
      header: 'Cambios guardados',
      message: 'La zona ha sido actualizada correctamente.',
      buttons: [
        {
          text: 'Aceptar',
          role: 'confirm',
          cssClass: 'success'
        }
      ]
    });
    await alert.present();
  }

  async mostrarErrorGuardadoZone() {
    const alert = await this.alertController.create({
      header: 'Error',
      message: 'No se pudieron guardar los cambios. Intenta de nuevo.',
      buttons: [
        {
          text: 'Aceptar',
          role: 'confirm',
          cssClass: 'danger'
        }
      ]
    });
    await alert.present();
  }

  // ========== MÉTODOS DE MESAS ==========

  cargarMesas() {
    this.mesasLoading = true;
    const userData = this.authService.getUserData();
    const userRestaurantId = userData?.restaurant_id;

    // Asegurar que las zonas estén cargadas
    if (this.zones.length === 0) {
      this.zoneService.getZones().subscribe({
        next: (response: any) => {
          let zones: any[] = [];
          if (Array.isArray(response)) {
            zones = response;
          } else if (response?.zones && Array.isArray(response.zones)) {
            zones = response.zones;
          } else if (response?.data && Array.isArray(response.data)) {
            zones = response.data;
          }
          if (userRestaurantId) {
            this.zones = zones.filter(z => z.restaurant_id === userRestaurantId);
          } else {
            this.zones = zones;
          }
        }
      });
    }

    this.tableService.getTables().subscribe({
      next: (response: any) => {
        let tables: any[] = [];
        if (Array.isArray(response)) {
          tables = response;
        } else if (response?.tables && Array.isArray(response.tables)) {
          tables = response.tables;
        } else if (response?.data && Array.isArray(response.data)) {
          tables = response.data;
        } else {
          tables = [];
        }

        if (userRestaurantId) {
          this.tables = tables.filter(table => table.restaurant_id === userRestaurantId);
        } else {
          this.tables = tables;
        }

        this.mesasFiltradas = [...this.tables];
        this.mesasCargadas = true;
        this.mesasLoading = false;
      },
      error: (error) => {
        console.error('Error al cargar mesas:', error);
        this.tables = [];
        this.mesasFiltradas = [];
        this.mesasCargadas = false;
        this.mesasLoading = false;
      }
    });
  }

  buscarMesas() {
    if (!this.terminoBusquedaTable) {
      this.mesasFiltradas = [...this.tables];
      return;
    }

    const termino = this.terminoBusquedaTable.toLowerCase();

    this.mesasFiltradas = this.tables.filter((table, index) => {
      switch (this.filtroActualTable) {
        case 'id':
          const displayId = (index + 1).toString();
          return displayId.includes(termino);
        case 'zona':
          const zoneIdStr = table.zone_id?.toString() || '';
          const zoneIdNum = Number(table.zone_id);
          const zone = this.zones.find(z => {
            const zId = z.id?.toString() || '';
            const zUuid = z.uuid?.toString() || '';
            const zDbId = z.database_id;
            return (zDbId && zDbId === zoneIdNum) ||
              zId === zoneIdStr ||
              zUuid === zoneIdStr;
          });
          return zone?.name.toLowerCase().includes(termino) || false;
        case 'nombre':
        default:
          return table.name.toLowerCase().includes(termino);
      }
    });
  }

  filtrarPorTipoTable(tipo: string) {
    this.filtroActualTable = tipo;
    this.buscarMesas();
  }

  limpiarBusquedaTable() {
    this.terminoBusquedaTable = '';
    this.zonaSeleccionadaFiltro = null;
    this.mesasFiltradas = [...this.tables];
  }

  filtrarPorZona(zoneId: string | number | null) {
    this.zonaSeleccionadaFiltro = zoneId;
    
    if (zoneId === null) {
      this.mesasFiltradas = [...this.tables];
      return;
    }

    this.mesasFiltradas = this.tables.filter(table => {
      const tableZoneId = table.zone_id?.toString() || '';
      const tableZoneNum = Number(table.zone_id);
      const zoneIdStr = zoneId.toString();
      
      const zone = this.zones.find(z => {
        const zId = z.id?.toString() || '';
        const zUuid = z.uuid?.toString() || '';
        const zDbId = z.database_id;
        return (zDbId && zDbId === zoneId) ||
          zId === zoneIdStr ||
          zUuid === zoneIdStr;
      });

      if (!zone) return false;

      // Comparar con los identificadores de la mesa
      const zoneDbId = zone.database_id;
      return (zoneDbId && zoneDbId === tableZoneNum) ||
        zone.id?.toString() === tableZoneId ||
        zone.uuid?.toString() === tableZoneId;
    });
  }

  contarMesasPorZona(zoneId: string | number): number {
    return this.tables.filter(table => {
      const tableZoneId = table.zone_id?.toString() || '';
      const tableZoneNum = Number(table.zone_id);
      const zoneIdStr = zoneId.toString();
      
      const zone = this.zones.find(z => {
        const zId = z.id?.toString() || '';
        const zUuid = z.uuid?.toString() || '';
        const zDbId = z.database_id;
        return (zDbId && zDbId === zoneId) ||
          zId === zoneIdStr ||
          zUuid === zoneIdStr;
      });

      if (!zone) return false;

      // Comparar con los identificadores de la mesa
      const zoneDbId = zone.database_id;
      return (zoneDbId && zoneDbId === tableZoneNum) ||
        zone.id?.toString() === tableZoneId ||
        zone.uuid?.toString() === tableZoneId;
    }).length;
  }

  abrirEdicionTable(table: Table) {
    this.tablePanelMode = 'edit';
    this.editingTable = table;
    this.editTableForm = {
      name: table.name,
    };
  }

  salirEdicionTable() {
    this.tablePanelMode = 'create';
    this.editingTable = null;
    this.editTableForm = {
      name: '',
    };
  }

  creatEmptyTableForm(): TableCreateForm {
    return {
      name: '',
      zone_id: '',
    };
  }

  guardarMesaPanel() {
    if (this.tablePanelMode === 'edit') {
      this.guardarEdicionTable();
      return;
    }

    if (this.tablePanelMode === 'create') {
      this.guardarNuevoTable();
    }
  }

  async guardarEdicionTable() {
    if (this.editingTable === null) {
      return;
    }

    const payload: any = {
      name: this.editTableForm.name.trim() || this.editingTable.name,
    };

    this.tableService.updateTable(this.editingTable.id.toString(), payload).subscribe({
      next: (response: any) => {
        const tableIndex = this.tables.findIndex(t => t.id?.toString() === this.editingTable?.id?.toString());

        if (tableIndex >= 0) {
          this.tables[tableIndex] = {
            ...this.tables[tableIndex],
            name: response?.name ?? this.tables[tableIndex].name,
            updated_at: response?.updated_at ?? this.tables[tableIndex].updated_at,
          };
          this.mesasFiltradas = [...this.tables];
        }

        this.mostrarConfirmacionGuardadoTable();
        this.salirEdicionTable();
      },
      error: (error) => {
        console.error('Error al actualizar:', error);
        this.mostrarErrorGuardadoTable();
      }
    });
  }

  guardarNuevoTable() {
    const userData = this.authService.getUserData();
    const restaurantId = userData?.restaurant_id;

    if (!restaurantId) {
      alert('Error: No se pudo obtener el ID del restaurante.');
      return;
    }

    if (!this.createTableForm.name.trim()) {
      alert('Por favor, ingresa un nombre para la mesa.');
      return;
    }

    if (!this.createTableForm.zone_id) {
      alert('Por favor, selecciona una zona.');
      return;
    }

    // El formulario puede contener database_id (número como string) o UUID
    // Buscar por ambos para encontrar la zona
    const zoneIdValue = this.createTableForm.zone_id;
    const selectedZone = this.zones.find(z =>
      z.id === zoneIdValue ||
      z.database_id?.toString() === zoneIdValue.toString() ||
      z.uuid === zoneIdValue
    );

    if (!selectedZone) {
      alert('Por favor, selecciona una zona válida.');
      return;
    }

    // El backend espera zone_id como integer (database_id)
    // Intentar usar database_id si está disponible, sino usar el id
    const zoneIdForBackend = selectedZone.database_id ?? selectedZone.id;

    if (!zoneIdForBackend) {
      alert('Error: La zona seleccionada no tiene un ID válido. Por favor, recarga la página.');
      return;
    }

    // Usar el UUID de la zona directamente (el backend acepta string o number)
    const payload: any = {
      name: this.createTableForm.name.trim(),
      zone_id: zoneIdForBackend,
      restaurant_id: Number(restaurantId),
    };

    this.tableService.createTable(payload).subscribe({
      next: (response: any) => {
        const createdTable: Table = {
          id: response?.id ?? response?.uuid,
          uuid: response?.id ?? response?.uuid,
          name: response?.name ?? this.createTableForm.name.trim(),
          zone_id: response?.zone_id ?? this.createTableForm.zone_id,
          restaurant_id: response?.restaurant_id ?? restaurantId,
        };

        this.tables = [...this.tables, createdTable];

        if (this.terminoBusquedaTable) {
          this.buscarMesas();
        } else {
          this.mesasFiltradas = [...this.tables];
        }

        this.createTableForm = this.creatEmptyTableForm();
        this.mostrarConfirmacionGuardadoTable();
      },
      error: (error) => {
        console.error('Error al crear mesa:', error);
        this.mostrarErrorGuardadoTable();
      }
    });
  }

  async confirmarEliminarTable(table: Table) {
    const alert = await this.alertController.create({
      header: 'Eliminar mesa',
      message: `¿Estás seguro de que quieres eliminar <strong>${table.name}</strong>?`,
      buttons: [
        {
          text: 'Cancelar',
          role: 'cancel',
          cssClass: 'secondary'
        },
        {
          text: 'Eliminar',
          handler: () => {
            this.eliminarTable(table.id);
          }
        }
      ]
    });
    await alert.present();
  }

  eliminarTable(id: string | number) {
    this.tableService.deleteTable(id.toString()).subscribe({
      next: () => {
        this.cargarMesas();
      },
      error: (error) => {
        console.error('Error al eliminar:', error);
      }
    });
  }

  editarTable(table: Table) {
    this.abrirEdicionTable(table);
  }

  async mostrarConfirmacionGuardadoTable() {
    const alert = await this.alertController.create({
      header: 'Cambios guardados',
      message: 'La mesa ha sido actualizada correctamente.',
      buttons: [
        {
          text: 'Aceptar',
          role: 'confirm',
          cssClass: 'success'
        }
      ]
    });
    await alert.present();
  }

  async mostrarErrorGuardadoTable() {
    const alert = await this.alertController.create({
      header: 'Error',
      message: 'No se pudieron guardar los cambios. Intenta de nuevo.',
      buttons: [
        {
          text: 'Aceptar',
          role: 'confirm',
          cssClass: 'danger'
        }
      ]
    });
    await alert.present();
  }

  obtenerNombreZona(zoneId: number | string): string {
    if (!zoneId) {
      return 'Sin zona';
    }

    const zoneIdStr = zoneId.toString();
    const zoneIdNum = Number(zoneId);

    const zone = this.zones.find(z => {
      const zId = z.id?.toString() || '';
      const zUuid = z.uuid?.toString() || '';
      const zDbId = z.database_id;

      return (zDbId && zDbId === zoneIdNum) ||
        zId === zoneIdStr ||
        zUuid === zoneIdStr;
    });

    return zone?.name ?? `Zona ${zoneId}`;
  }
}