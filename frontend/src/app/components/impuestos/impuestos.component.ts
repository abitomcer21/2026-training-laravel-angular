import { Component, Input, OnInit, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { IonicModule } from '@ionic/angular';
import { AlertController } from '@ionic/angular';
import { addIcons } from 'ionicons';
import {
  cashOutline, searchOutline, closeOutline,
  createOutline, trashOutline
} from 'ionicons/icons';

import { TaxService, Tax } from '../../services/api/tax.service';
import { DataCacheService } from '../../services/shared/data-cache.service';
import { AuthService } from '../../services/auth/auth.service';

interface TaxEditForm {
  name: string;
  percentage: number;
}

interface TaxCreateForm {
  name: string;
  percentage: number;
}

@Component({
  selector: 'app-impuestos',
  templateUrl: './impuestos.component.html',
  styleUrls: ['./impuestos.component.scss'],
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    IonicModule
  ]
})
export class ImpuestosComponent implements OnInit {
  @Input() set active(value: boolean) {
    this._active = value;
  }

  get active(): boolean {
    return this._active;
  }

  private _active: boolean = false;

  // Loading indicadores
  impuestosLoading: boolean = false;
  impuestoCargados: boolean = false;

  // Datos
  taxes: Tax[] = [];
  impuestosFiltrados: Tax[] = [];

  // Estado del panel
  taxPanelMode: 'edit' | 'create' = 'create';
  editingTax: Tax | null = null;

  // Formularios
  editTaxForm: TaxEditForm = {
    name: '',
    percentage: 0,
  };
  createTaxForm: TaxCreateForm = {
    name: '',
    percentage: 0,
  };

  // Búsqueda
  terminoBusquedaTax: string = '';
  filtroActualTax: string = 'nombre';

  constructor(
    private taxService: TaxService,
    private authService: AuthService,
    private alertController: AlertController,
    private cd: ChangeDetectorRef,
    private dataCacheService: DataCacheService
  ) {
    addIcons({
      cashOutline, searchOutline, closeOutline,
      createOutline, trashOutline
    });
  }

  ngOnInit() {
    // Solo cargar desde backend si no hay datos en caché
    const cachedTaxes = this.dataCacheService.getTaxes();
    if (cachedTaxes && cachedTaxes.length > 0) {
      this.taxes = [...cachedTaxes];
      this.impuestosFiltrados = [...this.taxes];
      this.impuestoCargados = true;
    } else {
      this.cargarImpuestos();
    }
  }

  cargarImpuestos() {
    this.impuestosLoading = true;
    const userData = this.authService.getUserData();
    const userRestaurantId = userData?.restaurant_id;

    this.taxService.getTaxes().subscribe({
      next: (response: any) => {
        let taxes: any[] = [];
        if (Array.isArray(response)) {
          taxes = response;
        } else if (response?.tax && Array.isArray(response.tax)) {
          taxes = response.tax;
        } else if (response?.Tax && Array.isArray(response.Tax)) {
          taxes = response.Tax;
        } else if (response?.data && Array.isArray(response.data)) {
          taxes = response.data;
        } else {
          taxes = [];
        }

        if (userRestaurantId) {
          this.taxes = taxes.filter(t => t.restaurant_id === userRestaurantId);
        } else {
          this.taxes = taxes;
        }

        this.impuestosFiltrados = [...this.taxes];
        this.impuestoCargados = true;
        this.impuestosLoading = false;
        // Forzar refresco visual
        this.cd.detectChanges();
      },
      error: (error) => {
        console.error('Error al cargar impuestos:', error);
        this.taxes = [];
        this.impuestosFiltrados = [];
        this.impuestoCargados = false;
        this.impuestosLoading = false;
      }
    });
  }

  buscarImpuestos() {
    if (!this.terminoBusquedaTax) {
      this.impuestosFiltrados = [...this.taxes];
      return;
    }

    const termino = this.terminoBusquedaTax.toLowerCase();

    this.impuestosFiltrados = this.taxes.filter((tax, index) => {
      switch (this.filtroActualTax) {
        case 'id':
          const displayId = (index + 1).toString();
          return displayId.includes(termino);
        case 'nombre':
        default:
          return tax.name.toLowerCase().includes(termino);
      }
    });
  }

  filtrarPorTipoTax(tipo: string) {
    this.filtroActualTax = tipo;
    this.buscarImpuestos();
  }

  limpiarBusquedaTax() {
    this.terminoBusquedaTax = '';
    this.impuestosFiltrados = [...this.taxes];
  }

  abrirEdicionTax(tax: Tax) {
    this.taxPanelMode = 'edit';
    this.editingTax = tax;
    this.editTaxForm = {
      name: tax.name,
      percentage: tax.percentage,
    };
  }

  salirEdicionTax() {
    this.taxPanelMode = 'create';
    this.editingTax = null;
    this.editTaxForm = {
      name: '',
      percentage: 0,
    };
  }

