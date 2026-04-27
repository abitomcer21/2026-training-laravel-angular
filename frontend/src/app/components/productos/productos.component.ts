import { Component, Input, OnChanges, SimpleChanges } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { IonIcon } from '@ionic/angular/standalone';
import { AlertController } from '@ionic/angular';
import { addIcons } from 'ionicons';
import {
    searchOutline, closeOutline, createOutline, trashOutline,
    albumsOutline, gridOutline, restaurantOutline
} from 'ionicons/icons';
import { forkJoin } from 'rxjs';

import { ProductService, Product } from '../../services/api/product.service';
import { FamilyService, Family } from '../../services/api/family.service';
import { TaxService, Tax } from '../../services/api/tax.service';
import { AuthService } from '../../services/auth/auth.service';

interface ProductEditForm {
    name: string;
    family_id: string;
    tax_id: string;
    price: number;
    stock: number;
    image_src: string;
}

interface ProductCreateForm {
    name: string;
    family_id: string;
    tax_id: string;
    price: number;
    stock: number;
    image_src: string;
    active: boolean | string;
}

@Component({
    selector: 'app-productos',
    templateUrl: './productos.component.html',
    styleUrls: ['./productos.component.scss'],
    standalone: true,
    imports: [CommonModule, FormsModule, IonIcon],
})
export class ProductosComponent implements OnChanges {
    @Input() active = false;
    @Input() familias: Family[] = [];

    // Loading
    productosLoading = false;
    productosCargados = false;

    // Panel mode
    productPanelMode: 'edit' | 'create' = 'create';
    editingProduct: Product | null = null;

    // Formularios
    editProductForm: ProductEditForm = {
        name: '',
        family_id: '',
        tax_id: '',
        price: 0,
        stock: 0,
        image_src: '',
    };
    createProductForm: ProductCreateForm = {
        name: '',
        family_id: '',
        tax_id: '',
        price: 0,
        stock: 0,
        image_src: '',
        active: true,
    };

    // Datos
    products: Product[] = [];
    productosFiltrados: Product[] = [];
    taxes: Tax[] = [];
    familiasParaProductos: Family[] = [];

    // Búsqueda y filtros
    terminoBusquedaProduct = '';
    filtroActualProduct = 'nombre';
    familiaSeleccionadaFiltro: string | null = null;

    constructor(
        private productService: ProductService,
        private familyService: FamilyService,
        private taxService: TaxService,
        private authService: AuthService,
        private alertController: AlertController
    ) {
        addIcons({
            searchOutline, closeOutline, createOutline, trashOutline,
            albumsOutline, gridOutline, restaurantOutline
        });
    }

    ngOnChanges(changes: SimpleChanges) {
        if (changes['active']?.currentValue === true && !this.productosCargados) {
            this.cargarProductos();
        }
        if (changes['familias']?.currentValue) {
            this.familiasParaProductos = changes['familias'].currentValue;
        }
    }

