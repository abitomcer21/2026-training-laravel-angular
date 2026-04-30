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
  ToastController,
  IonModal,
  IonHeader,
  IonToolbar,
  IonTitle,
  IonButtons,
  IonButton as IonButtonModal,
  IonSegment,
  IonSegmentButton,
  IonLabel as IonLabelModal,
  IonCheckbox,
  IonList,
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
    IonModal,
    IonHeader,
    IonToolbar,
    IonTitle,
    IonButtons,
    IonButtonModal,
    IonSegment,
    IonSegmentButton,
    IonLabelModal,
    IonCheckbox,
    IonList,
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
  
  estadoPedido: EstadoPedido = 'editando';
  pedidoInicialEnviado = false;
  itemsEnviadosACocina: OrderItem[] = [];

  // Propiedades para el modal de cobro
  mostrarModalCobro = false;
  tipoCobro: 'completo' | 'dividir' | 'articulos' = 'completo';
  numeroComensales = 2;
  montoPorPersona = 0;
  propina = 0;
  comentario = '';
  articulosSeleccionados: { [key: string]: boolean } = {};

  // Propiedades para el modal de ticket
  mostrarModalTicket = false;
  ticketParaImprimir = '';

  constructor(
    private productService: ProductService,
    private orderStateService: OrderStateService,
    private authService: AuthService,
    private familyService: FamilyService,
    private toastController: ToastController
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

  async mostrarToast(mensaje: string, color: string = 'success', duracion: number = 3000) {
    const toast = await this.toastController.create({
      message: mensaje,
      duration: duracion,
      position: 'top',
      color: color,
      buttons: [
        {
          text: 'Cerrar',
          role: 'cancel'
        }
      ]
    });
    await toast.present();
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
            price: producto.price / 100
          }));
        this.cargarFamilias(restaurantId);
        this.cargando = false;
      },
      error: (error) => {
        console.error('Error al cargar productos:', error);
        this.cargando = false;
        this.mostrarToast('Error al cargar productos', 'danger', 3000);
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
        
        this.productosPorFamilia.clear();
        this.productosOriginales.forEach(producto => {
          if (producto.family_id) {
            if (!this.productosPorFamilia.has(producto.family_id)) {
              this.productosPorFamilia.set(producto.family_id, []);
            }
            this.productosPorFamilia.get(producto.family_id)!.push(producto);
          }
        });
        
        this.familias = familiasDelRestaurant.map((familia: any) => ({
          id: familia.id,
          name: familia.name
        }));
        
        if (this.familias.length > 0 && !this.familiaSeleccionada) {
          this.seleccionarFamilia(this.familias[0].id);
        }
      },
      error: (error) => {
        console.error('Error al cargar familias:', error);
        this.mostrarToast('Error al cargar categorías', 'danger', 3000);
      }
    });
  }

  seleccionarFamilia(familiaId: string) {
    this.familiaSeleccionada = familiaId;
    this.filtroNombre = '';
    this.productos = this.productosPorFamilia.get(familiaId) || [];
    this.productosOriginales = [...this.productos];
  }

  getNombreFamilia(): string {
    const familia = this.familias.find(f => f.id === this.familiaSeleccionada);
    return familia?.name || '';
  }

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
          this.resetearEstadoPedido();
        }
      }
    });
  }

  resetearEstadoPedido() {
    this.estadoPedido = 'editando';
    this.pedidoInicialEnviado = false;
    this.itemsEnviadosACocina = [];
  }

  get productosFiltrados(): Product[] {
    return this.productos;
  }

  esProductoNuevo(productId: string): boolean {
    return !this.itemsEnviadosACocina.some(enviado => enviado.productId === productId);
  }

  agregarProducto(producto: Product) {
    if (!this.currentOrder.table || !this.currentOrder.user) {
      this.mostrarToast('Selecciona una mesa y usuario primero', 'warning', 2000);
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
    const item = this.currentOrder.items.find((i) => i.productId === productId);
    if (item) {
      this.orderStateService.updateItemQuantity(productId, item.quantity + 1);
    }
  }

  decrementarProducto(productId: string) {
    const item = this.currentOrder.items.find((i) => i.productId === productId);
    if (item) {
      if (item.quantity === 1) {
        this.eliminarProducto(productId);
      } else {
        this.orderStateService.updateItemQuantity(productId, item.quantity - 1);
      }
    }
  }

  eliminarProducto(productId: string) {
    this.orderStateService.removeItem(productId);
  }

  limpiarPedido() {
    if (this.currentOrder.items.length === 0) {
      this.mostrarToast('No hay productos para limpiar', 'warning', 2000);
      return;
    }
    
    this.orderStateService.clearOrder();
    this.resetearEstadoPedido();
    this.mostrarToast('Pedido limpiado correctamente', 'danger', 2000);
  }

  enviarACocina() {
    if (this.currentOrder.items.length === 0) {
      this.mostrarToast('No hay productos en el pedido', 'danger', 2000);
      return;
    }
    
    if (!this.pedidoInicialEnviado) {
      this.itemsEnviadosACocina = [...this.currentOrder.items];
      this.pedidoInicialEnviado = true;
      this.estadoPedido = 'confirmado';
      
      const totalItems = this.currentOrder.items.reduce((sum, item) => sum + item.quantity, 0);
      this.mostrarToast(
        `Pedido enviado a cocina - Mesa: ${this.currentOrder.table?.name} - ${totalItems} productos`,
        'success',
        4000
      );
    } else {
      const nuevosItems: OrderItem[] = [];
      
      this.currentOrder.items.forEach(item => {
        const itemEnviado = this.itemsEnviadosACocina.find(enviado => enviado.productId === item.productId);
        
        if (!itemEnviado) {
          nuevosItems.push({...item});
        } else if (item.quantity > itemEnviado.quantity) {
          const diferencia = item.quantity - itemEnviado.quantity;
          nuevosItems.push({
            ...item,
            quantity: diferencia,
            total: diferencia * item.price
          });
        }
      });
      
      if (nuevosItems.length === 0) {
        this.mostrarToast('No hay nuevos productos para enviar', 'warning', 2000);
        return;
      }
      
      this.itemsEnviadosACocina = [...this.currentOrder.items];
      
      const totalNuevos = nuevosItems.reduce((sum, item) => sum + item.quantity, 0);
      this.mostrarToast(
        `Nuevos productos enviados a cocina - Mesa: ${this.currentOrder.table?.name} - ${totalNuevos} productos añadidos`,
        'success',
        4000
      );
    }
  }

  volverAEditar() {
    this.estadoPedido = 'editando';
    this.mostrarToast('Modo edición activado', 'primary', 2000);
  }

  // ============================================
  // MÉTODOS DE COBRO
  // ============================================
  
  abrirModalCobro() {
    if (this.currentOrder.items.length === 0) {
      this.mostrarToast('No hay productos en el pedido', 'danger', 2000);
      return;
    }
    // Inicializar selección de artículos
    this.currentOrder.items.forEach(item => {
      this.articulosSeleccionados[item.productId] = true;
    });
    this.propina = 0;
    this.comentario = '';
    this.numeroComensales = 2;
    this.tipoCobro = 'completo';
    this.calcularMontoPorPersona();
    this.mostrarModalCobro = true;
  }

  calcularMontoPorPersona() {
    this.montoPorPersona = (this.currentOrder.total + this.propina) / this.numeroComensales;
  }

  getTotalSeleccionado(): number {
    let total = 0;
    this.currentOrder.items.forEach(item => {
      if (this.articulosSeleccionados[item.productId]) {
        total += item.total;
      }
    });
    return total + this.propina;
  }

  confirmarCobro() {
    let mensaje = '';
    let totalCobrado = 0;
    
    switch(this.tipoCobro) {
      case 'completo':
        totalCobrado = this.currentOrder.total + this.propina;
        mensaje = `Cobro completado: ${totalCobrado.toFixed(2)} €`;
        if (this.comentario) mensaje += `\nComentario: ${this.comentario}`;
        break;
      case 'dividir':
        totalCobrado = this.montoPorPersona * this.numeroComensales;
        mensaje = `Cuenta dividida entre ${this.numeroComensales} personas.\nCada una paga: ${this.montoPorPersona.toFixed(2)} €`;
        if (this.propina > 0) mensaje += `\nPropina incluida: ${this.propina.toFixed(2)} €`;
        break;
      case 'articulos':
        totalCobrado = this.getTotalSeleccionado();
        const itemsSeleccionados = this.currentOrder.items.filter(item => this.articulosSeleccionados[item.productId]);
        mensaje = `Cobro por artículos seleccionados: ${totalCobrado.toFixed(2)} €\n`;
        mensaje += `Artículos: ${itemsSeleccionados.length}`;
        break;
    }
    
    this.estadoPedido = 'cobrado';
    this.mostrarModalCobro = false;
    this.mostrarToast(mensaje, 'success', 4000);
    
    // Generar ticket y mostrarlo en modal
    this.ticketParaImprimir = this.generarTicket();
    this.mostrarModalTicket = true;
  }

  imprimirTicketDesdeModal() {
    console.log('Imprimiendo ticket:', this.ticketParaImprimir);
    this.mostrarToast('Ticket impreso correctamente', 'primary', 2000);
    this.mostrarModalTicket = false;
    
    setTimeout(() => {
      if (confirm('¿Desea comenzar un nuevo pedido?')) {
        this.nuevoPedido();
      }
    }, 500);
  }

  cerrarModalTicket() {
    this.mostrarModalTicket = false;
    setTimeout(() => {
      if (confirm('¿Desea comenzar un nuevo pedido?')) {
        this.nuevoPedido();
      }
    }, 500);
  }

  nuevoPedido() {
    this.orderStateService.clearOrder();
    this.resetearEstadoPedido();
    this.mostrarToast('Nuevo pedido listo para comenzar', 'success', 2000);
  }

  private generarTicket(): string {
    let ticket = '================================\n';
    ticket += '           RESTAURANTE\n';
    ticket += '================================\n';
    ticket += `Mesa: ${this.currentOrder.table?.name}\n`;
    ticket += `Usuario: ${this.currentOrder.user?.name}\n`;
    ticket += `Fecha: ${new Date().toLocaleString()}\n`;
    ticket += '================================\n';
    ticket += 'Producto          Cant     Total\n';
    ticket += '--------------------------------\n';
    
    this.currentOrder.items.forEach(item => {
      const nombre = item.productName.length > 15 ? item.productName.substring(0, 12) + '...' : item.productName;
      const enviado = this.itemsEnviadosACocina.some(e => e.productId === item.productId);
      const marca = enviado ? ' ✓' : ' +';
      ticket += `${nombre.padEnd(15)} ${item.quantity.toString().padStart(3)}     ${item.total.toFixed(2)} €${marca}\n`;
    });
    
    ticket += '--------------------------------\n';
    ticket += `TOTAL: ${this.currentOrder.total.toFixed(2)} €\n`;
    if (this.propina > 0) {
      ticket += `Propina: ${this.propina.toFixed(2)} €\n`;
      ticket += `Total con propina: ${(this.currentOrder.total + this.propina).toFixed(2)} €\n`;
    }
    if (this.comentario) {
      ticket += `Comentario: ${this.comentario}\n`;
    }
    ticket += '================================\n';
    ticket += '✓ = Enviado  + = Nuevo\n';
    ticket += '¡Gracias por su visita!\n';
    ticket += '================================\n';
    
    return ticket;
  }
}