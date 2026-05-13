import { Component, Input, Output, EventEmitter } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';

import { Product } from '../../../services/api/product.service';
import { Family } from '../../../services/api/family.service';
import { Tax } from '../../../services/api/tax.service';
import { ProductCreateForm, createEmptyProductForm } from '../forms/product-create.form';
import { ProductEditForm } from '../forms/product-edit.form';
import { obtenerNombreFamilia as obtenerNombreFamiliaUtil } from '../utils/product.utils';

@Component({
    selector: 'app-product-form',
    templateUrl: './product-form.component.html',
    styleUrls: ['./product-form.component.scss'],
    standalone: true,
    imports: [CommonModule, FormsModule],
})
export class ProductFormComponent {
    @Input() mode: 'create' | 'edit' = 'create';
    @Input() editProduct: Product | null = null;
    @Input() editFormData: ProductEditForm = {
        name: '',
        family_id: '',
        tax_id: '',
        price: 0,
        stock: 0,
        image_src: '',
    };
    @Input() createFormData: ProductCreateForm = createEmptyProductForm();
    @Input() familias: Family[] = [];
    @Input() taxes: Tax[] = [];
    @Input() isSaving = false;

    @Output() save = new EventEmitter<void>();
    @Output() cancel = new EventEmitter<void>();
    @Output() createFamily = new EventEmitter<string>();
    @Output() createTax = new EventEmitter<string>();

    // Properties that match the template
    get productPanelMode(): 'create' | 'edit' {
        return this.mode;
    }

    get editingProduct(): Product | null {
        return this.editProduct;
    }

    get editProductForm(): ProductEditForm {
        return this.editFormData;
    }

    get createProductForm(): ProductCreateForm {
        return this.createFormData;
    }

    get familiasParaProductos(): Family[] {
        return this.familias;
    }

    get isSavingProduct(): boolean {
        return this.isSaving;
    }

    obtenerNombreFamilia(familyId: string): string {
        return obtenerNombreFamiliaUtil(familyId, this.familias);
    }

    salirEdicionProduct(): void {
        this.cancel.emit();
    }

    guardarProductoPanel(): void {
        this.save.emit();
    }

    manejarCambioFamilia(value: string): void {
        if (value === '__create__') {
            this.createFamily.emit(value);
        }
    }

    manejarCambioImpuesto(value: string): void {
        if (value === '__create__') {
            this.createTax.emit(value);
        }
    }
}