    cargarProductos() {
        this.productosLoading = true;
        const userData = this.authService.getUserData();
        const userRestaurantId = userData?.restaurant_id;

        const requests: any = {};

        if (this.familiasParaProductos.length === 0) {
            requests.families = this.familyService.getFamilies();
        }

        if (this.taxes.length === 0) {
            requests.taxes = this.taxService.getTaxes();
        }

        if (Object.keys(requests).length > 0) {
            forkJoin(requests).subscribe({
                next: (responses: any) => {
                    if (responses.families) {
                        let families: any[] = [];
                        if (Array.isArray(responses.families)) {
                            families = responses.families;
                        } else if (responses.families?.Family && Array.isArray(responses.families.Family)) {
                            families = responses.families.Family;
                        } else if (responses.families?.data && Array.isArray(responses.families.data)) {
                            families = responses.families.data;
                        }

                        families = families.map(f => {
                            if (!f.database_id && f.id && !isNaN(Number(f.id))) {
                                return { ...f, database_id: Number(f.id) };
                            }
                            return f;
                        });

                        if (userRestaurantId) {
                            this.familiasParaProductos = families.filter(f => f.restaurant_id === userRestaurantId);
                        } else {
                            this.familiasParaProductos = families;
                        }
                    }

                    if (responses.taxes) {
                        let taxes: any[] = [];
                        if (Array.isArray(responses.taxes)) {
                            taxes = responses.taxes;
                        } else if (responses.taxes?.tax && Array.isArray(responses.taxes.tax)) {
                            taxes = responses.taxes.tax;
                        } else if (responses.taxes?.Tax && Array.isArray(responses.taxes.Tax)) {
                            taxes = responses.taxes.Tax;
                        } else if (responses.taxes?.data && Array.isArray(responses.taxes.data)) {
                            taxes = responses.taxes.data;
                        }
                        if (userRestaurantId) {
                            this.taxes = taxes.filter(t => t.restaurant_id === userRestaurantId);
                        } else {
                            this.taxes = taxes;
                        }
                    }

                    this.cargarProductosData(userRestaurantId);
                },
                error: (error) => {
                    console.error('Error cargando dependencias:', error);
                    this.cargarProductosData(userRestaurantId);
                }
            });
        } else {
            this.cargarProductosData(userRestaurantId);
        }
    }

    private cargarProductosData(userRestaurantId: number | undefined) {
        this.productService.getProducts().subscribe({
            next: (response: any) => {
                let products: any[] = [];
                if (Array.isArray(response)) {
                    products = response;
                } else if (response?.products && Array.isArray(response.products)) {
                    products = response.products;
                } else if (response?.data && Array.isArray(response.data)) {
                    products = response.data;
                }

                if (userRestaurantId) {
                    this.products = products.filter(p => p.restaurant_id === userRestaurantId);
                } else {
                    this.products = products;
                }

                this.productosFiltrados = [...this.products];
                this.productosCargados = true;
                this.productosLoading = false;
            },
            error: (error) => {
                console.error('Error:', error);
                this.products = [];
                this.productosFiltrados = [];
                this.productosCargados = false;
                this.productosLoading = false;
            }
        });
    }

    buscarProductos() {
        if (!this.terminoBusquedaProduct) {
            this.productosFiltrados = [...this.products];
            return;
        }

        const termino = this.terminoBusquedaProduct.toLowerCase();

        this.productosFiltrados = this.products.filter((product, index) => {
            switch (this.filtroActualProduct) {
                case 'id':
                    const displayId = (index + 1).toString();
                    return displayId.includes(termino);
                case 'familia':
                    const family = this.familiasParaProductos.find(f => f.id === product.family_id);
                    return family?.name.toLowerCase().includes(termino) || false;
                case 'nombre':
                default:
                    return product.name.toLowerCase().includes(termino);
            }
        });
    }

    filtrarPorTipoProduct(tipo: string) {
        this.filtroActualProduct = tipo;
        this.buscarProductos();
    }

    limpiarBusquedaProduct() {
        this.terminoBusquedaProduct = '';
        this.familiaSeleccionadaFiltro = null;
        this.productosFiltrados = [...this.products];
    }

    filtrarPorFamilia(familyId: string | null) {
        this.familiaSeleccionadaFiltro = familyId;

        if (familyId === null) {
            this.productosFiltrados = [...this.products];
        } else {
            this.productosFiltrados = this.products.filter(product => product.family_id === familyId);
        }

        this.terminoBusquedaProduct = '';
    }

    contarProductosPorFamilia(familyId: string): number {
        return this.products.filter(product => product.family_id === familyId).length;
    }

    abrirEdicionProduct(product: Product) {
        this.productPanelMode = 'edit';
        this.editingProduct = product;
        this.editProductForm = {
            name: product.name,
            family_id: product.family_id,
            tax_id: product.tax_id.toString(),
            price: product.price / 100,
            stock: product.stock,
            image_src: product.image_src,
        };
    }

    salirEdicionProduct() {
        this.productPanelMode = 'create';
        this.editingProduct = null;
        this.editProductForm = {
            name: '',
            family_id: '',
            tax_id: '',
            price: 0,
            stock: 0,
            image_src: '',
        };
    }

