import { Injectable } from '@angular/core';
import { AlertController } from '@ionic/angular';
import { Product } from '../../../services/api/product.service';

@Injectable({
    providedIn: 'root'
})
export class ProductAlertService {

    constructor(private alertController: AlertController) {}

    async mostrarConfirmacionGuardadoProduct() {
        const alert = await this.alertController.create({
            header: 'Cambios guardados',
            message: 'El producto ha sido actualizado correctamente.',
            buttons: [{ text: 'Aceptar', role: 'confirm', cssClass: 'success' }]
        });
        await alert.present();
    }

    async mostrarErrorGuardadoProduct() {
        const alert = await this.alertController.create({
            header: 'Error',
            message: 'No se pudieron guardar los cambios. Intenta de nuevo.',
            buttons: [{ text: 'Aceptar', role: 'confirm', cssClass: 'danger' }]
        });
        await alert.present();
    }

    async mostrarConfirmacionEliminadoProduct() {
        const alert = await this.alertController.create({
            header: 'Producto eliminado',
            message: 'El producto ha sido eliminado correctamente.',
            buttons: [{ text: 'Aceptar', role: 'confirm', cssClass: 'success' }]
        });
        await alert.present();
    }

    async mostrarConfirmacionFamiliaCreada(familyName: string) {
        const alert = await this.alertController.create({
            header: 'Familia creada',
            message: `La familia "${familyName}" ha sido creada y seleccionada automáticamente.`,
            buttons: [{ text: 'Aceptar', role: 'confirm', cssClass: 'success' }]
        });
        await alert.present();
    }

    async mostrarConfirmacionImpuestoCreado(taxName: string) {
        const alert = await this.alertController.create({
            header: 'Impuesto creado',
            message: `El impuesto "${taxName}" ha sido creado y seleccionado automáticamente.`,
            buttons: [{ text: 'Aceptar', role: 'confirm', cssClass: 'success' }]
        });
        await alert.present();
    }

    async confirmarEliminarProduct(product: Product, onConfirm: (id: string | number) => void) {
        const alert = await this.alertController.create({
            header: 'Eliminar producto',
            message: `¿Estás seguro de que quieres eliminar ${product.name}?`,
            buttons: [
                { text: 'Cancelar', role: 'cancel', cssClass: 'secondary' },
                { text: 'Eliminar', handler: () => { onConfirm(product.id); } }
            ]
        });
        await alert.present();
    }

    async abrirCrearFamilia(onConfirm: (name: string) => void) {
        const alert = await this.alertController.create({
            header: 'Crear familia',
            message: 'Ingresa el nombre de la nueva familia',
            inputs: [
                {
                    name: 'familyName',
                    type: 'text',
                    placeholder: 'Ej: Bebidas, Postres, Carnes...',
                    attributes: { maxlength: 100, autocomplete: 'off' }
                }
            ],
            buttons: [
                { text: 'Cancelar', role: 'cancel', cssClass: 'secondary' },
                {
                    text: 'Crear',
                    handler: (data: any) => {
                        const name = data.familyName?.trim();
                        if (name) { onConfirm(name); return true; }
                        return false;
                    }
                }
            ]
        });
        await alert.present();
    }

    async abrirCrearImpuesto(onConfirm: (name: string, percentage: number) => void) {
        const alert = await this.alertController.create({
            header: 'Crear impuesto',
            message: 'Ingresa el nombre y porcentaje del impuesto',
            inputs: [
                {
                    name: 'taxName',
                    type: 'text',
                    placeholder: 'Ej: IVA, IGIC...',
                    attributes: { maxlength: 100, autocomplete: 'off' }
                },
                {
                    name: 'taxPercentage',
                    type: 'number',
                    placeholder: 'Porcentaje (0-100)',
                    attributes: { min: 0, max: 100, step: 0.01 }
                }
            ],
            buttons: [
                { text: 'Cancelar', role: 'cancel', cssClass: 'secondary' },
                {
                    text: 'Crear',
                    handler: (data: any) => {
                        const name = data.taxName?.trim();
                        const percentage = parseFloat(data.taxPercentage);
                        if (name && !isNaN(percentage) && percentage >= 0 && percentage <= 100) {
                            onConfirm(name, percentage);
                            return true;
                        }
                        return false;
                    }
                }
            ]
        });
        await alert.present();
    }
}