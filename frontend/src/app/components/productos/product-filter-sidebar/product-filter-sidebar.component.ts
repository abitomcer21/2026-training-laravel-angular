import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { IonIcon } from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import { albumsOutline, gridOutline, pricetagOutline } from 'ionicons/icons';

import { Product } from '../../../services/api/product.service';
import { Family } from '../../../services/api/family.service';


@Component({
    selector: 'app-product-filter-sidebar',
    templateUrl: './product-filter-sidebar.component.html',
    styleUrls: ['./product-filter-sidebar.component.scss'],
    standalone: true,
    imports: [CommonModule, IonIcon],
})
export class ProductFilterSidebarComponent {

    @Input() familias: Family[] = [];
    @Input() productos: Product[] = [];
    @Input() familiaSeleccionada: string | null = null;

    @Output() familiaSeleccionadaChange = new EventEmitter<string | null>();

    constructor() {
        addIcons({ albumsOutline, gridOutline, pricetagOutline });
    }

    seleccionarFamilia(familyId: string | null) {
        this.familiaSeleccionadaChange.emit(familyId);
    }

    contarProductosPorFamilia(familyId: string): number {
        return this.productos.filter(p => p.family_id?.toString() === familyId).length;
    }
}