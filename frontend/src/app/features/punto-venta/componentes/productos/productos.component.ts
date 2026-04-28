import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import {
  IonIcon,
  IonLoading,
  IonButton,
  IonBadge,
  IonSpinner,
} from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import { restaurantOutline, addOutline, removeOutline } from 'ionicons/icons';
import { ProductService } from '../../../../services/api/product.service';
import { OrderStateService, CurrentOrder, OrderItem } from '../../../../services/order-state.service';
import { AuthService } from '../../../../services/auth/auth.service';

export interface Product {
  id: string;
  uuid: string;
  name: string;
  description?: string;
  price: number;
  family_id?: string;
  image_src?: string;
}

@Component({
  selector: 'app-productos',
  templateUrl: './productos.component.html',
  styleUrls: ['./productos.component.scss'],
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    IonIcon,
    IonLoading,
    IonButton,
    IonBadge,
    IonSpinner,
  ],
})
export class ProductosComponent implements OnInit {
  productos: Product[] = [];
  currentOrder: CurrentOrder = {
    table: null,
    user: null,
    items: [],
    total: 0,
  };
  cargando = false;
  filtroNombre = '';

  constructor(
    private productService: ProductService,
    private orderStateService: OrderStateService,
    private authService: AuthService
  ) {
    addIcons({ restaurantOutline, addOutline, removeOutline });
  }

  ngOnInit() {
    this.cargarProductos();
    this.suscribirseAorden();
  }

  cargarProductos() {
    this.cargando = true;
    const userData = this.authService.getUserData();
    const restaurantId = userData?.restaurant_id;

    if (!restaurantId) {
      console.error('No se encontró restaurant_id del usuario');
      this.cargando = false;
      return;
    }

    this.productService.getProducts().subscribe({
      next: (response: any) => {
        // Filtrar los productos por el restaurant_id del usuario loggeado
        const todosLosProductos = response.products || [];
        this.productos = todosLosProductos.filter((producto: any) => producto.restaurant_id === restaurantId);
        this.cargando = false;
      },
      error: (error) => {
        console.error('Error al cargar productos:', error);
        this.cargando = false;
      },
    });
  }

  suscribirseAorden() {
    this.orderStateService.getCurrentOrder().subscribe({
      next: (order) => {
        this.currentOrder = order;
      },
    });
  }

  get productosFiltrados(): Product[] {
    if (!this.filtroNombre) {
      return this.productos;
    }
    return this.productos.filter((p) =>
      p.name.toLowerCase().includes(this.filtroNombre.toLowerCase())
    );
  }

  agregarProducto(producto: Product) {
    if (!this.currentOrder.table || !this.currentOrder.user) {
      alert('Por favor selecciona una mesa y usuario primero');
      return;
    }

    const item: OrderItem = {
      productId: producto.id,
      productName: producto.name,
      quantity: 1,
      price: producto.price,
      total: producto.price,
    };

    this.orderStateService.addItem(item);
  }

  obtenerCantidadProducto(productId: string): number {
    const item = this.currentOrder.items.find((i) => i.productId === productId);
    return item ? item.quantity : 0;
  }

  incrementarProducto(productId: string) {
    const item = this.currentOrder.items.find((i) => i.productId === productId);
    if (item) {
      this.orderStateService.updateItemQuantity(productId, item.quantity + 1);
    }
  }

  decrementarProducto(productId: string) {
    const item = this.currentOrder.items.find((i) => i.productId === productId);
    if (item) {
      this.orderStateService.updateItemQuantity(productId, item.quantity - 1);
    }
  }
}
