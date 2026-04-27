import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { forkJoin } from 'rxjs';
import {
  IonHeader, IonToolbar, IonTitle, IonContent, IonButtons,
  IonButton, IonIcon, IonLabel, IonSpinner, IonAvatar,
  IonItem, IonInput, IonChip, IonBadge
} from '@ionic/angular/standalone';
import { AlertController } from '@ionic/angular';
import { addIcons } from 'ionicons';
import {
  logOutOutline, restaurantOutline, peopleOutline,
  mapOutline, cashOutline, gridOutline, albumsOutline,
  addOutline, searchOutline, closeOutline,
  createOutline, trashOutline, shieldCheckmarkOutline
} from 'ionicons/icons';

import { FamilyService, Family } from '../../services/api/family.service';
import { ProductService, Product } from '../../services/api/product.service';
import { TaxService, Tax } from '../../services/api/tax.service';
import { ZoneService, Zone } from '../../services/api/zone.service';
import { TableService, Table } from '../../services/api/table.service';
import { RestaurantService, Restaurant } from '../../services/api/restaurant.service';
import { AuthService } from '../../services/auth/auth.service';
import { UsuariosComponent } from '../../components/usuarios/usuarios.component';
interface MenuItem {
  nombre: string;
  valor: string;
  icono: string;
}

interface FamilyEditForm {
  name: string;
}

interface FamilyCreateForm {
  name: string;
  active: boolean | string;
}

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

interface TaxEditForm {
  name: string;
  percentage: number;
}

interface TaxCreateForm {
  name: string;
  percentage: number;
}

interface ZoneEditForm {
  name: string;
}

interface ZoneCreateForm {
  name: string;
}

interface TableEditForm {
  name: string;
}

interface TableCreateForm {
  name: string;
  zone_id: number | string;
}

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.page.html',
  styleUrls: ['./dashboard.page.scss'],
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    IonHeader, IonToolbar, IonTitle, IonContent, IonButtons,
    IonButton, IonIcon, IonLabel, IonSpinner, IonAvatar,
    IonItem, IonInput, IonChip, IonBadge, UsuariosComponent
  ]
})
export class DashboardPage implements OnInit {
  opcionSeleccionada: string = 'usuarios';
  restaurantName: string = 'Yurest TPV';

  // Loading indicators por sección
  familiasLoading: boolean = false;
  productosLoading: boolean = false;
  impuestosLoading: boolean = false;
  zonasLoading: boolean = false;
  mesasLoading: boolean = false;



  // Familias
  families: Family[] = [];
  familiasFiltradas: Family[] = [];
  familiasCargadas: boolean = false;
  familyPanelMode: 'edit' | 'create' = 'create';
  editingFamily: Family | null = null;
  editFamilyForm: FamilyEditForm = {
    name: '',
  };
  createFamilyForm: FamilyCreateForm = {
    name: '',
    active: true,
  };

  // Productos
  products: Product[] = [];
  productosFiltrados: Product[] = [];
  productosCargados: boolean = false;
  productPanelMode: 'edit' | 'create' = 'create';
  familiaSeleccionadaFiltro: string | null = null;
  editingProduct: Product | null = null;
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

  // Datos globales para dropdowns
  taxes: Tax[] = [];
  familiasParaProductos: Family[] = [];

  // Impuestos
  impuestoCargados: boolean = false;
  taxPanelMode: 'edit' | 'create' = 'create';
  editingTax: Tax | null = null;
  editTaxForm: TaxEditForm = {
    name: '',
    percentage: 0,
  };
  createTaxForm: TaxCreateForm = {
    name: '',
    percentage: 0,
  };
  impuestosFiltrados: Tax[] = [];

  // Zonas
  zones: Zone[] = [];
  zonasFiltradas: Zone[] = [];
  zonasCargadas: boolean = false;
  zonePanelMode: 'edit' | 'create' = 'create';
  editingZone: Zone | null = null;
  editZoneForm: ZoneEditForm = {
    name: '',
  };
  createZoneForm: ZoneCreateForm = {
    name: '',
  };

  // Mesas
  tables: Table[] = [];
  mesasFiltradas: Table[] = [];
  mesasCargadas: boolean = false;
  tablePanelMode: 'edit' | 'create' = 'create';
  zonaSeleccionadaFiltro: string | number | null = null;
  editingTable: Table | null = null;
  editTableForm: TableEditForm = {
    name: '',
  };
  createTableForm: TableCreateForm = {
    name: '',
    zone_id: '',
  };

