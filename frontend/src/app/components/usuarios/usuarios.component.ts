import { Component, Input, OnChanges, SimpleChanges } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { IonIcon } from '@ionic/angular/standalone';
import { AlertController } from '@ionic/angular';
import { addIcons } from 'ionicons';
import {
    searchOutline, closeOutline, createOutline, trashOutline,
    peopleOutline, gridOutline, shieldCheckmarkOutline,
    personOutline, restaurantOutline, flameOutline,
} from 'ionicons/icons';

import { UserService, User } from '../../services/api/user.service';
import { AuthService } from '../../services/auth/auth.service';

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

@Component({
    selector: 'app-usuarios',
    templateUrl: './usuarios.component.html',
    styleUrls: ['./usuarios.component.scss'],
    standalone: true,
    imports: [CommonModule, FormsModule, IonIcon],
})
export class UsuariosComponent implements OnChanges {
    @Input() active = false;

    usuariosLoading = false;
    usuariosCargados = false;
    panelMode: 'edit' | 'create' = 'create';
    editingUser: User | null = null;

    editForm: UserEditForm = { name: '', email: '', pin: '', image_src: '', role: '' };
    createForm: UserCreateForm = { name: '', email: '', password: '', password_confirmation: '', role: 'camarero', pin: '', image_src: '' };

    users: User[] = [];
    usuariosFiltrados: User[] = [];
    rolSeleccionadoFiltro: string | null = null;
    terminoBusqueda = '';
    filtroActual = 'nombre';

    constructor(
        private userService: UserService,
        private authService: AuthService,
        private alertController: AlertController,
    ) {
        addIcons({
            searchOutline, closeOutline, createOutline, trashOutline,
            peopleOutline, gridOutline, shieldCheckmarkOutline,
            personOutline, restaurantOutline, flameOutline,
        });
    }

    ngOnChanges(changes: SimpleChanges) {
        if (changes['active']?.currentValue === true && !this.usuariosCargados) {
            this.cargarUsuarios();
        }
    }

    cargarUsuarios() {
        this.usuariosLoading = true;
        const userRestaurantId = this.authService.getUserData()?.restaurant_id;

        this.userService.getUsers().subscribe({
            next: (response: any) => {
                let users: any[] = Array.isArray(response)
                    ? response
                    : (response?.users ?? response?.data?.users ?? []);

                if (userRestaurantId) {
                    users = users.filter(u => u.restaurant_id === userRestaurantId);
                }

                this.users = users;
                this.usuariosFiltrados = [...users];
                this.usuariosCargados = true;
                this.usuariosLoading = false;
            },
            error: () => {
                this.users = [];
                this.usuariosFiltrados = [];
                this.usuariosCargados = false;
                this.usuariosLoading = false;
            },
        });
    }

    buscarUsuarios() {
        if (!this.terminoBusqueda) {
            this.aplicarFiltroRol();
            return;
        }

        const termino = this.terminoBusqueda.toLowerCase();
        const base = this.rolSeleccionadoFiltro
            ? this.users.filter(u => u.role === this.rolSeleccionadoFiltro)
            : [...this.users];

        this.usuariosFiltrados = base.filter(user => {
            switch (this.filtroActual) {
                case 'email': return user.email.toLowerCase().includes(termino);
                case 'id': return user.id.toString().includes(termino);
                default: return user.name.toLowerCase().includes(termino);
            }
        });
    }

    filtrarPorTipo(tipo: string) {
        this.filtroActual = tipo;
        this.buscarUsuarios();
    }

    filtrarPorRol(role: string | null) {
        this.rolSeleccionadoFiltro = role;
        this.terminoBusqueda = '';
        this.aplicarFiltroRol();
    }

    private aplicarFiltroRol() {
        this.usuariosFiltrados = this.rolSeleccionadoFiltro
            ? this.users.filter(u => u.role === this.rolSeleccionadoFiltro)
            : [...this.users];
    }

