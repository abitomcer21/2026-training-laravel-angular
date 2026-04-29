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
  IonLabel,
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
  checkmarkCircleOutline,
  fastFoodOutline,
  folderOutline,
  pricetagOutline,
  personCircleOutline,
  alertCircleOutline,
  closeCircleOutline,
  printOutline,
  cashOutline,
  arrowBackOutline
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

type EstadoPedido = 'editando' | 'confirmado' | 'cobrado';

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
    IonLabel,
  ]
})
export class ProductosComponent implements OnInit {
  productos: Product[] = [];
  productosOriginales: Product[] = [];
  productosPorFamilia: Map<string, Product[]> = new Map();
  
  currentOrder: CurrentOrder = {
    table: null,
    user: null,
    items: [],
    total: 0,
  };
  
  cargando = false;
  filtroNombre = '';
  
  familias: { id: string; name: string }[] = [];
  familiaSeleccionada: string | null = null;
  
  // Estado del pedido
  estadoPedido: EstadoPedido = 'editando';

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
      checkmarkCircleOutline,
      fastFoodOutline,
      folderOutline,
      pricetagOutline,
      personCircleOutline,
      alertCircleOutline,
      closeCircleOutline,
      printOutline,
      cashOutline,
      arrowBackOutline
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
        this.productosOriginales = todosLosProductos
          .filter((producto: any) => producto.restaurant_id === restaurantId)
          .map((producto: any) => ({
            ...producto,
            price: producto.price / 100  // Convertir céntimos a euros
          }));
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
        
        // Asignar nombre de familia a cada producto
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
        
        // Organizar productos por familia
        this.productosPorFamilia.clear();
        this.productosOriginales.forEach(producto => {
          if (producto.family_id) {
            if (!this.productosPorFamilia.has(producto.family_id)) {
              this.productosPorFamilia.set(producto.family_id, []);
            }
            this.productosPorFamilia.get(producto.family_id)!.push(producto);
          }
        });
        
        // Crear lista de familias
        this.familias = familiasDelRestaurant.map((familia: any) => ({
          id: familia.id,
          name: familia.name
        }));
        
        // Seleccionar la primera familia por defecto (para que se vean productos al cargar)
        if (this.familias.length > 0 && !this.familiaSeleccionada) {
          this.seleccionarFamilia(this.familias[0].id);
        }
      },
      error: (error) => {
        console.error('Error al cargar familias:', error);
      }
    });
  }

  // Seleccionar una familia y mostrar sus productos
  seleccionarFamilia(familiaId: string) {
    this.familiaSeleccionada = familiaId;
    this.filtroNombre = '';
    this.productos = this.productosPorFamilia.get(familiaId) || [];
    this.productosOriginales = [...this.productos];
  }

  // Obtener el nombre de la familia seleccionada
  getNombreFamilia(): string {
    const familia = this.familias.find(f => f.id === this.familiaSeleccionada);
    return familia?.name || '';
  }

  // Aplicar filtros (solo búsqueda por nombre)
  aplicarFiltros() {
    if (!this.familiaSeleccionada) return;
    
    let resultado = [...(this.productosPorFamilia.get(this.familiaSeleccionada) || [])];
    
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
        if (this.currentOrder.items.length === 0) {
          this.estadoPedido = 'editando';
        }
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
    
    if (this.estadoPedido !== 'editando') {
      alert('No puedes modificar el pedido. Primero regresa a edición.');
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

  incrementarProducto(productId: string) {
    if (this.estadoPedido !== 'editando') {
      alert('No puedes modificar el pedido. Solo en modo edición.');
      return;
    }
    const item = this.currentOrder.items.find((i) => i.productId === productId);
    if (item) {
      this.orderStateService.updateItemQuantity(productId, item.quantity + 1);
    }
  }

  decrementarProducto(productId: string) {
    if (this.estadoPedido !== 'editando') {
      alert('No puedes modificar el pedido. Solo en modo edición.');
      return;
    }
    const item = this.currentOrder.items.find((i) => i.productId === productId);
    if (item) {
      this.orderStateService.updateItemQuantity(productId, item.quantity - 1);
    }
  }

  eliminarProducto(productId: string) {
    if (this.estadoPedido !== 'editando') {
      alert('No puedes modificar el pedido. Solo en modo edición.');
      return;
    }
    this.orderStateService.removeItem(productId);
  }

  limpiarPedido() {
    if (confirm('¿Limpiar todo el pedido?')) {
      this.orderStateService.clearOrder();
      this.estadoPedido = 'editando';
    }
  }

  enviarACocina() {
    if (this.currentOrder.items.length === 0) {
      alert('No hay productos en el pedido');
      return;
    }
    
    this.estadoPedido = 'confirmado';
    alert(`✅ Pedido enviado a cocina\nMesa: ${this.currentOrder.table?.name}\nTotal: ${this.currentOrder.total.toFixed(2)} €`);
  }

  volverAEditar() {
    this.estadoPedido = 'editando';
  }

  cobrarPedido() {
    if (confirm(`💰 ¿Confirmar cobro de ${this.currentOrder.total.toFixed(2)} €?`)) {
      this.estadoPedido = 'cobrado';
      alert(`💰 Pedido cobrado correctamente\nMesa: ${this.currentOrder.table?.name}\nTotal: ${this.currentOrder.total.toFixed(2)} €`);
    }
  }

  imprimirTicket() {
    if (this.currentOrder.items.length === 0) {
      alert('No hay productos para imprimir');
      return;
    }
    
    const ticket = this.generarTicket();
    console.log('Imprimiendo ticket:', ticket);
    alert(ticket);
    
    if (confirm('¿Ticket impreso correctamente? ¿Desea comenzar un nuevo pedido?')) {
      this.nuevoPedido();
    }
  }

  nuevoPedido() {
    this.orderStateService.clearOrder();
    this.estadoPedido = 'editando';
    alert('Puedes comenzar un nuevo pedido');
  }

  private generarTicket(): string {
    let ticket = '=========================\n';
    ticket += '    🍽️ RESTAURANTE 🍽️\n';
    ticket += '=========================\n';
    ticket += `Mesa: ${this.currentOrder.table?.name}\n`;
    ticket += `Usuario: ${this.currentOrder.user?.name}\n`;
    ticket += `Fecha: ${new Date().toLocaleString()}\n`;
    ticket += '=========================\n';
    ticket += 'Producto     Cant     Total\n';
    ticket += '-------------------------\n';
    
    this.currentOrder.items.forEach(item => {
      const nombre = item.productName.length > 15 ? item.productName.substring(0, 12) + '...' : item.productName;
      ticket += `${nombre.padEnd(12)} ${item.quantity.toString().padStart(3)}    ${item.total.toFixed(2)} €\n`;
    });
    
    ticket += '-------------------------\n';
    ticket += `TOTAL: ${this.currentOrder.total.toFixed(2)} €\n`;
    ticket += '=========================\n';
    ticket += '¡Gracias por su visita!\n';
    ticket += '=========================\n';
    
    return ticket;
  }
}