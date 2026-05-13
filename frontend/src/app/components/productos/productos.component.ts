    import {
    Component,
    Input,
    OnInit,
    OnDestroy,
    ChangeDetectorRef,
    } from '@angular/core';
    import { CommonModule } from '@angular/common';
    import { FormsModule } from '@angular/forms';
    import { addIcons } from 'ionicons';
    import {
    searchOutline,
    closeOutline,
    createOutline,
    trashOutline,
    albumsOutline,
    gridOutline,
    restaurantOutline,
    } from 'ionicons/icons';
    import { Subscription } from 'rxjs';
    import {
    ProductCreateForm,
    createEmptyProductForm,
    } from './forms/product-create.form';
    import { ProductEditForm } from './forms/product-edit.form';
    import { validateNewProductForm } from './validators/product.validator';
    import { ProductLoaderService } from './services/product-loader.service';
    import { ProductStateService } from './services/product-state.service';
    import { ProductPanelService } from './services/product-panel.service';
    import { ProductAlertService } from './services/product-alert.service';
    import { ProductFilterSidebarComponent } from './product-filter-sidebar/product-filter-sidebar.component';
    import { ProductFormComponent } from './product-form/product-form.component';
    import { ProductService, Product } from '../../services/api/product.service';
    import { Family } from '../../services/api/family.service';
    import { Tax } from '../../services/api/tax.service';
    import { DataCacheService } from '../../services/shared/data-cache.service';
    import { AuthService } from '../../services/auth/auth.service';

    import { ProductListComponent } from './product-list/product-list.component';

    @Component({
    selector: 'app-productos',
    templateUrl: './productos.component.html',
    styleUrls: ['./productos.component.scss'],
    standalone: true,
    imports: [
        CommonModule,
        FormsModule,
        ProductFilterSidebarComponent,
        ProductFormComponent,
        ProductListComponent,
    ],
    })
    export class ProductosComponent implements OnInit, OnDestroy {
    @Input() set active(value: boolean) {
        this._active = value;
    }
    get active(): boolean {
        return this._active;
    }
    private _active: boolean = false;

    @Input() familias: Family[] = [];

    private familyStateSubscription: Subscription | null = null;
    private familyDeletedSubscription: Subscription | null = null;
    private familiesCacheSubscription: Subscription | null = null;

    productosLoading = false;
    productosCargados = false;
    isSavingProduct = false;

    productPanelMode: 'edit' | 'create' = 'create';
    editingProduct: Product | null = null;

    editProductForm: ProductEditForm = {
        name: '',
        family_id: '',
        tax_id: '',
        price: 0,
        stock: 0,
        image_src: '',
    };

    createProductForm: ProductCreateForm = createEmptyProductForm();
    products: Product[] = [];
    productosFiltrados: Product[] = [];
    taxes: Tax[] = [];
    familiasParaProductos: Family[] = [];
    terminoBusquedaProduct = '';
    filtroActualProduct: 'nombre' | 'id' = 'nombre';
    familiaSeleccionadaFiltro: string | null = null;

    constructor(
        private productService: ProductService,
        private dataCacheService: DataCacheService,
        private authService: AuthService,
        private cd: ChangeDetectorRef,
        private productPanelService: ProductPanelService,
        private productLoaderService: ProductLoaderService,
        private productStateService: ProductStateService,
        private productAlertService: ProductAlertService,
    ) {
        addIcons({
        searchOutline,
        closeOutline,
        createOutline,
        trashOutline,
        albumsOutline,
        gridOutline,
        restaurantOutline,
        });
    }

    ngOnInit() {
        const cachedProducts = this.dataCacheService.getProducts();
        const cachedFamilies = this.dataCacheService.getFamilies();
        const cachedTaxes = this.dataCacheService.getTaxes();

        if (cachedProducts.length > 0) {
        this.products = [...cachedProducts];
        this.productosFiltrados = [...this.products];
        this.productosCargados = true;
        }

        if (cachedFamilies.length > 0) {this.familiasParaProductos = [...cachedFamilies];}

        if (cachedTaxes.length > 0) {this.taxes = [...cachedTaxes];}

        if (cachedProducts.length === 0 || cachedFamilies.length === 0 || cachedTaxes.length === 0) {
        this.cargarProductos();
        }

        this.familiesCacheSubscription =
        this.dataCacheService.getFamiliesCache$.subscribe((families) => {
            this.familiasParaProductos = [...families];
            this.cd.detectChanges();
        });

        this.suscribirseACambiosFamilia();
    }

    ngOnDestroy() {
        this.familyStateSubscription?.unsubscribe();
        this.familyDeletedSubscription?.unsubscribe();
        this.familiesCacheSubscription?.unsubscribe();
    }

    private suscribirseACambiosFamilia() {
        const subs = this.productStateService.suscribirseACambiosFamilia({
        onFamilyStatusChange: (familyId, active) => {
            const result =
            this.productStateService.actualizarEstadoProductosPorFamilia(
                familyId,
                active,
                this.products,
                this.productosFiltrados,
            );
            this.products = result.products;
            this.productosFiltrados = result.productosFiltrados;
        },
        onFamilyDeleted: (familyId) => {
            const result =
            this.productStateService.eliminarProductosPorFamiliaEliminada(
                familyId,
                this.products,
                this.productosFiltrados,
            );
            this.products = result.products;
            this.productosFiltrados = result.productosFiltrados;
        },
        onFamilyCreated: () => {},
        onTaxCreated: () => {},
        onError: (message) => alert(message),
        });

        this.familyStateSubscription = subs[0];
        this.familyDeletedSubscription = subs[1];
    }

    cargarProductos() {
        this.productosLoading = true;

        this.productLoaderService.cargarProductos(
        this.familiasParaProductos,
        this.taxes,
        (result) => {
            this.products = result.products;
            this.familiasParaProductos = result.families;
            this.taxes = result.taxes;
            this.productosFiltrados = [...this.products];
            this.productosCargados = true;
            this.productosLoading = false;
        },
        () => {
            this.products = [];
            this.productosFiltrados = [];
            this.productosCargados = false;
            this.productosLoading = false;
        },
        );
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
            case 'nombre':
            default:
            return product.name.toLowerCase().includes(termino);
        }
        });
    }

    filtrarPorTipoProduct(tipo: string) {
        if (tipo === 'nombre' || tipo === 'id') {
        this.filtroActualProduct = tipo;
        this.buscarProductos();
        }
    }

    limpiarBusquedaProduct() {
        this.terminoBusquedaProduct = '';
        this.familiaSeleccionadaFiltro = null;
        this.productosFiltrados = [...this.products];
    }

    filtrarPorFamilia(familyId: string | null) {
        this.familiaSeleccionadaFiltro = familyId;
        this.productosFiltrados =
        familyId === null
            ? [...this.products]
            : this.products.filter((p) => p.family_id?.toString() === familyId);
        this.terminoBusquedaProduct = '';
    }

    contarProductosPorFamilia(familyId: string): number {
        return this.products.filter((p) => p.family_id?.toString() === familyId)
        .length;
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

    editarProduct(product: Product) {
        this.abrirEdicionProduct(product);
    }

    guardarProductoPanel() {
        if (this.isSavingProduct) return;
        if (this.productPanelMode === 'edit') {
        this.guardarEdicionProduct();
        return;
        }
        if (this.productPanelMode === 'create') {
        this.guardarNuevoProduct();
        }
    }

    async guardarEdicionProduct() {
        if (this.editingProduct === null) return;

        if (
        !this.editProductForm.name.trim() || !this.editProductForm.family_id ||!this.editProductForm.tax_id) {
        alert('Faltan campos obligatorios');
        return;
        }

        this.isSavingProduct = true;

        const payload: any = {
        name: this.editProductForm.name.trim(),
        family_id: this.editProductForm.family_id || '',
        tax_id: this.editProductForm.tax_id,
        price: Math.round(Number(this.editProductForm.price) * 100),
        stock: Number(this.editProductForm.stock),
        image_src: this.editProductForm.image_src.trim() || null,
        };

        this.productService
        .updateProduct(this.editingProduct.id.toString(), payload)
        .subscribe({
            next: (response: any) => {
            const productIndex = this.products.findIndex(
                (p) => p.id?.toString() === this.editingProduct?.id?.toString(),
            );

            if (productIndex >= 0) {
                this.products[productIndex] = {
                ...this.products[productIndex],
                name: response?.name ?? this.products[productIndex].name,
                family_id:
                    response?.family_id ?? this.products[productIndex].family_id,
                tax_id: response?.tax_id ?? this.products[productIndex].tax_id,
                price: response?.price ?? this.products[productIndex].price,
                stock: response?.stock ?? this.products[productIndex].stock,
                image_src:
                    response?.image_src ?? this.products[productIndex].image_src,
                updated_at:
                    response?.updated_at ?? this.products[productIndex].updated_at,
                };
                this.productosFiltrados = [...this.products];
                this.productService.invalidateProductsCache();
            }

            this.productAlertService.mostrarConfirmacionGuardadoProduct();
            this.salirEdicionProduct();
            this.isSavingProduct = false;
            },
            error: (error) => {
            console.error('Error al actualizar:', error);
            this.productAlertService.mostrarErrorGuardadoProduct();
            this.isSavingProduct = false;
            },
        });
    }

    guardarNuevoProduct() {
        const restaurantId = this.authService.getUserData()?.restaurant_id;

        if (!restaurantId) {
        console.error(
            'No se pudo obtener el restaurant_id del usuario autenticado',
        );
        return;
        }

        const result = validateNewProductForm(
        this.createProductForm,
        this.familiasParaProductos,
        this.taxes,
        );

        if (!result.valid) {
        alert(result.error!);
        return;
        }

        this.isSavingProduct = true;
        const payload = this.productPanelService.buildCreatePayload(
        this.createProductForm,
        Number(restaurantId),
        );

        this.productService.createProduct(payload).subscribe({
        next: (response: any) => {
            const createdProduct = this.productPanelService.mapResponseToProduct(
            response,
            this.createProductForm,
            restaurantId,
            );

            this.productService.invalidateProductsCache();
            this.products = [createdProduct, ...this.products];
            this.productosFiltrados = this.terminoBusquedaProduct
            ? this.products.filter((p) =>
                p.name
                    .toLowerCase()
                    .includes(this.terminoBusquedaProduct.toLowerCase()),
                )
            : [...this.products];

            this.createProductForm = createEmptyProductForm();
            this.productAlertService.mostrarConfirmacionGuardadoProduct();
            this.isSavingProduct = false;
        },
        error: (error) => {
            console.error('Error al crear:', error);
            this.productAlertService.mostrarErrorGuardadoProduct();
            this.isSavingProduct = false;
        },
        });
    }

    eliminarProduct(id: string | number) {
        this.productService.deleteProduct(id.toString()).subscribe({
        next: () => {
            this.products = this.products.filter(
            (p) => p.id?.toString() !== id.toString(),
            );
            this.productosFiltrados = this.productosFiltrados.filter(
            (p) => p.id?.toString() !== id.toString(),
            );
            this.dataCacheService.setProductsCache(this.products);
            this.productService.invalidateProductsCache();
            this.cd.detectChanges();
            this.productAlertService.mostrarConfirmacionEliminadoProduct();
        },
        error: (error) => {
            console.error('Error al eliminar:', error);
        },
        });
    }

    confirmarEliminarProduct(product: Product) {
        this.productAlertService.confirmarEliminarProduct(product, (id) =>
        this.eliminarProduct(id),
        );
    }

    cambiarEstadoProduct(product: Product) {
        if (!this.isFamilyActive(product.family_id)) {
        alert(
            'No puedes activar este producto porque su familia está desactivada',
        );
        return;
        }

        this.productService
        .updateProduct(product.id.toString(), { active: !product.active })
        .subscribe({
            next: (response: any) => {
            const productIndex = this.products.findIndex(
                (p) => p.id?.toString() === product.id?.toString(),
            );
            if (productIndex >= 0) {
                this.products[productIndex].active =
                response?.active ?? !this.products[productIndex].active;
                this.productosFiltrados = [...this.products];
            }
            },
            error: (error) => {
            console.error('Error al cambiar estado:', error);
            },
        });
    }

    isFamilyActive(familyId: string): boolean {
        const family = this.familiasParaProductos.find(
        (f) => f.id?.toString() === familyId?.toString(),
        );
        return family ? family.active : true;
    }

    isProductDisabledByFamily(product: Product): boolean {
        return !this.isFamilyActive(product.family_id);
    }

    obtenerNombreFamilia(familyId: string): string {
        if (!familyId) return 'Sin familia';
        return (
        this.familiasParaProductos.find((f) => f.id?.toString() === familyId)
            ?.name ?? `Familia ${familyId}`
        );
    }

    obtenerNombreImpuesto(taxId: number | string): string {
        if (!taxId) return 'Sin impuesto';
        const id = taxId.toString();
        return (
        this.taxes.find(
            (t) => t.id?.toString() === id || t.uuid?.toString() === id,
        )?.name ?? `Impuesto ${taxId}`
        );
    }

    formatearPrecio(cents: number): string {
        return (cents / 100).toFixed(2) + '€';
    }

    crearFamiliaRapida(name: string) {
        this.productStateService.crearFamiliaRapida(
        name,
        (family) => {
            this.familiasParaProductos = [...this.familiasParaProductos, family];
            this.createProductForm.family_id = family.id?.toString();
            this.productAlertService.mostrarConfirmacionFamiliaCreada(name);
        },
        () => alert('Error al crear la familia'),
        );
    }

    crearImpuestoRapido(name: string, percentage: number) {
        this.productStateService.crearImpuestoRapido(
        name,
        percentage,
        (tax) => {
            this.taxes = [...this.taxes, tax];
            this.createProductForm.tax_id = tax.id.toString();
            this.dataCacheService.setTaxesCache(this.taxes);
            this.productAlertService.mostrarConfirmacionImpuestoCreado(name);
        },
        () => alert('Error al crear el impuesto'),
        );
    }

    manejarCambioFamilia(value: string) {
        if (value === '__create__') {
        this.createProductForm.family_id = '';
        this.productAlertService.abrirCrearFamilia((name) =>
            this.crearFamiliaRapida(name),
        );
        }
    }

    manejarCambioImpuesto(value: string) {
        if (value === '__create__') {
        this.createProductForm.tax_id = '';
        this.productAlertService.abrirCrearImpuesto((name, pct) =>
            this.crearImpuestoRapido(name, pct),
        );
        }
    }
}
