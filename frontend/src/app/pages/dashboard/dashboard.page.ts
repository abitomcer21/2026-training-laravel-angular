// src/app/pages/dashboard/dashboard.page.ts
import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common'; 
import { FormsModule } from '@angular/forms';
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

import { UserService, User } from '../../services/api/user.service';
import { FamilyService, Family } from '../../services/api/family.service';
import { ProductService, Product } from '../../services/api/product.service';
import { TaxService, Tax } from '../../services/api/tax.service';
import { AuthService } from '../../services/auth/auth.service';

interface MenuItem {
  nombre: string;
  valor: string;
  icono: string;
}

interface DashboardMetric {
  label: string;
  value: number;
  description: string;
  icon: string;
  accent: 'blue' | 'amber' | 'emerald';
}

interface UserEditForm {
  name: string;
  email: string;
  pin: string;
  image_src: string;
  role: string;
}

interface UserCreateForm {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  role: string;
  pin: string;
  image_src: string;
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
  family_id: number;
  tax_id: number;
  price: number;
  stock: number;
  image_src: string;
}

interface ProductCreateForm {
  name: string;
  family_id: number;
  tax_id: number;
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
    IonItem, IonInput, IonChip, IonBadge
  ]
})
export class DashboardPage implements OnInit {
  opcionSeleccionada: string = 'usuarios';
  
  // Loading indicators por sección
  usuariosLoading: boolean = false;
  familiasLoading: boolean = false;
  productosLoading: boolean = false;
  impuestosLoading: boolean = false;
  
  usuariosCargados: boolean = false;
  panelMode: 'edit' | 'create' = 'create';
  editingUser: User | null = null;
  editForm: UserEditForm = {
    name: '',
    email: '',
    pin: '',
    image_src: '',
    role: '',
  };
  createForm: UserCreateForm = {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'camarero',
    pin: '',
    image_src: '',
  };