  creatEmptyTaxForm(): TaxCreateForm {
    return {
      name: '',
      percentage: 0,
    };
  }

  guardarImpuestoPanel() {
    if (this.taxPanelMode === 'edit') {
      this.guardarEdicionTax();
      return;
    }

    if (this.taxPanelMode === 'create') {
      this.guardarNuevoTax();
    }
  }

  async guardarEdicionTax() {
    if (this.editingTax === null) {
      return;
    }

    if (!this.editTaxForm.name.trim() || this.editTaxForm.percentage < 0 || this.editTaxForm.percentage > 100) {
      console.error('Faltan campos obligatorios o porcentaje inválido');
      return;
    }

    const payload: any = {
      name: this.editTaxForm.name.trim(),
      percentage: Number(this.editTaxForm.percentage),
    };

    this.taxService.updateTax(this.editingTax.id.toString(), payload).subscribe({
      next: (response: any) => {
        const taxIndex = this.taxes.findIndex(t => t.id?.toString() === this.editingTax?.id?.toString());

        if (taxIndex >= 0) {
          this.taxes[taxIndex] = {
            ...this.taxes[taxIndex],
            name: response?.name ?? this.taxes[taxIndex].name,
            percentage: response?.percentage ?? this.taxes[taxIndex].percentage,
            updated_at: response?.updated_at ?? this.taxes[taxIndex].updated_at,
          };
          this.impuestosFiltrados = [...this.taxes];
          this.taxService.invalidateTaxesCache();
        }

        this.mostrarConfirmacionGuardadoTax();
        this.salirEdicionTax();
      },
      error: (error) => {
        console.error('Error al actualizar:', error);
        this.mostrarErrorGuardadoTax();
      }
    });
  }

  guardarNuevoTax() {
    const userData = this.authService.getUserData();
    const restaurantId = userData?.restaurant_id;

    if (!restaurantId) {
      console.error('No se pudo obtener el restaurant_id del usuario autenticado');
      return;
    }

    if (!this.createTaxForm.name.trim() || this.createTaxForm.percentage < 0 || this.createTaxForm.percentage > 100) {
      console.error('Faltan campos obligatorios o porcentaje inválido');
      return;
    }

    const payload: any = {
      name: this.createTaxForm.name.trim(),
      percentage: Number(this.createTaxForm.percentage),
      restaurant_id: Number(restaurantId),
    };

    this.taxService.createTax(payload).subscribe({
      next: (response: any) => {
        const createdTax: Tax = {
          id: response?.id ?? response?.uuid,
          uuid: response?.id ?? response?.uuid,
          name: response?.name ?? this.createTaxForm.name.trim(),
          percentage: response?.percentage ?? this.createTaxForm.percentage,
          restaurant_id: response?.restaurant_id ?? restaurantId,
        };

        this.taxes = [...this.taxes, createdTax];
        this.taxService.invalidateTaxesCache();

        if (this.terminoBusquedaTax) {
          this.buscarImpuestos();
        } else {
          this.impuestosFiltrados = [...this.taxes];
        }

        this.createTaxForm = this.creatEmptyTaxForm();
        this.mostrarConfirmacionGuardadoTax();
      },
      error: (error) => {
        console.error('Error al crear:', error);
        this.mostrarErrorGuardadoTax();
      }
    });
  }

  async confirmarEliminarTax(tax: Tax) {
    const alert = await this.alertController.create({
      header: 'Eliminar impuesto',
      message: `¿Estás seguro de que quieres eliminar ${tax.name}?`,
      buttons: [
        {
          text: 'Cancelar',
          role: 'cancel',
          cssClass: 'secondary'
        },
        {
          text: 'Eliminar',
          handler: () => {
            this.eliminarTax(tax.id);
          }
        }
      ]
    });
    await alert.present();
  }

  eliminarTax(id: string | number) {
    this.taxService.deleteTax(id.toString()).subscribe({
      next: () => {
        // Eliminar de la lista local inmediatamente
        const idStr = id.toString();
        this.taxes = this.taxes.filter(t => t.id?.toString() !== idStr);
        this.taxService.invalidateTaxesCache();
        this.dataCacheService.setTaxesCache(this.taxes);
        // Actualizar la lista filtrada según el filtro/búsqueda actual
        this.buscarImpuestos();
        // Forzar refresco visual
        this.cd.detectChanges();
        this.mostrarConfirmacionEliminadoTax();
      },
      error: (error) => {
        console.error('Error al eliminar:', error);
      }
    });
  }

  async mostrarConfirmacionEliminadoTax() {
    const alert = await this.alertController.create({
      header: 'IVA eliminado',
      message: 'El impuesto ha sido eliminado correctamente.',
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

  editarTax(tax: Tax) {
    this.abrirEdicionTax(tax);
  }

  async mostrarConfirmacionGuardadoTax() {
    const alert = await this.alertController.create({
      header: 'Cambios guardados',
      message: 'El impuesto ha sido actualizado correctamente.',
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

  async mostrarErrorGuardadoTax() {
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
