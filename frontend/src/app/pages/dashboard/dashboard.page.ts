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
import { ZonasComponent } from '../../components/zonas/zonas.component';
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
    IonItem, IonInput, IonChip, IonBadge, UsuariosComponent, FamiliasComponent, ProductosComponent, ImpuestosComponent, ZonasComponent
  ]
})
export class DashboardPage implements OnInit {
  opcionSeleccionada: string = 'usuarios';
  restaurantName: string = 'Yurest TPV';

  // Loading indicadores por sección
  mesasLoading: boolean = false;

  // Datos globales para dropdowns
  zones: Zone[] = [];
  taxes: Tax[] = [];



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