    contarUsuariosPorRol(role: string): number {
        return this.users.filter(u => u.role === role).length;
    }

    limpiarBusqueda() {
        this.terminoBusqueda = '';
        this.rolSeleccionadoFiltro = null;
        this.usuariosFiltrados = [...this.users];
    }

    editarUsuario(user: User) {
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
        this.editForm = { name: '', email: '', pin: '', image_src: '', role: '' };
    }

    guardarPanel() {
        this.panelMode === 'edit' ? this.guardarEdicion() : this.guardarNuevoUsuario();
    }

    private async guardarEdicion() {
        if (!this.editingUser) return;

        const payload = {
            name: this.editForm.name.trim() || null,
            email: this.editForm.email.trim() || null,
            pin: this.editForm.pin.trim() || null,
            image_src: this.editForm.image_src.trim() || null,
            role: this.editForm.role,
        };

        this.userService.updateUser(this.editingUser.uuid, payload).subscribe({
            next: () => {
                this.users = this.users.map(u =>
                    u.uuid === this.editingUser?.uuid ? {
                        ...u,
                        name: this.editForm.name.trim() || u.name,
                        email: this.editForm.email.trim() || u.email,
                        pin: this.editForm.pin.trim() || u.pin,
                        image_src: this.editForm.image_src.trim() || u.image_src,
                        role: this.editForm.role,
                    } : u,
                );
                this.usuariosFiltrados = [...this.users];
                this.salirEdicion();
                this.mostrarAlerta('Cambios guardados', 'Usuario actualizado correctamente.');
            },
            error: () => this.mostrarAlerta('Error', 'No se pudieron guardar los cambios.'),
        });
    }

    private guardarNuevoUsuario() {
        const restaurantId = this.authService.getUserData()?.restaurant_id;
        if (!restaurantId) return;

        const f = this.createForm;
        if (!f.name.trim() || !f.email.trim() || !f.password || !f.pin.trim() || !f.role) return;
        if (f.password !== f.password_confirmation) return;

        const payload = {
            name: f.name.trim(),
            email: f.email.trim(),
            password: f.password,
            password_confirmation: f.password_confirmation,
            role: f.role,
            pin: f.pin.trim(),
            image_src: f.image_src.trim() || null,
            restaurant_id: restaurantId,
        };

        this.userService.createUser(payload).subscribe({
            next: (response: any) => {
                const newUser: User = {
                    id: Math.max(0, ...this.users.map(u => u.id)) + 1,
                    uuid: response?.id ?? response?.uuid,
                    name: response?.name ?? f.name.trim(),
                    email: response?.email ?? f.email.trim(),
                    role: response?.role ?? f.role,
                    pin: response?.pin ?? f.pin.trim(),
                    image_src: response?.image_src ?? null,
                    restaurant_id: response?.restaurant_id ?? restaurantId,
                };
                this.users = [...this.users, newUser];
                this.usuariosFiltrados = [...this.users];
                this.createForm = { name: '', email: '', password: '', password_confirmation: '', role: 'camarero', pin: '', image_src: '' };
            },
            error: () => this.mostrarAlerta('Error', 'No se pudo crear el usuario.'),
        });
    }

    async confirmarEliminar(user: User) {
        const alert = await this.alertController.create({
            header: 'Eliminar usuario',
            message: `¿Eliminar a <strong>${user.name}</strong>?`,
            buttons: [
                { text: 'Cancelar', role: 'cancel' },
                { text: 'Eliminar', handler: () => this.eliminarUsuario(user.uuid) },
            ],
        });
        await alert.present();
    }

    private eliminarUsuario(uuid: string) {
        this.userService.deleteUser(uuid).subscribe({
            next: () => this.cargarUsuarios(),
            error: () => this.mostrarAlerta('Error', 'No se pudo eliminar el usuario.'),
        });
    }

    private async mostrarAlerta(header: string, message: string) {
        const alert = await this.alertController.create({
            header,
            message,
            buttons: ['Aceptar'],
        });
        await alert.present();
    }
}