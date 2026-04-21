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
  restaurant_id: string;
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
  loading: boolean = false;
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
    restaurant_id: '1',
  };

  // Datos
  users: User[] = [];
  usuariosFiltrados: User[] = [];

  // Búsqueda
  terminoBusqueda: string = '';
  filtroActual: string = 'nombre';

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
    this.cargarUsuarios();
  }

  seleccionarOpcion(valor: string) {
    this.opcionSeleccionada = valor;
    if (valor === 'usuarios' && !this.usuariosCargados) {
      this.cargarUsuarios();
    }
  }

  cargarUsuarios() {
    this.loading = true;
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
        this.loading = false;
      },
      error: (error) => {
        console.error('Error:', error);
        this.users = [];
        this.usuariosFiltrados = [];
        this.usuariosCargados = false;
        this.loading = false;
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
      restaurant_id: this.getDefaultRestaurantId(),
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
    const restaurantId = Number.parseInt(this.createForm.restaurant_id, 10);

    if (Number.isNaN(restaurantId)) {
      console.error('Restaurant ID inválido');
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

  cerrarSesion() {
    localStorage.removeItem('isLoggedIn');
    localStorage.removeItem('token');
    localStorage.removeItem('userData');
    window.location.href = '/login';
  }
}