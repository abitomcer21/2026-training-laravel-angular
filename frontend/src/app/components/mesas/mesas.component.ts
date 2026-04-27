import { Component, Input, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { IonicModule } from '@ionic/angular';
import { AlertController } from '@ionic/angular';
import { addIcons } from 'ionicons';
import {
  gridOutline, searchOutline, closeOutline,
  createOutline, trashOutline, locationOutline, mapOutline
} from 'ionicons/icons';

import { TableService, Table } from '../../services/api/table.service';
import { ZoneService, Zone } from '../../services/api/zone.service';
import { AuthService } from '../../services/auth/auth.service';

interface TableEditForm {
  name: string;
}

interface TableCreateForm {
  name: string;
  zone_id: string | number;
}

@Component({
  selector: 'app-mesas',
  templateUrl: './mesas.component.html',
  styleUrls: ['./mesas.component.scss'],
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    IonicModule
  ]
})
export class MesasComponent implements OnInit {
  @Input() set active(value: boolean) {
    this._active = value;
    if (value && !this.mesasCargadas) {
      this.cargarMesas();
    }
  }

  get active(): boolean {
    return this._active;
  }

  private _active: boolean = false;

  // Loading indicadores
  mesasLoading: boolean = false;
  mesasCargadas: boolean = false;

  // Datos
  tables: Table[] = [];
  mesasFiltradas: Table[] = [];
  zones: Zone[] = [];

  // Estado del panel
  tablePanelMode: 'edit' | 'create' = 'create';
  zonaSeleccionadaFiltro: string | number | null = null;
  editingTable: Table | null = null;

  // Formularios
  editTableForm: TableEditForm = {
    name: '',
  };
  createTableForm: TableCreateForm = {
    name: '',
    zone_id: '',
  };

  // Búsqueda
  terminoBusquedaTable: string = '';
  filtroActualTable: string = 'nombre';

  constructor(
    private tableService: TableService,
    private zoneService: ZoneService,
    private authService: AuthService,
    private alertController: AlertController
  ) {
    addIcons({
      gridOutline, searchOutline, closeOutline,
      createOutline, trashOutline, locationOutline, mapOutline
    });
  }

  ngOnInit() {
    // La carga se dispara mediante el setter de @Input active
  }

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
      this.filtrarPorZona(this.zonaSeleccionadaFiltro);
      return;
    }

    const termino = this.terminoBusquedaTable.toLowerCase();

    let filtered = this.tables.filter((table, index) => {
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

    if (this.zonaSeleccionadaFiltro !== null) {
      filtered = filtered.filter(table => {
        const tableZoneId = table.zone_id?.toString() || '';
        const tableZoneNum = Number(table.zone_id);
        const zoneIdStr = this.zonaSeleccionadaFiltro?.toString() || '';
        
        const zone = this.zones.find(z => {
          const zId = z.id?.toString() || '';
          const zUuid = z.uuid?.toString() || '';
          const zDbId = z.database_id;
          return (zDbId && zDbId === this.zonaSeleccionadaFiltro) ||
            zId === zoneIdStr ||
            zUuid === zoneIdStr;
        });

        if (!zone) return false;

        const zoneDbId = zone.database_id;
        return (zoneDbId && zoneDbId === tableZoneNum) ||
          zone.id?.toString() === tableZoneId ||
          zone.uuid?.toString() === tableZoneId;
      });
    }

    this.mesasFiltradas = filtered;
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

      const zoneDbId = zone.database_id;
      return (zoneDbId && zoneDbId === tableZoneNum) ||
        zone.id?.toString() === tableZoneId ||
        zone.uuid?.toString() === tableZoneId;
    }).length;
  }

  obtenerNombreZona(zone_id: any): string {
    const zone = this.zones.find(z => {
      const zId = z.id?.toString() || '';
      const zUuid = z.uuid?.toString() || '';
      const zDbId = z.database_id;
      const zoneIdStr = zone_id?.toString() || '';
      const zoneIdNum = Number(zone_id);
      return (zDbId && zDbId === zoneIdNum) ||
        zId === zoneIdStr ||
        zUuid === zoneIdStr;
    });
    return zone?.name || 'Desconocida';
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

    const zoneIdForBackend = selectedZone.database_id ?? selectedZone.id;

    if (!zoneIdForBackend) {
      alert('Error: La zona seleccionada no tiene un ID válido. Por favor, recarga la página.');
      return;
    }

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

        if (this.terminoBusquedaTable || this.zonaSeleccionadaFiltro !== null) {
          this.buscarMesas();
        } else {
          this.mesasFiltradas = [...this.tables];
        }

        this.createTableForm = this.creatEmptyTableForm();
        this.mostrarConfirmacionGuardadoTable();
      },
      error: (error) => {
        console.error('Error al crear:', error);
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

  manejarCambioZona(value: string | number) {
    if (value === '__create__') {
      this.createTableForm.zone_id = '';
      this.abrirCrearZona();
    }
  }

  async abrirCrearZona() {
    const alert = await this.alertController.create({
      header: 'Crear nueva zona',
      message: 'Ingresa el nombre de la zona:',
      inputs: [
        {
          name: 'zoneName',
          type: 'text',
          placeholder: 'Nombre de la zona'
        }
      ],
      buttons: [
        {
          text: 'Cancelar',
          role: 'cancel'
        },
        {
          text: 'Crear',
          role: 'confirm',
          handler: (data) => {
            const zoneName = data?.zoneName?.trim();
            if (zoneName) {
              this.crearZonaRapida(zoneName);
              return true;
            } else {
              return false;
            }
          }
        }
      ]
    });

    await alert.present();
  }

  crearZonaRapida(name: string) {
    const userData = this.authService.getUserData();
    const restaurantId = userData?.restaurant_id;

    if (!restaurantId) {
      console.error('No se pudo obtener el restaurant_id');
      return;
    }

    const payload = {
      name: name.trim(),
      restaurant_id: restaurantId
    };

    this.zoneService.createZone(payload).subscribe({
      next: (response: any) => {
        const newZone: Zone = {
          id: response?.id ?? response?.uuid,
          uuid: response?.id ?? response?.uuid,
          database_id: response?.database_id,
          name: response?.name,
          restaurant_id: response?.restaurant_id
        };
        this.zones = [...this.zones, newZone];
        this.createTableForm.zone_id = newZone.database_id || newZone.id;
        this.mostrarConfirmacionZonaCreada(name);
      },
      error: (error) => {
        console.error('Error al crear zona:', error);
        alert('Error al crear la zona');
      }
    });
  }

  async mostrarConfirmacionZonaCreada(zoneName: string) {
    const alert = await this.alertController.create({
      header: '¡Zona creada!',
      message: `La zona "${zoneName}" ha sido creada correctamente.`,
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
}

