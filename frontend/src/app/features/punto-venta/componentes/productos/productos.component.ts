import { Component, OnInit, Output, EventEmitter, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { BehaviorSubject } from 'rxjs';
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
  arrowBackOutline,
} from 'ionicons/icons';

import { ProductService } from '../../../../services/api/product.service';
import { FamilyService } from '../../../../services/api/family.service';
import { TableService } from '../../../../services/api/table.service';
import { OrderStateService, CurrentOrder, OrderItem } from '../../../../services/order-state.service';
import { AuthService } from '../../../../services/auth/auth.service';

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
  @Output() cambiarVista = new EventEmitter<string>();

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

  mostrarModalCobro = false;
  tipoCobro: 'completo' | 'dividir' | 'articulos' = 'completo';
  numeroComensales = 2;
  montoPorPersona = 0;
  articulosSeleccionados: { [key: string]: boolean } = {};

  articulosPagados: { [key: string]: boolean } = {};
  totalPagado = 0;
  totalPorPagar = 0;

  mostrarModalTicket = false;
  ticketParaImprimir$ = new BehaviorSubject<string>('');

  get ticketParaImprimir(): string {
    return this.ticketParaImprimir$.value;
  }

  constructor(
    private productService: ProductService,
    private orderStateService: OrderStateService,
    private authService: AuthService,
    private familyService: FamilyService,
    private tableService: TableService,
    private toastController: ToastController,
    private changeDetector: ChangeDetectorRef
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
      buttons: [{ text: 'Cerrar', role: 'cancel' }]
    });
    await toast.present();
  }

  volverAMesas() {
    // Invalidar cache para que MesasComponent cargue datos frescos
    this.tableService.invalidateTablesCache();
    this.cambiarVista.emit('mesas');
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
        } else {
          // Cargar estados adicionales del servicio
          this.pedidoInicialEnviado = this.orderStateService.getPedidoInicialEnviadoValue();
          this.itemsEnviadosACocina = this.orderStateService.getItemsEnviadosACocinaValue();
          this.articulosPagados = this.orderStateService.getArticulosPagadosValue();
        }
        this.calcularTotalesPendientes();
      }
    });
  }

  resetearEstadoPedido() {
    this.estadoPedido = 'editando';
    this.pedidoInicialEnviado = false;
    this.itemsEnviadosACocina = [];
    this.articulosPagados = {};
    this.totalPagado = 0;
    this.totalPorPagar = 0;
  }

  get productosFiltrados(): Product[] {
    return this.productos;
  }

  esProductoNuevo(productId: string): boolean {
    return !this.itemsEnviadosACocina.some(enviado => enviado.productId === productId);
  }

  esProductoPagado(productId: string): boolean {
    return this.articulosPagados[productId] === true;
  }

  calcularTotalesPendientes() {
    let pagado = 0;
    let porPagar = 0;
    let total = 0;

    this.currentOrder.items.forEach(item => {
      total += item.total;
      if (this.articulosPagados[item.productId]) {
        pagado += item.total;
      } else {
        porPagar += item.total;
      }
    });

    this.totalPagado = pagado;
    this.totalPorPagar = porPagar;
    // Actualizar el total del pedido si no estaba calculado
    if (this.currentOrder.total !== total) {
      this.currentOrder.total = total;
    }
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
    if (this.esProductoPagado(productId)) {
      this.mostrarToast('No puedes modificar un producto ya pagado', 'warning', 2000);
      return;
    }
    const item = this.currentOrder.items.find((i) => i.productId === productId);
    if (item) {
      this.orderStateService.updateItemQuantity(productId, item.quantity + 1);
    }
  }

  decrementarProducto(productId: string) {
    if (this.esProductoPagado(productId)) {
      this.mostrarToast('No puedes modificar un producto ya pagado', 'warning', 2000);
      return;
    }
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
    if (this.esProductoPagado(productId)) {
      this.mostrarToast('No puedes eliminar un producto ya pagado', 'warning', 2000);
      return;
    }
    this.orderStateService.removeItem(productId);
    delete this.articulosPagados[productId];
    this.calcularTotalesPendientes();
  }

  limpiarPedido() {
    if (this.currentOrder.items.length === 0) {
      this.mostrarToast('No hay productos para limpiar', 'warning', 2000);
      return;
    }

    // Limpiar el pedido específico de esta mesa+usuario
    if (this.currentOrder.table && this.currentOrder.user) {
      // Normalizar IDs antes de limpiar
      const tableId = String(this.currentOrder.table.id);
      const userId = String(this.currentOrder.user.id);
      this.orderStateService.clearTableOrder(tableId, userId);
    } else {
      this.orderStateService.clearOrder();
    }
    
    this.resetearEstadoPedido();
    this.mostrarToast('Mesa limpiada y disponible', 'danger', 2000);
    this.volverAMesas();
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

      // Guardar en el servicio
      this.orderStateService.setPedidoInicialEnviado(true, this.itemsEnviadosACocina);

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
          nuevosItems.push({ ...item });
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
      this.orderStateService.setPedidoInicialEnviado(true, this.itemsEnviadosACocina);

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

  abrirModalCobro() {
    if (this.currentOrder.items.length === 0) {
      this.mostrarToast('No hay productos en el pedido', 'danger', 2000);
      return;
    }
    this.currentOrder.items.forEach(item => {
      this.articulosSeleccionados[item.productId] = !this.articulosPagados[item.productId];
    });
    this.numeroComensales = 2;
    this.tipoCobro = 'completo';
    this.calcularMontoPorPersona();
    this.mostrarModalCobro = true;
  }

  calcularMontoPorPersona() {
    const totalPendiente = this.currentOrder.items
      .filter(item => !this.articulosPagados[item.productId])
      .reduce((sum, item) => sum + item.total, 0);
    this.montoPorPersona = totalPendiente / this.numeroComensales;
  }

  getTotalSeleccionado(): number {
    let total = 0;
    this.currentOrder.items.forEach(item => {
      if (this.articulosSeleccionados[item.productId] && !this.articulosPagados[item.productId]) {
        total += item.total;
      }
    });
    return total;
  }

  getTotalPendiente(): number {
    return this.currentOrder.items
      .filter(item => !this.articulosPagados[item.productId])
      .reduce((sum, item) => sum + item.total, 0);
  }
  confirmarCobro() {
    let mensaje = '';
    let totalCobrado = 0;
    let itemsCobrados: string[] = [];

    switch (this.tipoCobro) {
      case 'completo':
        totalCobrado = this.getTotalPendiente();
        this.currentOrder.items.forEach(item => {
          if (!this.articulosPagados[item.productId]) {
            this.articulosPagados[item.productId] = true;
          }
        });
        mensaje = `Cobro completado: ${totalCobrado.toFixed(2)} €`;
        break;

      case 'dividir':
        totalCobrado = this.montoPorPersona * this.numeroComensales;
        this.currentOrder.items.forEach(item => {
          if (!this.articulosPagados[item.productId]) {
            this.articulosPagados[item.productId] = true;
          }
        });
        mensaje = `Cuenta dividida entre ${this.numeroComensales} personas. Cada una paga: ${this.montoPorPersona.toFixed(2)} €`;
        break;

      case 'articulos':
        totalCobrado = this.getTotalSeleccionado();
        this.currentOrder.items.forEach(item => {
          if (this.articulosSeleccionados[item.productId] && !this.articulosPagados[item.productId]) {
            this.articulosPagados[item.productId] = true;
            itemsCobrados.push(item.productName);
          }
        });
        mensaje = `Cobro por artículos seleccionados: ${totalCobrado.toFixed(2)} €. Artículos pagados: ${itemsCobrados.length}`;
        break;
    }

    this.calcularTotalesPendientes();

    // Guardar los pagos en el servicio
    this.orderStateService.setArticulosPagados(this.articulosPagados);

    if (this.totalPorPagar === 0) {
      this.estadoPedido = 'cobrado';
    }

    this.mostrarModalCobro = false;
    this.mostrarToast(mensaje, 'success', 4000);

    // Generar ticket y mostrarlo en modal - con pequeño delay para asegurar que el modal anterior se cierre
    setTimeout(() => {
      // Sincronizar TODOS los datos del ticket ANTES de generarlo
      // 1. Obtener el pedido actual más reciente del servicio
      const currentOrderFromService = this.orderStateService.getCurrentOrderValue();
      
      // 2. Asegurar que tenemos los items
      if (currentOrderFromService && currentOrderFromService.items) {
        this.currentOrder = currentOrderFromService;
      }
      
      // 3. Sincronizar estados
      this.calcularTotalesPendientes();
      this.pedidoInicialEnviado = this.orderStateService.getPedidoInicialEnviadoValue();
      this.itemsEnviadosACocina = this.orderStateService.getItemsEnviadosACocinaValue();
      this.articulosPagados = this.orderStateService.getArticulosPagadosValue();
      
      // 4. Asegurar que currentOrder.total está calculado
      if (!this.currentOrder.total || this.currentOrder.total === 0) {
        this.currentOrder.total = this.currentOrder.items.reduce((sum, item) => sum + item.total, 0);
      }
      
      // 5. Generar el ticket y guardarlo en el BehaviorSubject
      const ticket = this.generarTicket();
      this.ticketParaImprimir$.next(ticket);
      console.log('Ticket generado:', ticket);
      
      // 6. Mostrar popup
      this.mostrarModalTicket = true;
    }, 300);
  }

  imprimirTicketDesdeModal() {
    this.mostrarToast('Ticket impreso correctamente', 'primary', 2000);
    this.mostrarModalTicket = false;

    // Sincronizar datos actuales
    this.articulosPagados = this.orderStateService.getArticulosPagadosValue();
    this.calcularTotalesPendientes();

    if (this.totalPorPagar === 0) {
      this.mostrarToast('Pedido completado. Volviendo a mesas...', 'success', 2000);
      setTimeout(() => {
        if (this.currentOrder.table && this.currentOrder.user) {
          // Normalizar IDs antes de limpiar
          const tableId = String(this.currentOrder.table.id);
          const userId = String(this.currentOrder.user.id);
          this.orderStateService.clearTableOrder(tableId, userId);
        } else {
          this.orderStateService.clearOrder();
        }
        this.resetearEstadoPedido();
        this.volverAMesas();
      }, 1500);
    } else {
      this.mostrarToast(`Restan por pagar: ${this.totalPorPagar.toFixed(2)} €`, 'warning', 3000);
    }
  }

  cerrarModalTicket() {
    this.mostrarModalTicket = false;

    // Sincronizar datos actuales
    this.articulosPagados = this.orderStateService.getArticulosPagadosValue();
    this.calcularTotalesPendientes();

    // SOLO redirigir si NO hay nada pendiente de pago
    if (this.totalPorPagar === 0) {
      this.mostrarToast('Pedido completado. Volviendo a mesas...', 'success', 2000);
      setTimeout(() => {
        if (this.currentOrder.table && this.currentOrder.user) {
          // Normalizar IDs antes de limpiar
          const tableId = String(this.currentOrder.table.id);
          const userId = String(this.currentOrder.user.id);
          this.orderStateService.clearTableOrder(tableId, userId);
        } else {
          this.orderStateService.clearOrder();
        }
        this.resetearEstadoPedido();
        this.volverAMesas();
      }, 1500);
    }
  }

  nuevoPedido() {
    if (this.currentOrder.table && this.currentOrder.user) {
      // Normalizar IDs antes de limpiar
      const tableId = String(this.currentOrder.table.id);
      const userId = String(this.currentOrder.user.id);
      this.orderStateService.clearTableOrder(tableId, userId);
    } else {
      this.orderStateService.clearOrder();
    }
    this.resetearEstadoPedido();
    this.mostrarToast('Nuevo pedido listo para comenzar', 'success', 2000);
    this.volverAMesas();
  }



  private generarTicket(): string {
    // Asegurar que tenemos los datos más recientes
    const order = this.currentOrder || this.orderStateService.getCurrentOrderValue();
    const items = (order && order.items) ? order.items : [];
    
    let ticket = '================================\n';
    ticket += '           RESTAURANTE\n';
    ticket += '================================\n';
    ticket += `Mesa: ${order?.table?.name || 'N/A'}\n`;
    ticket += `Usuario: ${order?.user?.name || 'N/A'}\n`;
    ticket += `Fecha: ${new Date().toLocaleString()}\n`;
    ticket += '================================\n';
    ticket += 'Producto          Cant     Total\n';
    ticket += '--------------------------------\n';

    if (items && items.length > 0) {
      items.forEach(item => {
        const nombre = item.productName.length > 15 ? item.productName.substring(0, 12) + '...' : item.productName;
        const enviado = this.itemsEnviadosACocina && this.itemsEnviadosACocina.some(e => e.productId === item.productId);
        const pagado = this.articulosPagados && this.articulosPagados[item.productId];
        let estado = '';
        if (pagado) estado = ' PAGADO';
        else if (enviado) estado = ' EN COCINA';
        else estado = ' NUEVO';

        ticket += `${nombre.padEnd(15)} ${item.quantity.toString().padStart(3)}     ${item.total.toFixed(2)} €${estado}\n`;
      });
    } else {
      ticket += 'Sin items\n';
    }

    ticket += '--------------------------------\n';
    if (this.totalPagado > 0) {
      ticket += `PAGADO: ${this.totalPagado.toFixed(2)} €\n`;
    }
    if (this.totalPorPagar > 0) {
      ticket += `PENDIENTE: ${this.totalPorPagar.toFixed(2)} €\n`;
    }
    ticket += `TOTAL: ${(order?.total || 0).toFixed(2)} €\n`;
    ticket += '================================\n';
    ticket += 'Gracias por su visita\n';
    ticket += '================================\n';

    return ticket;
  }
}