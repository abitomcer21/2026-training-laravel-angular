import { Component, Input, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { IonicModule } from '@ionic/angular';
import { AlertController } from '@ionic/angular';
import { addIcons } from 'ionicons';
import { forkJoin } from 'rxjs';
import {
  mapOutline, searchOutline, closeOutline,
  createOutline, trashOutline
} from 'ionicons/icons';

import { ZoneService, Zone } from '../../services/api/zone.service';
import { TableService } from '../../services/api/table.service';
import { ZoneStateService } from '../../services/shared/zone-state.service';
import { AuthService } from '../../services/auth/auth.service';

interface ZoneEditForm {
  name: string;
}

interface ZoneCreateForm {
  name: string;
}

@Component({
  selector: 'app-zonas',
  templateUrl: './zonas.component.html',
  styleUrls: ['./zonas.component.scss'],
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    IonicModule
  ]
})
export class ZonasComponent implements OnInit {
  @Input() set active(value: boolean) {
    this._active = value;
    if (value && !this.zonasCargadas) {
      this.cargarZonas();
    }
  }

  get active(): boolean {
    return this._active;
  }

  private _active: boolean = false;

  // Loading indicadores
  zonasLoading: boolean = false;
  zonasCargadas: boolean = false;

  // Datos
  zones: Zone[] = [];
  zonasFiltradas: Zone[] = [];

  // Estado del panel
  zonePanelMode: 'edit' | 'create' = 'create';
  editingZone: Zone | null = null;

  // Formularios
  editZoneForm: ZoneEditForm = {
    name: '',
  };
  createZoneForm: ZoneCreateForm = {
    name: '',
  };

  // Búsqueda
  terminoBusquedaZone: string = '';
  filtroActualZone: string = 'nombre';

  constructor(
    private zoneService: ZoneService,
    private tableService: TableService,
    private zoneStateService: ZoneStateService,
    private authService: AuthService,
    private alertController: AlertController
  ) {
    addIcons({
      mapOutline, searchOutline, closeOutline,
      createOutline, trashOutline
    });
  }

  ngOnInit() {
    this.cargarZonas();
  }

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
          this.zoneService.invalidateZonesCache();
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
        this.zoneService.invalidateZonesCache();

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
    this.tableService.getTables().subscribe({
      next: (response: any) => {
        let allTables: any[] = [];
        if (Array.isArray(response)) {
          allTables = response;
        } else if (response?.tables && Array.isArray(response.tables)) {
          allTables = response.tables;
        } else if (response?.data && Array.isArray(response.data)) {
          allTables = response.data;
        }

        const relatedTables = allTables.filter(t => t.zone_id === zone.id);
        const tableCount = relatedTables.length;

        this.mostrarAlertaConfirmacionEliminacionZona(zone, tableCount);
      },
      error: (error) => {
        console.error('Error al obtener mesas:', error);
        this.mostrarAlertaConfirmacionEliminacionZona(zone, 0);
      }
    });
  }

  async mostrarAlertaConfirmacionEliminacionZona(zone: Zone, tableCount: number) {
    let message = `¿Estás seguro de que quieres eliminar ${zone.name}?`;
    
    if (tableCount > 0) {
      const mesaTexto = tableCount === 1 ? 'mesa' : 'mesas';
      message += ` Se eliminarán ${tableCount} ${mesaTexto} asociada${tableCount === 1 ? '' : 's'}.`;
    }

    const alert = await this.alertController.create({
      header: 'Eliminar zona',
      message: message,
      buttons: [
        {
          text: 'Cancelar',
          role: 'cancel',
          cssClass: 'secondary',
        },
        {
          text: 'Eliminar',
          handler: () => {
            this.eliminarZonaConMesas(zone.id, tableCount);
          },
          cssClass: 'danger',
        },
      ],
    });
    await alert.present();
  }

  private eliminarZonaConMesas(zoneId: string | number, tableCount: number) {
    if (tableCount > 0) {
      this.tableService.getTables().subscribe({
        next: (response: any) => {
          let allTables: any[] = [];
          if (Array.isArray(response)) {
            allTables = response;
          } else if (response?.tables && Array.isArray(response.tables)) {
            allTables = response.tables;
          } else if (response?.data && Array.isArray(response.data)) {
            allTables = response.data;
          }

          const relatedTables = allTables.filter(t => t.zone_id === zoneId);
          
          const deleteObservables = relatedTables.map(table =>
            this.tableService.deleteTable(table.id)
          );

          if (deleteObservables.length > 0) {
            forkJoin(deleteObservables).subscribe({
              next: () => {
                this.eliminarZone(zoneId);
              },
              error: (error) => {
                console.error('Error al eliminar mesas:', error);
                this.eliminarZone(zoneId);
              }
            });
          } else {
            this.eliminarZone(zoneId);
          }
        },
        error: (error) => {
          console.error('Error al obtener mesas para eliminar:', error);
          this.eliminarZone(zoneId);
        }
      });
    } else {
      this.eliminarZone(zoneId);
    }
  }

  private eliminarZone(id: string | number) {
    this.zoneService.deleteZone(id.toString()).subscribe({
      next: () => {
        this.zones = this.zones.filter(z => z.id?.toString() !== id.toString());
        this.zonasFiltradas = this.zonasFiltradas.filter(z => z.id?.toString() !== id.toString());
        
        this.zoneStateService.notifyZoneDeleted(id.toString());
        
        this.zoneService.invalidateZonesCache();
        this.tableService.invalidateTablesCache();
      },
      error: (error) => {
        console.error('Error al eliminar zona:', error);
      },
    });
  }

  eliminarZoneOld(id: string | number) {
    this.zoneService.deleteZone(id.toString()).subscribe({
      next: () => {
        this.zoneService.invalidateZonesCache();
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
}