    creatEmptyProductForm(): ProductCreateForm {
        return {
            name: '',
            family_id: '',
            tax_id: '',
            price: 0,
            stock: 0,
            image_src: '',
            active: true,
        };
    }

    guardarProductoPanel() {
        if (this.productPanelMode === 'edit') {
            this.guardarEdicionProduct();
            return;
        }

        if (this.productPanelMode === 'create') {
            this.guardarNuevoProduct();
        }
    }

    async guardarEdicionProduct() {
        if (this.editingProduct === null) {
            return;
        }

        if (!this.editProductForm.name.trim() || !this.editProductForm.family_id || !this.editProductForm.tax_id) {
            alert('Faltan campos obligatorios');
            return;
        }

        const payload: any = {
            name: this.editProductForm.name.trim(),
            family_id: this.editProductForm.family_id || '',
            tax_id: this.editProductForm.tax_id,
            price: Math.round(Number(this.editProductForm.price) * 100),
            stock: Number(this.editProductForm.stock),
            image_src: this.editProductForm.image_src.trim() || null,
        };

        this.productService.updateProduct(this.editingProduct.id.toString(), payload).subscribe({
            next: (response: any) => {
                const productIndex = this.products.findIndex(p => p.id?.toString() === this.editingProduct?.id?.toString());

                if (productIndex >= 0) {
                    this.products[productIndex] = {
                        ...this.products[productIndex],
                        name: response?.name ?? this.products[productIndex].name,
                        family_id: response?.family_id ?? this.products[productIndex].family_id,
                        tax_id: response?.tax_id ?? this.products[productIndex].tax_id,
                        price: response?.price ?? this.products[productIndex].price,
                        stock: response?.stock ?? this.products[productIndex].stock,
                        image_src: response?.image_src ?? this.products[productIndex].image_src,
                        updated_at: response?.updated_at ?? this.products[productIndex].updated_at,
                    };
                    this.productosFiltrados = [...this.products];
                }

                this.mostrarConfirmacionGuardadoProduct();
                this.salirEdicionProduct();
            },
            error: (error) => {
                console.error('Error al actualizar:', error);
                this.mostrarErrorGuardadoProduct();
            }
        });
    }

    guardarNuevoProduct() {
        const userData = this.authService.getUserData();
        const restaurantId = userData?.restaurant_id;

        if (!restaurantId) {
            console.error('No se pudo obtener el restaurant_id del usuario autenticado');
            return;
        }

        if (!this.familiasParaProductos || this.familiasParaProductos.length === 0) {
            alert('Debes crear al menos una familia antes de crear productos. Ve a la sección de Familias.');
            return;
        }

        if (!this.taxes || this.taxes.length === 0) {
            alert('Debes crear al menos un impuesto antes de crear productos. Ve a la sección de Impuestos.');
            return;
        }

        const familyId = this.createProductForm.family_id || '';
        const taxId = this.createProductForm.tax_id || '';
        const priceNum = Number(this.createProductForm.price);
        const stockNum = Number(this.createProductForm.stock);

        if (!this.createProductForm.name.trim()) {
            alert('El nombre del producto es obligatorio');
            return;
        }

        if (!familyId || familyId.trim() === '') {
            alert('Debe seleccionar una familia');
            return;
        }

        if (!taxId || taxId.trim() === '') {
            alert('Debe seleccionar un impuesto');
            return;
        }

        const selectedFamily = this.familiasParaProductos.find(f => f.id === familyId);

        if (!selectedFamily) {
            alert(`Error: La familia seleccionada no es válida.`);
            return;
        }

        const selectedTax = this.taxes.find(t => t.id === taxId);

        if (!selectedTax) {
            alert(`Error: El impuesto seleccionado no es válido.`);
            return;
        }

        if (priceNum < 0 || isNaN(priceNum)) {
            return;
        }

        if (stockNum < 0 || isNaN(stockNum)) {
            return;
        }

        let activeValue = this.createProductForm.active;
        if (typeof activeValue === 'string') {
            activeValue = activeValue === 'true' || activeValue === '1';
        }

        const payload: any = {
            name: this.createProductForm.name.trim(),
            family_id: familyId,
            tax_id: taxId,
            price: Math.round(priceNum * 100),
            stock: stockNum,
            image_src: this.createProductForm.image_src.trim() || null,
            active: Boolean(activeValue),
            restaurant_id: Number(restaurantId),
        };

        this.productService.createProduct(payload).subscribe({
            next: (response: any) => {
                const createdProduct: Product = {
                    id: response?.id ?? response?.uuid,
                    name: response?.name ?? this.createProductForm.name.trim(),
                    family_id: response?.family_id ?? this.createProductForm.family_id,
                    tax_id: response?.tax_id ?? this.createProductForm.tax_id,
                    price: response?.price,
                    stock: response?.stock ?? this.createProductForm.stock,
                    image_src: response?.image_src ?? this.createProductForm.image_src.trim(),
                    active: response?.active ?? Boolean(this.createProductForm.active),
                    restaurant_id: response?.restaurant_id ?? restaurantId,
                };

                this.products = [...this.products, createdProduct];

                if (this.terminoBusquedaProduct) {
                    this.buscarProductos();
                } else {
                    this.productosFiltrados = [...this.products];
                }

                this.createProductForm = this.creatEmptyProductForm();
                this.mostrarConfirmacionGuardadoProduct();
            },
            error: (error) => {
                console.error('Error al crear:', error);
                this.mostrarErrorGuardadoProduct();
            }
        });
    }