  // Datos
  users: User[] = [];
  usuariosFiltrados: User[] = [];

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
  editingProduct: Product | null = null;
  editProductForm: ProductEditForm = {
    name: '',
    family_id: 0,
    tax_id: 0,
    price: 0,
    stock: 0,
    image_src: '',
  };
  createProductForm: ProductCreateForm = {
    name: '',
    family_id: 0,
    tax_id: 0,
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

  // Búsqueda
  terminoBusqueda: string = '';
  filtroActual: string = 'nombre';
  terminoBusquedaFamily: string = '';
  filtroActualFamily: string = 'nombre';
  terminoBusquedaProduct: string = '';
  filtroActualProduct: string = 'nombre';
  terminoBusquedaTax: string = '';
  filtroActualTax: string = 'nombre';

  menuItems: MenuItem[] = [
    { nombre: 'Usuarios', valor: 'usuarios', icono: 'people-outline' },
    { nombre: 'Familias', valor: 'familias', icono: 'albums-outline' },
    { nombre: 'Productos', valor: 'productos', icono: 'restaurant-outline' },
    { nombre: 'Impuestos', valor: 'impuestos', icono: 'cash-outline' },
    { nombre: 'Zonas', valor: 'zonas', icono: 'map-outline' },
    { nombre: 'Mesas', valor: 'mesas', icono: 'grid-outline' }
  ];

  constructor(
    private userService: UserService,
    private familyService: FamilyService,
    private productService: ProductService,
    private taxService: TaxService,
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

  get dashboardMetrics(): DashboardMetric[] {
    return [
      {
        label: 'Camareros',
        value: this.contarPorRoles(['camarero']),
        description: 'Personal de sala activo',
        icon: 'people-outline',
        accent: 'blue',
      },
      {
        label: 'Chefs',
        value: this.contarPorRoles(['chef']),
        description: 'Equipo de cocina',
        icon: 'restaurant-outline',
        accent: 'amber',
      },
      {
        label: 'Supervisores',
        value: this.contarPorRoles(['supervisor']),
        description: 'Responsables de turno',
        icon: 'shield-checkmark-outline',
        accent: 'emerald',
      },
    ];
  }

  ngOnInit() {
    this.createForm = this.createEmptyUserForm();
    this.createFamilyForm = this.creatEmptyFamilyForm();
    this.createProductForm = this.creatEmptyProductForm();
    
    // Cargar todos los datos del restaurante automáticamente
    this.cargarUsuarios();
    this.cargarFamilias();
    this.cargarProductos();
    this.cargarImpuestos();
  }

  seleccionarOpcion(valor: string) {
    this.opcionSeleccionada = valor;
    if (valor === 'usuarios' && !this.usuariosCargados) {
      this.cargarUsuarios();
    }
    if (valor === 'familias' && !this.familiasCargadas) {
      this.cargarFamilias();
    }
    if (valor === 'productos' && !this.productosCargados) {
      this.cargarProductos();
    }
    if (valor === 'impuestos' && !this.impuestoCargados) {
      this.cargarImpuestos();
    }
  }

  cargarUsuarios() {
    this.usuariosLoading = true;
    // Obtener el restaurant_id del usuario logueado
    const userData = this.authService.getUserData();
    const userRestaurantId = userData?.restaurant_id;

    this.userService.getUsers().subscribe({
      next: (response: any) => {
        let users: any[] = [];
        if (Array.isArray(response)) {
          users = response;
        } else if (response?.users && Array.isArray(response.users)) {
          users = response.users;
        } else if (response?.data?.users && Array.isArray(response.data.users)) {
          users = response.data.users;
        } else {
          users = [];
        }

        // Filtrar por restaurant_id del usuario logueado
        if (userRestaurantId) {
          this.users = users.filter(user => user.restaurant_id === userRestaurantId);
        } else {
          this.users = users;
        }

        this.usuariosFiltrados = [...this.users];
        this.usuariosCargados = true;
        this.usuariosLoading = false;
      },
      error: (error) => {
        console.error('Error:', error);
        this.users = [];
        this.usuariosFiltrados = [];
        this.usuariosCargados = false;
        this.usuariosLoading = false;
      }
    });
  }
  
  buscarUsuarios() {
    if (!this.terminoBusqueda) {
      this.usuariosFiltrados = [...this.users];
      return;
    }

    const termino = this.terminoBusqueda.toLowerCase();

    this.usuariosFiltrados = this.users.filter(user => {
      switch (this.filtroActual) {
        case 'email':
          return user.email.toLowerCase().includes(termino);
        case 'id':
          return user.id.toString().includes(termino);
        case 'nombre':
        default:
          return user.name.toLowerCase().includes(termino);
      }
    });
  }

  filtrarPorTipo(tipo: string) {
    this.filtroActual = tipo;
    this.buscarUsuarios();
  }

  contarPorRoles(roles: string[]): number {
    return this.users.filter(user => roles.includes(user.role.toLowerCase())).length;
  }

  limpiarBusqueda() {
    this.terminoBusqueda = '';
    this.usuariosFiltrados = [...this.users];
  }

  abrirEdicion(user: User) {
    this.panelMode = 'edit';
    this.editingUser = user;
    this.editForm = {
      name: user.name,
      email: user.email,
      pin: user.pin,
      image_src: user.image_src ?? '',
      role: user.role,
    };
  }

  salirEdicion() {
    this.panelMode = 'create';
    this.editingUser = null;
    this.editForm = {
      name: '',
      email: '',
      pin: '',
      image_src: '',
      role: '',
    };
  }

  createEmptyUserForm(): UserCreateForm {
    return {
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      role: 'camarero',
      pin: '',
      image_src: '',
    };
  }

  getDefaultRestaurantId(): string {
    const userData = this.authService.getUserData();
    const restaurantId = userData?.restaurant_id ?? this.users[0]?.restaurant_id ?? 1;

    return String(restaurantId);
  }

  async confirmarEliminar(user: User) {
    const alert = await this.alertController.create({
      header: 'Eliminar usuario',
      message: `¿Estás seguro de que quieres eliminar a <strong>${user.name}</strong>?`,
      buttons: [
        {
          text: 'Cancelar',
          role: 'cancel',
          cssClass: 'secondary'
        },
        {
          text: 'Eliminar',
          handler: () => {
            this.eliminarUsuario(user.uuid);
          }
        }
      ]
    });
    await alert.present();
  }

  eliminarUsuario(uuid: string) {
    this.userService.deleteUser(uuid).subscribe({
      next: () => {
        this.cargarUsuarios();
      },
      error: (error) => {
        console.error('Error al eliminar:', error);
      }
    });
  }

  editarUsuario(user: User) {
    this.abrirEdicion(user);
  }

  guardarPanel() {
    if (this.panelMode === 'edit') {
      this.guardarEdicion();
      return;
    }

    if (this.panelMode === 'create') {
      this.guardarNuevoUsuario();
    }
  }

  async guardarEdicion() {
    if (this.editingUser === null) {
      return;
    }

    const payload: any = {
      name: this.editForm.name.trim() || null,
      email: this.editForm.email.trim() || null,
      pin: this.editForm.pin.trim() || null,
      image_src: this.editForm.image_src.trim() || null,
      role: this.editForm.role,
    };

    this.userService.updateUser(this.editingUser.uuid, payload).subscribe({
      next: () => {
        // Mostrar confirmación INMEDIATAMENTE
        this.mostrarConfirmacionGuardado();

        // Luego actualizar la tabla en segundo plano
        this.users = this.users.map((user) => ({
          ...user,
          ...(user.uuid === this.editingUser?.uuid
            ? {
                name: this.editForm.name.trim(),
                email: this.editForm.email.trim(),
                pin: this.editForm.pin.trim(),
                image_src: this.editForm.image_src.trim() || null,
                role: this.editForm.role,
              }
            : {}),
        }));

        if (this.terminoBusqueda) {
          this.buscarUsuarios();
        } else {
          this.usuariosFiltrados = [...this.users];
        }

        this.salirEdicion();
      },
      error: (error) => {
        console.error('Error al actualizar:', error);
        this.mostrarErrorGuardado();
      }
    });
  }

  async mostrarConfirmacionGuardado() {
    const alert = await this.alertController.create({
      header: 'Cambios guardados',
      message: 'El usuario ha sido actualizado correctamente.',
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

  async mostrarErrorGuardado() {
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

  guardarNuevoUsuario() {
    // Obtener restaurant_id del usuario autenticado
    const userData = this.authService.getUserData();
    const restaurantId = userData?.restaurant_id;

    if (!restaurantId) {
      console.error('No se pudo obtener el restaurant_id del usuario autenticado');  
      return;
    }

    if (
      !this.createForm.name.trim() ||
      !this.createForm.email.trim() ||
      !this.createForm.password ||
      !this.createForm.password_confirmation ||
      !this.createForm.pin.trim() ||
      !this.createForm.role
    ) {
      console.error('Faltan campos obligatorios para crear el usuario');
      return;
    }

    if (this.createForm.password !== this.createForm.password_confirmation) {
      console.error('Las contraseñas no coinciden');
      return;
    }

    const payload: any = {
      name: this.createForm.name.trim(),
      email: this.createForm.email.trim(),
      password: this.createForm.password,
      password_confirmation: this.createForm.password_confirmation,
      role: this.createForm.role,
      pin: this.createForm.pin.trim(),
      image_src: this.createForm.image_src.trim() || null,
      restaurant_id: restaurantId,
    };

    this.userService.createUser(payload).subscribe({
      next: (response: any) => {
        const createdUser: User = {
          id: this.getNextNumericUserId(),
          uuid: response?.id ?? response?.uuid,
          name: response?.name ?? this.createForm.name.trim(),
          email: response?.email ?? this.createForm.email.trim(),
          role: response?.role ?? this.createForm.role,
          pin: response?.pin ?? this.createForm.pin.trim(),
          image_src: response?.image_src ?? (this.createForm.image_src.trim() || null),
          restaurant_id: response?.restaurant_id ?? restaurantId,
        };

        this.users = [...this.users, createdUser];

        if (this.terminoBusqueda) {
          this.buscarUsuarios();
        } else {
          this.usuariosFiltrados = [...this.users];
        }
        this.createForm = this.createEmptyUserForm();
      },
      error: (error) => {
        console.error('Error al crear usuario:', error);
      }
    });
  }

  getNextNumericUserId(): number {
    if (this.users.length === 0) {
      return 1;
    }

    return Math.max(...this.users.map((user) => user.id)) + 1;
  }

  // ========== MÉTODOS DE FAMILIAS ==========

  cargarFamilias() {
    this.familiasLoading = true;
    const userData = this.authService.getUserData();
    const userRestaurantId = userData?.restaurant_id;

    console.log('Cargando familias. Restaurant ID:', userRestaurantId);

    this.familyService.getFamilies().subscribe({
      next: (response: any) => {
        console.log('Respuesta de getFamilies():', response);
        let families: any[] = [];
        if (Array.isArray(response)) {
          families = response;
          console.log('Detectado: response es un array');
        } else if (response?.family && Array.isArray(response.family)) {
          families = response.family;
          console.log('Detectado: response.family es un array');
        } else if (response?.Family && Array.isArray(response.Family)) {
          families = response.Family;
          console.log('Detectado: response.Family es un array');
        } else if (response?.data && Array.isArray(response.data)) {
          families = response.data;
          console.log('Detectado: response.data es un array');
        } else {
          families = [];
          console.warn('No se pudo extraer array de familias');
        }

        console.log('Familias antes de filtrar:', families);

        if (userRestaurantId) {
          this.families = families.filter(family => family.restaurant_id === userRestaurantId);
          console.log('Familias después de filtrar por restaurant_id:', this.families);
        } else {
          this.families = families;
        }

        this.familiasFiltradas = [...this.families];
        this.familiasParaProductos = [...this.families];
        this.familiasCargadas = true;
        this.familiasLoading = false;
        console.log('familiasParaProductos actualizado:', this.familiasParaProductos);
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
          uuid: response?.id ?? response?.uuid,
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

    // Cargar familias y impuestos también si no están cargados
    if (this.familiasParaProductos.length === 0) {
      this.familyService.getFamilies().subscribe({
        next: (response: any) => {
          let families: any[] = [];
          if (Array.isArray(response)) {
            families = response;
          } else if (response?.Family && Array.isArray(response.Family)) {
            families = response.Family;
          } else if (response?.data && Array.isArray(response.data)) {
            families = response.data;
          }
          if (userRestaurantId) {
            this.familiasParaProductos = families.filter(f => f.restaurant_id === userRestaurantId);
          } else {
            this.familiasParaProductos = families;
          }
        }
      });
    }

    if (this.taxes.length === 0) {
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
          }
          if (userRestaurantId) {
            this.taxes = taxes.filter(t => t.restaurant_id === userRestaurantId);
          } else {
            this.taxes = taxes;
          }
        }
      });
    }

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
          const family = this.familiasParaProductos.find(f => f.id === product.family_id || f.id?.toString() === product.family_id?.toString());
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
    this.productosFiltrados = [...this.products];
  }

  abrirEdicionProduct(product: Product) {
    this.productPanelMode = 'edit';
    this.editingProduct = product;
    this.editProductForm = {
      name: product.name,
      family_id: product.family_id,
      tax_id: product.tax_id,
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
      family_id: 0,
      tax_id: 0,
      price: 0,
      stock: 0,
      image_src: '',
    };
  }

  creatEmptyProductForm(): ProductCreateForm {
    return {
      name: '',
      family_id: 0,
      tax_id: 0,
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
      console.error('Faltan campos obligatorios');
      return;
    }

    const payload: any = {
      name: this.editProductForm.name.trim(),
      family_id: this.editProductForm.family_id?.toString() || '',
      tax_id: this.editProductForm.tax_id?.toString() || '',
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

    console.log('=== INICIO: guardarNuevoProduct ===');
    console.log('Restaurant ID del usuario:', restaurantId);
    console.log('Form data:', this.createProductForm);
    console.log('Familias disponibles (familiasParaProductos):', this.familiasParaProductos);
    console.log('Impuestos disponibles (taxes):', this.taxes);

    if (!restaurantId) {
      console.error('No se pudo obtener el restaurant_id del usuario autenticado');
      return;
    }

    // Verify that families and taxes are loaded
    if (!this.familiasParaProductos || this.familiasParaProductos.length === 0) {
      console.error('No hay familias disponibles. Por favor, crea al menos una familia primero.');
      alert('Debes crear al menos una familia antes de crear productos. Ve a la sección de Familias.');
      return;
    }

    if (!this.taxes || this.taxes.length === 0) {
      console.error('No hay impuestos disponibles. Por favor, crea al menos un impuesto primero.');
      alert('Debes crear al menos un impuesto antes de crear productos. Ve a la sección de Impuestos.');
      return;
    }

    // family_id y tax_id son UUIDs (strings), no números
    const familyId = this.createProductForm.family_id?.toString() || '';
    const taxId = this.createProductForm.tax_id?.toString() || '';
    const priceNum = Number(this.createProductForm.price);
    const stockNum = Number(this.createProductForm.stock);

    console.log('IDs extraídos:', { familyId, taxId });

    // Validate required fields and their numeric values
    if (!this.createProductForm.name.trim()) {
      console.error('El nombre del producto es obligatorio');
      return;
    }

    if (!familyId || familyId.trim() === '') {
      console.error('Debes seleccionar una familia válida', { familyId });
      return;
    }

    if (!taxId || taxId.trim() === '') {
      console.error('Debes seleccionar un impuesto válido', { taxId });
      return;
    }

    // Verify that selected family and tax actually exist in the loaded data
    const selectedFamily = this.familiasParaProductos.find(f => f.id === familyId);
    console.log('Buscando familia con ID', familyId, 'en lista:', this.familiasParaProductos);
    console.log('Familia encontrada:', selectedFamily);
    
    if (!selectedFamily) {
      console.error(`La familia con ID ${familyId} no existe en la lista disponible`);
      alert(`Error: La familia seleccionada no es válida. Las familias disponibles son: ${this.familiasParaProductos.map(f => `${f.name} (${f.id})`).join(', ')}`);
      return;
    }

    const selectedTax = this.taxes.find(t => t.id === taxId);
    console.log('Buscando impuesto con ID', taxId, 'en lista:', this.taxes);
    console.log('Impuesto encontrado:', selectedTax);
    
    if (!selectedTax) {
      console.error(`El impuesto con ID ${taxId} no existe`);
      alert(`Error: El impuesto seleccionado no es válido. Por favor, selecciona un impuesto de la lista.`);
      return;
    }

    if (priceNum < 0 || isNaN(priceNum)) {
      console.error('El precio debe ser un número válido y mayor o igual a 0');
      return;
    }

    if (stockNum < 0 || isNaN(stockNum)) {
      console.error('El stock debe ser un número válido y mayor o igual a 0');
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

    console.log('Payload enviado:', payload);
    console.log('Tipos:', {
      family_id_type: typeof payload.family_id,
      family_id_value: payload.family_id,
      tax_id_type: typeof payload.tax_id,
      tax_id_value: payload.tax_id,
      price_type: typeof payload.price,
      stock_type: typeof payload.stock,
    });

    this.productService.createProduct(payload).subscribe({
      next: (response: any) => {
        const createdProduct: Product = {
          id: response?.id ?? response?.uuid,
          uuid: response?.id ?? response?.uuid,
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

  obtenerNombreFamilia(familyId: number): string {
    const family = this.familiasParaProductos.find(f => f.id === familyId || f.id?.toString() === familyId?.toString());
    return family?.name ?? `Familia ${familyId}`;
  }

  obtenerNombreImpuesto(taxId: number): string {
    const tax = this.taxes.find(t => t.id === taxId || t.id?.toString() === taxId?.toString());
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

    console.log('Cargando impuestos. Restaurant ID:', userRestaurantId);

    this.taxService.getTaxes().subscribe({
      next: (response: any) => {
        console.log('Respuesta de getTaxes():', response);
        console.log('Tipo de respuesta:', Array.isArray(response) ? 'array' : typeof response);
        console.log('Propiedades de respuesta:', Object.keys(response || {}));

        let taxes: any[] = [];
        if (Array.isArray(response)) {
          taxes = response;
          console.log('Detectado: response es un array');
        } else if (response?.tax && Array.isArray(response.tax)) {
          taxes = response.tax;
          console.log('Detectado: response.tax es un array');
        } else if (response?.Tax && Array.isArray(response.Tax)) {
          taxes = response.Tax;
          console.log('Detectado: response.Tax es un array');
        } else if (response?.data && Array.isArray(response.data)) {
          taxes = response.data;
          console.log('Detectado: response.data es un array');
        } else {
          console.warn('No se pudo extraer array de impuestos. Response:', response);
          taxes = [];
        }

        console.log('Impuestos antes de filtrar:', taxes);

        if (userRestaurantId) {
          this.taxes = taxes.filter(t => t.restaurant_id === userRestaurantId);
          console.log('Impuestos después de filtrar por restaurant_id:', this.taxes);
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
}