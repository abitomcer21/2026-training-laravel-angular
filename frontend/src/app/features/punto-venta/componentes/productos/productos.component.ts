import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import {
  IonIcon,
  IonLoading,
  IonButton,
  IonBadge,
  IonSpinner,
  IonContent,
  IonItem,
  IonInput,
  IonChip,
  IonCard,
  IonCardHeader,
  IonCardTitle,
  IonCardSubtitle,
  IonCardContent,
} from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import { 
  restaurantOutline, 
  addOutline, 
  removeOutline,
  searchOutline,
  removeCircleOutline,
  addCircleOutline,
  trashOutline,
  cartOutline,
  checkmarkCircleOutline
} from 'ionicons/icons';
import { ProductService } from '../../../../services/api/product.service';
import { OrderStateService, CurrentOrder, OrderItem } from '../../../../services/order-state.service';
import { AuthService } from '../../../../services/auth/auth.service';
import { FamilyService } from '../../../../services/api/family.service';

export interface Product {
  id: string;
  uuid: string;
  name: string;
  description?: string;
  price: number;
  family_id?: string;
  family_name?: string;
  image_src?: string;
  restaurant_id?: string;
}

export interface Family {
  id: string;
  uuid: string;
  name: string;
  restaurant_id?: string;
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
    IonContent,
    IonItem,
    IonInput,
    IonChip,
    IonCard,
    IonCardHeader,
    IonCardTitle,
    IonCardSubtitle,
    IonCardContent,
  ]
})
export class ProductosComponent implements OnInit {
  productos: Product[] = [];
  productosOriginales: Product[] = [];
  currentOrder: CurrentOrder = {
    table: null,
    user: null,
    items: [],
    total: 0,
  };
  cargando = false;
  filtroNombre = '';
  
  familias: { id: string; name: string }[] = [];
  familiaSeleccionada = '';

  constructor(
    private productService: ProductService,
    private orderStateService: OrderStateService,
    private authService: AuthService,
    private familyService: FamilyService
  ) {
    addIcons({ 
      restaurantOutline, 
      addOutline, 
      removeOutline,
      searchOutline,
      removeCircleOutline,
      addCircleOutline,
      trashOutline,
      cartOutline,
      checkmarkCircleOutline
    });
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
      next: (productsResponse: any) => {
        const todosLosProductos = productsResponse.products || [];
        this.productosOriginales = todosLosProductos.filter(
          (producto: any) => producto.restaurant_id === restaurantId
        );
        this.productos = [...this.productosOriginales];
        this.cargarFamilias(restaurantId);
        this.cargando = false;
      },
      error: (error) => {
        console.error('Error al cargar productos:', error);
        this.cargando = false;
      }
    });
  }

  cargarFamilias(restaurantId: string) {
    this.familyService.getFamilies().subscribe({
      next: (familiesResponse: any) => {
        let todasLasFamilias = familiesResponse?.Family || familiesResponse?.families || [];
        
        const familiasDelRestaurant = todasLasFamilias.filter(
          (familia: any) => !restaurantId || familia.restaurant_id === restaurantId
        );
        
        const familyMap = new Map<string, string>();
        familiasDelRestaurant.forEach((familia: any) => {
          const familyId = familia.id;
          const familyName = familia.name;
          if (familyId && familyName) {
            familyMap.set(familyId, familyName);
          }
        });
        
        this.productosOriginales = this.productosOriginales.map(producto => {
          let familyName = 'Sin familia';
          if (producto.family_id && familyMap.has(producto.family_id)) {
            familyName = familyMap.get(producto.family_id) || 'Sin familia';
          }
          return {
            ...producto,
            family_name: familyName
          };
        });
        
        this.productos = [...this.productosOriginales];
        
        this.familias = familiasDelRestaurant.map((familia: any) => ({
          id: familia.id,
          name: familia.name
        }));
        
        if (this.familiaSeleccionada || this.filtroNombre) {
          this.aplicarFiltros();
        }
      },
      error: (error) => {
        console.error('Error al cargar familias:', error);
      }
    });
  }

  filtrarPorFamilia(familiaId: string) {
    if (this.familiaSeleccionada === familiaId) {
      this.familiaSeleccionada = '';
    } else {
      this.familiaSeleccionada = familiaId;
    }
    this.aplicarFiltros();
  }

  limpiarFiltroFamilia() {
    this.familiaSeleccionada = '';
    this.aplicarFiltros();
  }

  aplicarFiltros() {
    let resultado = [...this.productosOriginales];
    
    if (this.familiaSeleccionada) {
      resultado = resultado.filter(producto => 
        producto.family_id === this.familiaSeleccionada
      );
    }
    
    if (this.filtroNombre && this.filtroNombre.trim()) {
      const termino = this.filtroNombre.toLowerCase().trim();
      resultado = resultado.filter(producto =>
        producto.name.toLowerCase().includes(termino)
      );
    }
    
    this.productos = resultado;
  }

  onFiltroNombreChange() {
    this.aplicarFiltros();
  }

  suscribirseAorden() {
    this.orderStateService.getCurrentOrder().subscribe({
      next: (order) => {
        this.currentOrder = order;
      }
    });
  }

  get productosFiltrados(): Product[] {
    return this.productos;
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

  eliminarProducto(productId: string) {
    this.orderStateService.removeItem(productId);
  }

  limpiarPedido() {
    if (confirm('¿Limpiar todo el pedido?')) {
      this.orderStateService.clearOrder();
    }
  }

  confirmarPedido() {
    if (this.currentOrder.items.length === 0) {
      alert('No hay productos en el pedido');
      return;
    }
    alert(`Pedido confirmado para mesa ${this.currentOrder.table?.name}\nTotal: $${this.currentOrder.total}`);
  }
}