  // Búsqueda
  terminoBusqueda: string = '';
  filtroActual: string = 'nombre';
  terminoBusquedaFamily: string = '';
  filtroActualFamily: string = 'nombre';
  terminoBusquedaProduct: string = '';
  filtroActualProduct: string = 'nombre';
  terminoBusquedaTax: string = '';
  filtroActualTax: string = 'nombre';
  terminoBusquedaZone: string = '';
  filtroActualZone: string = 'nombre';
  terminoBusquedaTable: string = '';
  filtroActualTable: string = 'nombre';

  menuItems: MenuItem[] = [
    { nombre: 'Usuarios', valor: 'usuarios', icono: 'people-outline' },
    { nombre: 'Familias', valor: 'familias', icono: 'albums-outline' },
    { nombre: 'Productos', valor: 'productos', icono: 'restaurant-outline' },
    { nombre: 'Impuestos', valor: 'impuestos', icono: 'cash-outline' },
    { nombre: 'Zonas', valor: 'zonas', icono: 'map-outline' },
    { nombre: 'Mesas', valor: 'mesas', icono: 'grid-outline' }
  ];

  constructor(
    private familyService: FamilyService,
    private productService: ProductService,
    private taxService: TaxService,
    private zoneService: ZoneService,
    private tableService: TableService,
    private restaurantService: RestaurantService,
    private authService: AuthService,
    private alertController: AlertController
  ) {
    addIcons({
      logOutOutline, restaurantOutline, peopleOutline,
      mapOutline, cashOutline, gridOutline, albumsOutline,
      addOutline, searchOutline, closeOutline,
      createOutline, trashOutline, shieldCheckmarkOutline
    });
  }


  ngOnInit() {

    this.createFamilyForm = this.creatEmptyFamilyForm();
    this.createProductForm = this.creatEmptyProductForm();

    // Cargar nombre del restaurante
    this.cargarRestaurantName();
  }

  cargarRestaurantName() {
    this.restaurantService.getMyRestaurant().subscribe({
      next: (response: any) => {
        this.restaurantName = response?.name || 'Yurest TPV';
      },
      error: (error) => {
        console.error('Error al cargar nombre del restaurante:', error);
        this.restaurantName = 'Yurest TPV';
      }
    });
  }

  seleccionarOpcion(valor: string) {
    this.opcionSeleccionada = valor;
    if (valor === 'familias' && !this.familiasCargadas) {
      this.cargarFamilias();
    }
    if (valor === 'productos' && !this.productosCargados) {
      this.cargarProductos();
    }
    if (valor === 'impuestos' && !this.impuestoCargados) {
      this.cargarImpuestos();
    }
    if (valor === 'zonas' && !this.zonasCargadas) {
      this.cargarZonas();
    }
    if (valor === 'mesas' && !this.mesasCargadas) {
      this.cargarMesas();
    }
  }



