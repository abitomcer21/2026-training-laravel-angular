import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { IonIcon } from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import {
    searchOutline, closeOutline, createOutline, trashOutline, restaurantOutline
} from 'ionicons/icons';

import { Product } from '../../../services/api/product.service';
import { Family } from '../../../services/api/family.service';
import { Tax } from '../../../services/api/tax.service';

@Component({
    selector: 'app-product-list',
    templateUrl: './product-list.component.html',
    styleUrls: ['./product-list.component.scss'],
    standalone: true,
    imports: [CommonModule, FormsModule, IonIcon],
})
export class ProductListComponent {

    @Input() productos: Product[] = [];
    @Input() familias: Family[] = [];
    @Input() taxes: Tax[] = [];
    @Input() loading = false;
    @Input() terminoBusqueda = '';
    @Input() filtroActual = 'nombre';

    @Output() buscar = new EventEmitter<string>();
    @Output() limpiarBusqueda = new EventEmitter<void>();
    @Output() cambiarFiltro = new EventEmitter<string>();
    @Output() editarProduct = new EventEmitter<Product>();
    @Output() eliminarProduct = new EventEmitter<Product>();
    @Output() cambiarEstado = new EventEmitter<Product>();

    constructor() {
        addIcons({ searchOutline, closeOutline, createOutline, trashOutline, restaurantOutline });
    }

    obtenerNombreFamilia(familyId: string): string {
        if (!familyId) return 'Sin familia';
        return this.familias.find(f => f.id?.toString() === familyId)?.name ?? `Familia ${familyId}`;
    }

    obtenerNombreImpuesto(taxId: number | string): string {
        if (!taxId) return 'Sin impuesto';
        const id = taxId.toString();
        return this.taxes.find(t => t.id?.toString() === id || t.uuid?.toString() === id)?.name ?? `Impuesto ${taxId}`;
    }

    formatearPrecio(cents: number): string {
        return (cents / 100).toFixed(2) + '€';
    }

    isFamilyActive(familyId: string): boolean {
        const family = this.familias.find(f => f.id?.toString() === familyId?.toString());
        return family ? family.active : true;
    }

    isProductDisabledByFamily(product: Product): boolean {
        return !this.isFamilyActive(product.family_id);
    }
}