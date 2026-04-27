import { Component, Input, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { IonIcon } from '@ionic/angular/standalone';
import { AlertController } from '@ionic/angular';
import { addIcons } from 'ionicons';
import {
    searchOutline, closeOutline, createOutline, trashOutline,
    albumsOutline, gridOutline,
} from 'ionicons/icons';

import { FamilyService, Family } from '../../services/api/family.service';
import { AuthService } from '../../services/auth/auth.service';

interface FamilyEditForm {
    name: string;
}

interface FamilyCreateForm {
    name: string;
    active: boolean | string;
}

@Component({
    selector: 'app-familias',
    templateUrl: './familias.component.html',
    styleUrls: ['./familias.component.scss'],
    standalone: true,
    imports: [CommonModule, FormsModule, IonIcon],
})
export class FamiliasComponent implements OnInit {
    @Input() set active(value: boolean) {
        this._active = value;
        if (value && !this.familiasCargadas) {
            this.cargarFamilias();
        }
    }

    get active(): boolean {
        return this._active;
    }

    private _active: boolean = false;

    familiasLoading = false;
    familiasCargadas = false;
    familyPanelMode: 'edit' | 'create' = 'create';
    editingFamily: Family | null = null;

    editFamilyForm: FamilyEditForm = { name: '' };
    createFamilyForm: FamilyCreateForm = { name: '', active: true };

    families: Family[] = [];
    familiasFiltradas: Family[] = [];
    terminoBusquedaFamily = '';
    filtroActualFamily = 'nombre';

    constructor(
        private familyService: FamilyService,
        private authService: AuthService,
        private alertController: AlertController,
    ) {
        addIcons({
            searchOutline, closeOutline, createOutline, trashOutline,
            albumsOutline, gridOutline,
        });
    }

    ngOnInit() {
        // La carga se dispara mediante el setter de @Input active
    }

    cargarFamilias() {
        this.familiasLoading = true;
        const userData = this.authService.getUserData();
        const userRestaurantId = userData?.restaurant_id;

        this.familyService.getFamilies().subscribe({
            next: (response: any) => {
                let families: any[] = [];
                if (Array.isArray(response)) {
                    families = response;
                } else if (response?.family && Array.isArray(response.family)) {
                    families = response.family;
                } else if (response?.Family && Array.isArray(response.Family)) {
                    families = response.Family;
                } else if (response?.data && Array.isArray(response.data)) {
                    families = response.data;
                } else {
                    families = [];
                }

                // Mapear familias para asegurar que tengan database_id
                families = families.map(f => {
                    if (!f.database_id && f.id && !isNaN(Number(f.id))) {
                        return { ...f, database_id: Number(f.id) };
                    }
                    return f;
                });

                if (userRestaurantId) {
                    this.families = families.filter(family => family.restaurant_id === userRestaurantId);
                } else {
                    this.families = families;
                }

                this.familiasFiltradas = [...this.families];
                this.familiasCargadas = true;
                this.familiasLoading = false;
            },
            error: (error) => {
                console.error('Error al cargar familias:', error);
                this.families = [];
                this.familiasFiltradas = [];
                this.familiasCargadas = false;
                this.familiasLoading = false;
            },
        });
    }

    buscarFamilias() {
        if (!this.terminoBusquedaFamily) {
            this.familiasFiltradas = [...this.families];
            return;
        }

        const termino = this.terminoBusquedaFamily.toLowerCase();

        this.familiasFiltradas = this.families.filter((family, index) => {
            switch (this.filtroActualFamily) {
                case 'id':
                    const displayId = (index + 1).toString();
                    return displayId.includes(termino);
                case 'nombre':
                default:
                    return family.name.toLowerCase().includes(termino);
            }
        });
    }

    filtrarPorTipoFamily(tipo: string) {
        this.filtroActualFamily = tipo;
        this.buscarFamilias();
    }

    limpiarBusquedaFamily() {
        this.terminoBusquedaFamily = '';
        this.familiasFiltradas = [...this.families];
    }

    abrirEdicionFamily(family: Family) {
        this.familyPanelMode = 'edit';
        this.editingFamily = family;
        this.editFamilyForm = {
            name: family.name,
        };
    }

    salirEdicionFamily() {
        this.familyPanelMode = 'create';
        this.editingFamily = null;
        this.editFamilyForm = {
            name: '',
        };
    }

    creatEmptyFamilyForm(): FamilyCreateForm {
        return {
            name: '',
            active: true,
        };
    }

    guardarFamiliaPanel() {
        if (this.familyPanelMode === 'edit') {
            this.guardarEdicionFamily();
            return;
        }

        if (this.familyPanelMode === 'create') {
            this.guardarNuevoFamily();
        }
    }

    async guardarEdicionFamily() {
        if (this.editingFamily === null) {
            return;
        }

        const payload: any = {
            name: this.editFamilyForm.name.trim() || this.editingFamily.name,
        };

        this.familyService.updateFamily(this.editingFamily.id.toString(), payload).subscribe({
            next: (response: any) => {
                const familyIndex = this.families.findIndex(f => f.id?.toString() === this.editingFamily?.id?.toString());

                if (familyIndex >= 0) {
                    this.families[familyIndex] = {
                        ...this.families[familyIndex],
                        name: response?.name ?? this.families[familyIndex].name,
                        updated_at: response?.updated_at ?? this.families[familyIndex].updated_at,
                    };
                    this.familiasFiltradas = [...this.families];
                }

                this.mostrarConfirmacionGuardadoFamily();
                this.salirEdicionFamily();
            },
            error: (error) => {
                console.error('Error al actualizar:', error);
                this.mostrarErrorGuardadoFamily();
            },
        });
    }

    guardarNuevoFamily() {
        const userData = this.authService.getUserData();
        const restaurantId = userData?.restaurant_id;

        if (!restaurantId) {
            console.error('No se pudo obtener el restaurant_id del usuario autenticado');
            return;
        }

        if (!this.createFamilyForm.name.trim()) {
            console.error('Faltan campos obligatorios');
            return;
        }

        let activeValue = this.createFamilyForm.active;
        if (typeof activeValue === 'string') {
            activeValue = activeValue === 'true' || activeValue === '1';
        }

        const payload: any = {
            name: this.createFamilyForm.name.trim(),
            active: activeValue,
            restaurant_id: Number(restaurantId),
        };

        this.familyService.createFamily(payload).subscribe({
            next: (response: any) => {
                const createdFamily: Family = {
                    id: response?.id ?? response?.uuid,
                    name: response?.name ?? this.createFamilyForm.name.trim(),
                    active: response?.active ?? this.createFamilyForm.active,
                    restaurant_id: response?.restaurant_id ?? restaurantId,
                };

                this.families = [...this.families, createdFamily];

                if (this.terminoBusquedaFamily) {
                    this.buscarFamilias();
                } else {
                    this.familiasFiltradas = [...this.families];
                }

                this.createFamilyForm = this.creatEmptyFamilyForm();
                this.mostrarConfirmacionGuardadoFamily();
            },
            error: (error) => {
                console.error('Error al crear:', error);
                this.mostrarErrorGuardadoFamily();
            },
        });
    }

    async confirmarEliminarFamily(family: Family) {
        const alert = await this.alertController.create({
            header: 'Eliminar familia',
            message: `¿Estás seguro de que quieres eliminar <strong>${family.name}</strong>?`,
            buttons: [
                {
                    text: 'Cancelar',
                    role: 'cancel',
                    cssClass: 'secondary',
                },
                {
                    text: 'Eliminar',
                    handler: () => {
                        this.eliminarFamily(family.id);
                    },
                },
            ],
        });
        await alert.present();
    }

    eliminarFamily(id: string | number) {
        this.familyService.deleteFamily(id.toString()).subscribe({
            next: () => {
                this.cargarFamilias();
            },
            error: (error) => {
                console.error('Error al eliminar:', error);
            },
        });
    }

    editarFamily(family: Family) {
        this.abrirEdicionFamily(family);
    }

    cambiarEstadoFamily(family: Family) {
        const payload = {
            active: !family.active,
        };

        this.familyService.updateFamily(family.id.toString(), payload).subscribe({
            next: (response: any) => {
                const familyIndex = this.families.findIndex(f => f.id?.toString() === family.id?.toString());

                if (familyIndex >= 0) {
                    this.families[familyIndex].active = response?.active ?? !this.families[familyIndex].active;
                    this.familiasFiltradas = [...this.families];
                }
            },
            error: (error) => {
                console.error('Error al cambiar estado:', error);
            },
        });
    }

    async mostrarConfirmacionGuardadoFamily() {
        const alert = await this.alertController.create({
            header: 'Cambios guardados',
            message: 'La familia ha sido actualizada correctamente.',
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

    async mostrarErrorGuardadoFamily() {
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