    async confirmarEliminarProduct(product: Product) {
        const alert = await this.alertController.create({
            header: 'Eliminar producto',
            message: `¿Estás seguro de que quieres eliminar <strong>${product.name}</strong>?`,
            buttons: [
                {
                    text: 'Cancelar',
                    role: 'cancel',
                    cssClass: 'secondary'
                },
                {
                    text: 'Eliminar',
                    handler: () => {
                        this.eliminarProduct(product.id);
                    }
                }
            ]
        });
        await alert.present();
    }

    eliminarProduct(id: string | number) {
        this.productService.deleteProduct(id.toString()).subscribe({
            next: () => {
                this.cargarProductos();
            },
            error: (error) => {
                console.error('Error al eliminar:', error);
            }
        });
    }

    editarProduct(product: Product) {
        this.abrirEdicionProduct(product);
    }

    cambiarEstadoProduct(product: Product) {
        const payload = {
            active: !product.active,
        };

        this.productService.updateProduct(product.id.toString(), payload).subscribe({
            next: (response: any) => {
                const productIndex = this.products.findIndex(p => p.id?.toString() === product.id?.toString());

                if (productIndex >= 0) {
                    this.products[productIndex].active = response?.active ?? !this.products[productIndex].active;
                    this.productosFiltrados = [...this.products];
                }
            },
            error: (error) => {
                console.error('Error al cambiar estado:', error);
            }
        });
    }

    async mostrarConfirmacionGuardadoProduct() {
        const alert = await this.alertController.create({
            header: 'Cambios guardados',
            message: 'El producto ha sido actualizado correctamente.',
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

    async mostrarErrorGuardadoProduct() {
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

    obtenerNombreFamilia(familyId: string): string {
        if (!familyId) {
            return 'Sin familia';
        }

        const family = this.familiasParaProductos.find(f => f.id === familyId);

        return family?.name ?? `Familia ${familyId}`;
    }

    obtenerNombreImpuesto(taxId: number | string): string {
        if (!taxId) {
            return 'Sin impuesto';
        }

        const taxIdStr = taxId.toString();
        const tax = this.taxes.find(t => {
            const tId = t.id?.toString() || '';
            const tUuid = t.uuid?.toString() || '';
            return tId === taxIdStr || tUuid === taxIdStr;
        });

        return tax?.name ?? `Impuesto ${taxId}`;
    }

    formatearPrecio(cents: number): string {
        return (cents / 100).toFixed(2) + '€';
    }
}