  // ========== MÉTODOS DE FAMILIAS ==========

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
          // Si no tiene database_id pero el id es numérico, usarlo como database_id
          if (!f.database_id && f.id && !isNaN(Number(f.id))) {
            return { ...f, database_id: Number(f.id) };
          }
          return f;
        });

        console.log('Familias cargadas en cargarFamilias:', families);
        if (families.length > 0) {
          console.log('Primera familia completa:', families[0]);
          console.log('Todas las propiedades de primera familia:', Object.keys(families[0]));
        } else {
          console.warn('No hay familias cargadas');
        }

        if (userRestaurantId) {
          this.families = families.filter(family => family.restaurant_id === userRestaurantId);
        } else {
          this.families = families;
        }

        this.familiasFiltradas = [...this.families];
        this.familiasParaProductos = [...this.families];
        this.familiasCargadas = true;
        this.familiasLoading = false;
      },
      error: (error) => {
        console.error('Error al cargar familias:', error);
        this.families = [];
        this.familiasFiltradas = [];
        this.familiasParaProductos = [];
        this.familiasCargadas = false;
        this.familiasLoading = false;
      }
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
          // Actualizar también la lista de familias para productos
          this.familiasParaProductos = [...this.families];
        }

        this.mostrarConfirmacionGuardadoFamily();
        this.salirEdicionFamily();
      },
      error: (error) => {
        console.error('Error al actualizar:', error);
        this.mostrarErrorGuardadoFamily();
      }
    });
  }

  guardarNuevoFamily() {
    // Obtener restaurant_id del usuario autenticado
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

    // Convertir active a booleano (el select devuelve string)
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

        // Actualizar también la lista de familias para productos
        this.familiasParaProductos = [...this.families];

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
      }
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
          cssClass: 'secondary'
        },
        {
          text: 'Eliminar',
          handler: () => {
            this.eliminarFamily(family.id);
          }
        }
      ]
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
      }
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
        // Buscar y actualizar la familia en el array
        const familyIndex = this.families.findIndex(f => f.id?.toString() === family.id?.toString());

        if (familyIndex >= 0) {
          this.families[familyIndex].active = response?.active ?? !this.families[familyIndex].active;
          this.familiasFiltradas = [...this.families];
          // Actualizar también la lista de familias para productos
          this.familiasParaProductos = [...this.families];
        }
      },
      error: (error) => {
        console.error('Error al cambiar estado:', error);
      }
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

  getNextNumericFamilyId(): number {
    if (this.families.length === 0) {
      return 1;
    }

    return Math.max(...this.families.map((family) => typeof family.id === 'number' ? family.id : 0)) + 1;
  }

  cerrarSesion() {
    localStorage.removeItem('isLoggedIn');
    localStorage.removeItem('token');
    localStorage.removeItem('userData');
    window.location.href = '/login';
  }


  // ========== MÉTODOS DE PRODUCTOS ==========

  cargarProductos() {
    this.productosLoading = true;
    const userData = this.authService.getUserData();
    const userRestaurantId = userData?.restaurant_id;

    // OPTIMIZACIÓN: Usar forkJoin para cargar dependencias en paralelo
    const requests: any = {};

    if (this.familiasParaProductos.length === 0) {
      requests.families = this.familyService.getFamilies();
    }

    if (this.taxes.length === 0) {
      requests.taxes = this.taxService.getTaxes();
    }

    // Si hay dependencias por cargar, cargarlas primero en paralelo
    if (Object.keys(requests).length > 0) {
      forkJoin(requests).subscribe({
        next: (responses: any) => {
          // Procesar familias
          if (responses.families) {
            let families: any[] = [];
            if (Array.isArray(responses.families)) {
              families = responses.families;
            } else if (responses.families?.Family && Array.isArray(responses.families.Family)) {
              families = responses.families.Family;
            } else if (responses.families?.data && Array.isArray(responses.families.data)) {
              families = responses.families.data;
            }

            // Mapear familias para asegurar que tengan database_id
            families = families.map(f => {
              // Si no tiene database_id pero el id es numérico, usarlo como database_id
              if (!f.database_id && f.id && !isNaN(Number(f.id))) {
                return { ...f, database_id: Number(f.id) };
              }
              return f;
            });

            console.log('Familias cargadas en cargarProductos (forkJoin):', families);
            console.log('Primera familia completa:', families[0]);
            console.log('Todas las propiedades de primera familia:', Object.keys(families[0] || {}));

            // Guardar en el array principal de families
            if (userRestaurantId) {
              this.families = families.filter(f => f.restaurant_id === userRestaurantId);
              this.familiasParaProductos = [...this.families];
            } else {
              this.families = families;
              this.familiasParaProductos = [...this.families];
            }
            this.familiasCargadas = true;
          }

          // Procesar taxes
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

          // Ahora cargar productos
          this.cargarProductosData(userRestaurantId);
        },
        error: (error) => {
          console.error('Error cargando dependencias:', error);
          this.cargarProductosData(userRestaurantId);
        }
      });
    } else {
      // Si ya están cargadas las dependencias, cargar productos directamente
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

        console.log('Productos cargados:', this.products);
        console.log('Primer producto family_id:', this.products[0]?.family_id);
        console.log('Primer producto completo:', this.products[0]);
        console.log('familiasParaProductos disponibles:', this.familiasParaProductos);

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
      // Mostrar todos los productos
      this.productosFiltrados = [...this.products];
    } else {
      // Filtrar por familia seleccionada
      this.productosFiltrados = this.products.filter(product => product.family_id === familyId);
    }

    // Limpiar búsqueda de texto
    this.terminoBusquedaProduct = '';
  }

  contarProductosPorFamilia(familyId: string): number {
    return this.products.filter(product => product.family_id === familyId).length;
  }

  filtrarPorZona(zoneId: string | number | null) {
    this.zonaSeleccionadaFiltro = zoneId;

    if (zoneId === null) {
      // Mostrar todas las mesas
      this.mesasFiltradas = [...this.tables];
    } else {
      // Filtrar por zona seleccionada
      const zoneIdNum = typeof zoneId === 'string' ? parseInt(zoneId, 10) : zoneId;
      this.mesasFiltradas = this.tables.filter(table => {
        const tableZoneId = typeof table.zone_id === 'string' ? parseInt(table.zone_id, 10) : table.zone_id;
        return tableZoneId === zoneIdNum;
      });
    }

    // Limpiar búsqueda de texto
    this.terminoBusquedaTable = '';
  }

  contarMesasPorZona(zoneId: string | number): number {
    const zoneIdNum = typeof zoneId === 'string' ? parseInt(zoneId, 10) : zoneId;
    return this.tables.filter(table => {
      const tableZoneId = typeof table.zone_id === 'string' ? parseInt(table.zone_id, 10) : table.zone_id;
      return tableZoneId === zoneIdNum;
    }).length;
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

    console.log('Guardando edición de producto. Payload:', payload);

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

    // Verify that families and taxes are loaded
    if (!this.familiasParaProductos || this.familiasParaProductos.length === 0) {
      alert('Debes crear al menos una familia antes de crear productos. Ve a la sección de Familias.');
      return;
    }

    if (!this.taxes || this.taxes.length === 0) {
      alert('Debes crear al menos un impuesto antes de crear productos. Ve a la sección de Impuestos.');
      return;
    }

    // family_id es UUID (string)
    const familyId = this.createProductForm.family_id || '';
    const taxId = this.createProductForm.tax_id || '';
    const priceNum = Number(this.createProductForm.price);
    const stockNum = Number(this.createProductForm.stock);

    console.log('Validando producto:', { familyId, taxId, name: this.createProductForm.name });

    // Validate required fields and their numeric values
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

    // Verify that selected family and tax actually exist in the loaded data
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

    console.log('Enviando payload:', payload);

    this.productService.createProduct(payload).subscribe({
      next: (response: any) => {
        console.log('Respuesta del servidor:', response);
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

    // Buscar en familiasParaProductos primero
    let family = this.familiasParaProductos.find(f => f.id === familyId);

    // Si no se encuentra, buscar en el array general de families
    if (!family) {
      family = this.families.find(f => f.id === familyId);
    }

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

  // ========== MÉTODOS DE IMPUESTOS ==========

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
      message: `¿Estás seguro de que quieres eliminar <strong>${tax.name}</strong>?`,
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
        this.cargarImpuestos();
      },
      error: (error) => {
        console.error('Error al eliminar:', error);
      }
    });
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

  // ========== MÉTODOS DE ZONAS ==========

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
    const alert = await this.alertController.create({
      header: 'Eliminar zona',
      message: `¿Estás seguro de que quieres eliminar <strong>${zone.name}</strong>?`,
      buttons: [
        {
          text: 'Cancelar',
          role: 'cancel',
          cssClass: 'secondary'
        },
        {
          text: 'Eliminar',
          handler: () => {
            this.eliminarZone(zone.id);
          }
        }
      ]
    });
    await alert.present();
  }

  eliminarZone(id: string | number) {
    this.zoneService.deleteZone(id.toString()).subscribe({
      next: () => {
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

  // ========== MÉTODOS DE MESAS ==========

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
      this.mesasFiltradas = [...this.tables];
      return;
    }

    const termino = this.terminoBusquedaTable.toLowerCase();

    this.mesasFiltradas = this.tables.filter((table, index) => {
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

    // El formulario puede contener database_id (número como string) o UUID
    // Buscar por ambos para encontrar la zona
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

    // El backend espera zone_id como integer (database_id)
    // Intentar usar database_id si está disponible, sino usar el id
    const zoneIdForBackend = selectedZone.database_id ?? selectedZone.id;

    if (!zoneIdForBackend) {
      alert('Error: La zona seleccionada no tiene un ID válido. Por favor, recarga la página.');
      return;
    }

    // Usar el UUID de la zona directamente (el backend acepta string o number)
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

        if (this.terminoBusquedaTable) {
          this.buscarMesas();
        } else {
          this.mesasFiltradas = [...this.tables];
        }

        this.createTableForm = this.creatEmptyTableForm();
        this.mostrarConfirmacionGuardadoTable();
      },
      error: (error) => {
        console.error('Error al crear mesa:', error);
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

  obtenerNombreZona(zoneId: number | string): string {
    if (!zoneId) {
      return 'Sin zona';
    }

    const zoneIdStr = zoneId.toString();
    const zoneIdNum = Number(zoneId);

    const zone = this.zones.find(z => {
      const zId = z.id?.toString() || '';
      const zUuid = z.uuid?.toString() || '';
      const zDbId = z.database_id;

      return (zDbId && zDbId === zoneIdNum) ||
        zId === zoneIdStr ||
        zUuid === zoneIdStr;
    });

    return zone?.name ?? `Zona ${zoneId}`;
  }
}