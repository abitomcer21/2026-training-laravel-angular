import {
  Component,
  OnInit,
  Output,
  EventEmitter,
  ChangeDetectorRef,
} from '@angular/core';
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
  documentTextOutline,
} from 'ionicons/icons';

import { ProductService } from '../../../../services/api/product.service';
import { FamilyService } from '../../../../services/api/family.service';
import { TableService } from '../../../../services/api/table.service';
import { TaxService } from '../../../../services/api/tax.service';
import {
  OrderStateService,
  CurrentOrder,
  OrderItem,
} from '../../../../services/order-state.service';
import { AuthService } from '../../../../services/auth/auth.service';
import { OrderService } from '../../../../services/api/order.service';
import { SalesService } from '../../../../services/api/sales.service';

export interface Product {
  id: string;
  uuid: string;
  name: string;
  description?: string;
  price: number;
  iva?: number;
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
    IonButton,
    IonBadge,
    IonSpinner,
    IonContent,
    IonModal,
    IonHeader,
    IonToolbar,
    IonTitle,
    IonButtons,
    IonButtonModal,
  ],
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
    comensales: 1,
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

  metodoSeleccionado: 'efectivo' | 'tarjeta' | 'mixto' | null = 'tarjeta';
  montosMetodoPago = {
    efectivo: 0,
    tarjeta: 0,
  };

  articulosPagados: { [key: string]: boolean } = {};
  totalPagado = 0;
  totalPorPagar = 0;

  mostrarModalTicket = false;
  ticketParaImprimir$ = new BehaviorSubject<string>('');

  get ticketParaImprimir(): string {
    return this.ticketParaImprimir$.value;
  }

  numeroTicketActual: string | null = null;

  taxMap: Map<string, number> = new Map();

  constructor(
    private productService: ProductService,
    private orderStateService: OrderStateService,
    private authService: AuthService,
    private familyService: FamilyService,
    private tableService: TableService,
    private taxService: TaxService,
    private toastController: ToastController,
    private changeDetector: ChangeDetectorRef,
    private orderService: OrderService,
    private salesService: SalesService,
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
      arrowBackOutline,
      documentTextOutline,
    });
  }

  ngOnInit() {
    this.cargarProductos();
    this.suscribirseAorden();
  }

  async mostrarToast(
    mensaje: string,
    color: string = 'success',
    duracion: number = 3000,
  ) {
    const toast = await this.toastController.create({
      message: mensaje,
      duration: duracion,
      position: 'top',
      color: color,
      buttons: [{ text: 'Cerrar', role: 'cancel' }],
    });
    await toast.present();
  }

  volverAMesas() {
    this.resetearEstadoPedido();

    this.tableService.invalidateTablesCache();

    this.cambiarVista.emit('mesas');
  }

  cargarProductos() {
    this.cargando = true;
    const userData = this.authService.getUserData();
    const restaurantId = userData?.restaurant_id;

    if (!restaurantId) {
      this.cargando = false;
      return;
    }

    this.taxService.getTaxes().subscribe({
      next: (taxesResponse: any) => {
        let todosLosTaxes: any[] = [];

        if (Array.isArray(taxesResponse)) {
          todosLosTaxes = taxesResponse;
        } else if (taxesResponse.tax) {
          todosLosTaxes = taxesResponse.tax;
        } else if (taxesResponse.taxes) {
          todosLosTaxes = taxesResponse.taxes;
        } else if (taxesResponse.Tax) {
          todosLosTaxes = taxesResponse.Tax;
        } else if (taxesResponse.data) {
          todosLosTaxes = taxesResponse.data;
        } else {
          console.warn('No se encontró lista de taxes en ninguna propiedad');
          console.warn('Propiedades disponibles:', Object.keys(taxesResponse));
        }
        // Crear el mapa de tax_id -> percentage
        this.taxMap.clear();
        todosLosTaxes.forEach((tax: any) => {
          this.taxMap.set(tax.id?.toString(), tax.percentage || 0);
          if (tax.uuid) {
            this.taxMap.set(tax.uuid?.toString(), tax.percentage || 0);
          }
        });

        this.cargarProductosConTaxes(restaurantId);
      },
      error: (error) => {
        this.cargando = false;
      },
    });
  }

  private cargarProductosConTaxes(restaurantId: string) {
    this.productService.getProducts().subscribe({
      next: (productsResponse: any) => {
        const todosLosProductos = productsResponse.products || [];

        this.productosOriginales = todosLosProductos
          .filter((producto: any) => producto.restaurant_id === restaurantId)
          .map((producto: any) => {
            const ivaFromMap =
              this.taxMap.get(producto.tax_id?.toString()) || 0;

            return {
              ...producto,
              price: producto.price / 100,
              iva: ivaFromMap,
            };
          });

        this.cargarFamilias(restaurantId);
        this.cargando = false;
      },
      error: (error) => {
        this.cargando = false;
        this.mostrarToast('Error al cargar productos', 'danger', 3000);
      },
    });
  }

  cargarFamilias(restaurantId: string) {
    this.familyService.getFamilies().subscribe({
      next: (familiesResponse: any) => {
        let todasLasFamilias =
          familiesResponse?.Family || familiesResponse?.families || [];

        const familiasDelRestaurant = todasLasFamilias.filter(
          (familia: any) =>
            (!restaurantId || familia.restaurant_id === restaurantId) &&
            familia.active,
        );

        const familyMap = new Map<string, string>();
        familiasDelRestaurant.forEach((familia: any) => {
          const familyId = familia.id;
          const familyName = familia.name;
          if (familyId && familyName) {
            familyMap.set(familyId, familyName);
          }
        });

        this.productosOriginales = this.productosOriginales.map((producto) => {
          let familyName = 'Sin familia';
          if (producto.family_id && familyMap.has(producto.family_id)) {
            familyName = familyMap.get(producto.family_id) || 'Sin familia';
          }
          return {
            ...producto,
            family_name: familyName,
          };
        });

        this.productosPorFamilia.clear();
        this.productosOriginales.forEach((producto) => {
          if (producto.family_id) {
            if (!this.productosPorFamilia.has(producto.family_id)) {
              this.productosPorFamilia.set(producto.family_id, []);
            }
            this.productosPorFamilia.get(producto.family_id)!.push(producto);
          }
        });

        this.familias = familiasDelRestaurant.map((familia: any) => ({
          id: familia.id,
          name: familia.name,
        }));

        if (this.familias.length > 0 && !this.familiaSeleccionada) {
          this.seleccionarFamilia(this.familias[0].id);
        }
      },
      error: (error) => {
        this.mostrarToast('Error al cargar categorías', 'danger', 3000);
      },
    });
  }

  seleccionarFamilia(familiaId: string) {
    this.familiaSeleccionada = familiaId;
    this.filtroNombre = '';
    this.productos = this.productosPorFamilia.get(familiaId) || [];
    this.productosOriginales = [...this.productos];
  }

  getNombreFamilia(): string {
    const familia = this.familias.find(
      (f) => f.id === this.familiaSeleccionada,
    );
    return familia?.name || '';
  }

  aplicarFiltros() {
    if (!this.familiaSeleccionada) return;

    let resultado = [
      ...(this.productosPorFamilia.get(this.familiaSeleccionada) || []),
    ];

    if (this.filtroNombre && this.filtroNombre.trim()) {
      const termino = this.filtroNombre.toLowerCase().trim();
      resultado = resultado.filter((producto) =>
        producto.name.toLowerCase().includes(termino),
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
        if (order.items && order.items.length > 0) {
          order.items.forEach((item, idx) => {});
        }

        if (!order || !order.items || order.items.length === 0) {
          this.resetearEstadoPedido();
          if (order) {
            this.currentOrder.table = order.table;
            this.currentOrder.user = order.user;
            this.currentOrder.comensales = order.comensales || 1;
          }
        } else {
          this.currentOrder = order;
          this.pedidoInicialEnviado =
            this.orderStateService.getPedidoInicialEnviadoValue();
          this.itemsEnviadosACocina =
            this.orderStateService.getItemsEnviadosACocinaValue();
          this.articulosPagados =
            this.orderStateService.getArticulosPagadosValue();
        }

        this.calcularTotalesPendientes();
      },
    });
  }

  resetearEstadoPedido() {
    this.estadoPedido = 'editando';
    this.pedidoInicialEnviado = false;
    this.itemsEnviadosACocina = [];
    this.articulosPagados = {};
    this.totalPagado = 0;
    this.totalPorPagar = 0;
    this.numeroTicketActual = null;

    this.currentOrder = {
      table: null,
      user: null,
      items: [],
      total: 0,
      comensales: 1,
    };

    this.mostrarModalCobro = false;
    this.mostrarModalTicket = false;
    this.tipoCobro = 'completo';
    this.numeroComensales = 2;
    this.montoPorPersona = 0;
    this.articulosSeleccionados = {};
    this.filtroNombre = '';
    this.familiaSeleccionada = null;
  }

  get productosFiltrados(): Product[] {
    return this.productos;
  }

  esProductoNuevo(productId: string): boolean {
    return !this.itemsEnviadosACocina.some(
      (enviado) => enviado.productId === productId,
    );
  }

  esProductoPagado(productId: string): boolean {
    return this.articulosPagados[productId] === true;
  }

  calcularTotalesPendientes() {
    let pagado = 0;
    let porPagar = 0;
    let total = 0;

    this.currentOrder.items.forEach((item) => {
      total += item.total;
      if (this.articulosPagados[item.productId]) {
        pagado += item.total;
      } else {
        porPagar += item.total;
      }
    });

    this.totalPagado = pagado;
    this.totalPorPagar = porPagar;
    if (this.currentOrder.total !== total) {
      this.currentOrder.total = total;
    }
  }

  agregarProducto(producto: Product) {
    if (!this.currentOrder.table || !this.currentOrder.user) {
      this.mostrarToast(
        'Selecciona una mesa y usuario primero',
        'warning',
        2000,
      );
      return;
    }

    const item: OrderItem = {
      productId: producto.id,
      productName: producto.name,
      quantity: 1,
      price: producto.price,
      total: producto.price,
      iva: producto.iva || 0,
    };

    this.orderStateService.addItem(item);
  }

  incrementarProducto(productId: string) {
    if (this.esProductoPagado(productId)) {
      this.mostrarToast(
        'No puedes modificar un producto ya pagado',
        'warning',
        2000,
      );
      return;
    }
    const item = this.currentOrder.items.find((i) => i.productId === productId);
    if (item) {
      this.orderStateService.updateItemQuantity(productId, item.quantity + 1);
    }
  }

  decrementarProducto(productId: string) {
    if (this.esProductoPagado(productId)) {
      this.mostrarToast(
        'No puedes modificar un producto ya pagado',
        'warning',
        2000,
      );
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
      this.mostrarToast(
        'No puedes eliminar un producto ya pagado',
        'warning',
        2000,
      );
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

    if (this.currentOrder.table) {
      const tableId = String(this.currentOrder.table.id);
      this.orderStateService.clearTableOrder(tableId);
    } else {
      this.orderStateService.clearOrder();
    }

    this.resetearEstadoPedido();
    this.volverAMesas();
  }

 enviarACocina() {
  if (this.currentOrder.items.length === 0) {
    return;
  }

  const userData = this.authService.getUserData();

  if (!this.pedidoInicialEnviado) {
    const payload = {
      restaurant_id: userData.restaurant_id,
      table_id: this.currentOrder.table!.id,
      opened_by_user_id: this.currentOrder.user!.id,
      status: 'open',
      diners: this.currentOrder.comensales || 1,
      order_lines: this.currentOrder.items.map((item) => {
        return {
          product_id: item.productId,
          user_id: this.currentOrder.user!.id,
          quantity: item.quantity,
          price: Math.round(item.price * 100),
          tax_percentage: item.iva || 0,
        };
      }),
    };

    this.orderService.createOrder(payload).subscribe({
      next: (response) => {
        this.orderStateService.setOrderId(response.id);
        this.itemsEnviadosACocina = [...this.currentOrder.items];
        this.pedidoInicialEnviado = true;
        this.estadoPedido = 'confirmado';
        this.orderStateService.setPedidoInicialEnviado(true, this.itemsEnviadosACocina);
        this.mostrarToast(`Pedido enviado a cocina — Mesa: ${this.currentOrder.table?.name}`, 'success', 3000);
      },
      error: () => this.mostrarToast('Error al enviar pedido', 'danger', 3000),
    });

  } else {
    const nuevosItems: OrderItem[] = [];

    this.currentOrder.items.forEach((item) => {
      const itemEnviado = this.itemsEnviadosACocina.find((e) => e.productId === item.productId);
      if (!itemEnviado) {
        nuevosItems.push({ ...item });
      } else if (item.quantity > itemEnviado.quantity) {
        const diferencia = item.quantity - itemEnviado.quantity;
        nuevosItems.push({ ...item, quantity: diferencia, total: diferencia * item.price });
      }
    });

    if (nuevosItems.length === 0) {
      this.mostrarToast('No hay nuevos productos para enviar', 'warning', 2000);
      return;
    }

    const orderId = this.orderStateService.getOrderIdValue();
    const addLinesPayload = {
      order_lines: nuevosItems.map((item) => ({
        product_id: item.productId,
        user_id: this.currentOrder.user!.id,
        quantity: item.quantity,
        price: Math.round(item.price * 100),
        tax_percentage: item.iva || 0,
      })),
    };

    this.orderService.addOrderLines(orderId, addLinesPayload).subscribe({
      next: () => {
        this.itemsEnviadosACocina = [...this.currentOrder.items];
        this.orderStateService.setPedidoInicialEnviado(true, this.itemsEnviadosACocina);
        const totalNuevos = nuevosItems.reduce((sum, item) => sum + item.quantity, 0);
        this.mostrarToast(`${totalNuevos} productos añadidos a cocina`, 'success', 3000);
      },
      error: () => this.mostrarToast('Error al añadir productos', 'danger', 3000),
    });
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
    this.currentOrder.items.forEach((item) => {
      this.articulosSeleccionados[item.productId] =
        !this.articulosPagados[item.productId];
    });
    this.numeroComensales = 2;
    this.tipoCobro = 'completo';
    this.calcularMontoPorPersona();
    this.mostrarModalCobro = true;
  }

  calcularMontoPorPersona() {
    const totalPendiente = this.currentOrder.items
      .filter((item) => !this.articulosPagados[item.productId])
      .reduce((sum, item) => sum + item.total, 0);
    this.montoPorPersona = totalPendiente / this.numeroComensales;
  }

  aumentarComensales() {
    const comensalesActual = this.currentOrder.comensales || 1;
    const nuevosComensal = comensalesActual + 1;
    this.orderStateService.setComensales(nuevosComensal);
  }

  disminuirComensales() {
    const comensalesActual = this.currentOrder.comensales || 1;
    if (comensalesActual > 1) {
      const nuevosComensal = comensalesActual - 1;
      this.orderStateService.setComensales(nuevosComensal);
    }
  }

  getTotalSeleccionado(): number {
    let total = 0;
    this.currentOrder.items.forEach((item) => {
      if (
        this.articulosSeleccionados[item.productId] &&
        !this.articulosPagados[item.productId]
      ) {
        total += item.total;
      }
    });
    return total;
  }

  Math = Math;

  getTotalPendiente(): number {
    return this.currentOrder.items
      .filter((item) => !this.articulosPagados[item.productId])
      .reduce((sum, item) => sum + item.total, 0);
  }
  confirmarCobro() {
    if (!this.metodoSeleccionado) {
      this.mostrarToast('Selecciona un método de pago', 'warning', 2000);
      return;
    }

    if (this.metodoSeleccionado === 'mixto') {
      const totalMixto =
        this.montosMetodoPago.efectivo + this.montosMetodoPago.tarjeta;
      const totalPendiente = this.getTotalPendiente();

      if (Math.abs(totalMixto - totalPendiente) > 0.01) {
        this.mostrarToast(
          `Total mixto (${totalMixto.toFixed(2)}€) no coincide con pendiente (${totalPendiente.toFixed(2)}€)`,
          'warning',
          3000,
        );
        return;
      }
    }

    let mensaje = '';
    let totalCobrado = 0;
    let itemsCobrados: string[] = [];

    switch (this.tipoCobro) {
      case 'completo':
        totalCobrado = this.getTotalPendiente();
        this.currentOrder.items.forEach((item) => {
          if (!this.articulosPagados[item.productId]) {
            this.articulosPagados[item.productId] = true;
          }
        });
        break;

      case 'dividir':
        totalCobrado = this.montoPorPersona * this.numeroComensales;
        this.currentOrder.items.forEach((item) => {
          if (!this.articulosPagados[item.productId]) {
            this.articulosPagados[item.productId] = true;
          }
        });
        break;

      case 'articulos':
        totalCobrado = this.getTotalSeleccionado();
        this.currentOrder.items.forEach((item) => {
          if (
            this.articulosSeleccionados[item.productId] &&
            !this.articulosPagados[item.productId]
          ) {
            this.articulosPagados[item.productId] = true;
            itemsCobrados.push(item.productName);
          }
        });
        mensaje = `Cobro por artículos seleccionados: ${totalCobrado.toFixed(2)} €. Artículos pagados: ${itemsCobrados.length}`;
        break;
    }

    this.calcularTotalesPendientes();

    this.orderStateService.setArticulosPagados(this.articulosPagados);

    if (this.totalPorPagar === 0) {
      this.estadoPedido = 'cobrado';
    }

    let mensajeMetodo = '';
    switch (this.metodoSeleccionado) {
      case 'efectivo':
        mensajeMetodo = ' (Pagado en efectivo)';
        break;
      case 'tarjeta':
        mensajeMetodo = ' (Pagado con tarjeta)';
        break;
      case 'mixto':
        mensajeMetodo = ` (Pago mixto: ${this.montosMetodoPago.efectivo.toFixed(2)}€ + ${this.montosMetodoPago.tarjeta.toFixed(2)}€)`;
        break;
    }

    const orderId = this.orderStateService.getOrderIdValue();
    if (this.currentOrder.user && this.currentOrder.user.id && orderId) {
      const salePayload = {
        order_id: orderId,
        user_id: this.currentOrder.user.id,
      };

      this.salesService.createSale(salePayload).subscribe({
        next: (response) => {
          console.log('Sale created successfully:', response);
          this.mostrarToast('Venta registrada correctamente', 'success', 2000);
        },
        error: (error) => {
          console.error('Error creating sale:', error);
          this.mostrarToast('Error al registrar la venta', 'danger', 3000);
        },
      });
    } else {
      console.error('Current order user or user id is missing', { orderId, user: this.currentOrder.user });
      this.mostrarToast('Error: falta información del usuario o pedido', 'danger', 3000);
    }

    this.mostrarModalCobro = false;

    setTimeout(() => {
      const currentOrderFromService =
        this.orderStateService.getCurrentOrderValue();

      if (currentOrderFromService && currentOrderFromService.items) {
        this.currentOrder = currentOrderFromService;
      }

      this.calcularTotalesPendientes();
      this.pedidoInicialEnviado =
        this.orderStateService.getPedidoInicialEnviadoValue();
      this.itemsEnviadosACocina =
        this.orderStateService.getItemsEnviadosACocinaValue();
      this.articulosPagados = this.orderStateService.getArticulosPagadosValue();

      if (!this.currentOrder.total || this.currentOrder.total === 0) {
        this.currentOrder.total = this.currentOrder.items.reduce(
          (sum, item) => sum + item.total,
          0,
        );
      }

      if (!this.numeroTicketActual) {
        this.numeroTicketActual = this.generarNumeroTicket();
      }

      const ticket = this.generarTicket();
      this.ticketParaImprimir$.next(ticket);
      console.log('Ticket generado:', ticket);

      this.mostrarModalTicket = true;
    }, 300);
  }

  imprimirTicketDesdeModal() {
    this.mostrarModalTicket = false;

    const tableId = this.currentOrder.table
      ? String(this.currentOrder.table.id)
      : null;
    const userId = this.currentOrder.user
      ? String(this.currentOrder.user.id)
      : null;

    setTimeout(() => {
      if (tableId && userId) {
        this.orderStateService.clearTableOrder(tableId);
      } else {
        this.orderStateService.clearOrder();
      }

      this.resetearEstadoPedido();

      setTimeout(() => {
        this.volverAMesas();
      }, 500);
    }, 500);
  }

  cerrarModalTicket() {
    this.mostrarModalTicket = false;

    // Sincronizar datos actuales para saber si hay pendiente
    this.articulosPagados = this.orderStateService.getArticulosPagadosValue();
    this.calcularTotalesPendientes();

    if (this.totalPorPagar === 0) {
      const tableId = this.currentOrder.table
        ? String(this.currentOrder.table.id)
        : null;
      const userId = this.currentOrder.user
        ? String(this.currentOrder.user.id)
        : null;

      setTimeout(() => {
        if (tableId && userId) {
          this.orderStateService.clearTableOrder(tableId);
        } else {
          this.orderStateService.clearOrder();
        }
        this.resetearEstadoPedido();
        this.volverAMesas();
      }, 500);
    } else {
    }
  }

  nuevoPedido() {
    if (this.currentOrder.table) {
      const tableId = String(this.currentOrder.table.id);
      this.orderStateService.clearTableOrder(tableId);
    } else {
      this.orderStateService.clearOrder();
    }
    this.resetearEstadoPedido();
    this.volverAMesas();
  }

  verTicketProvisional() {
    if (this.currentOrder.items.length === 0) {
      return;
    }
    const ticket = this.generarTicket('PROVISIONAL');
    this.ticketParaImprimir$.next(ticket);
    this.mostrarModalTicket = true;
  }

  private generarNumeroTicket(): string {
    const ultimoNumero = localStorage.getItem('ultimoNumeroTicket');
    const numero = ultimoNumero ? parseInt(ultimoNumero) + 1 : 1;

    localStorage.setItem('ultimoNumeroTicket', numero.toString());

    return `T-${numero.toString().padStart(3, '0')}`;
  }

  private generarTicket(tipoTicket?: string): string {
    const order =
      this.currentOrder || this.orderStateService.getCurrentOrderValue();
    const items = order && order.items ? order.items : [];

    let ticket = '================================\n';
    ticket += '           RESTAURANTE\n';
    ticket += '================================\n';

    if (tipoTicket !== 'PROVISIONAL' && this.numeroTicketActual) {
      ticket += `Ticket: ${this.numeroTicketActual}\n`;
    } else if (tipoTicket === 'PROVISIONAL') {
      ticket += `Ticket: PROVISIONAL\n`;
    }

    ticket += `Mesa: ${order?.table?.name || 'N/A'}\n`;
    ticket += `Usuario: ${order?.user?.name || 'N/A'}\n`;
    ticket += `Fecha: ${new Date().toLocaleString()}\n`;
    ticket += '================================\n';
    ticket += 'Producto       Cant Precio IVA%\n';
    ticket += '--------------------------------\n';

    let subtotalSinIva = 0;
    let totalIva = 0;

    if (items && items.length > 0) {
      items.forEach((item) => {
        const ivaRate = (item.iva || 0) / 100;

        const precioConIva = item.price;
        const precioSinIva =
          ivaRate > 0 ? precioConIva / (1 + ivaRate) : precioConIva;
        const ivaItem = precioConIva - precioSinIva;

        subtotalSinIva += precioSinIva * item.quantity;
        totalIva += ivaItem * item.quantity;

        const nombre =
          item.productName.length > 12
            ? item.productName.substring(0, 11) + '.'
            : item.productName;
        const ivaDisplay = (item.iva || 0).toString().padStart(3);

        ticket += `${nombre.padEnd(12)} ${item.quantity.toString().padStart(2)}   ${precioConIva.toFixed(2).padStart(5)} ${ivaDisplay}%\n`;
      });
    } else {
      ticket += 'Sin items\n';
    }

    const totalConIva = subtotalSinIva + totalIva;

    ticket += '--------------------------------\n';
    ticket += `Subtotal: ${subtotalSinIva.toFixed(2).padStart(17)} €\n`;
    ticket += `IVA:      ${totalIva.toFixed(2).padStart(17)} €\n`;
    ticket += `TOTAL:    ${totalConIva.toFixed(2).padStart(17)} €\n`;
    ticket += '================================\n';
    ticket += 'Gracias por su visita\n';
    ticket += '================================\n';

    return ticket;
  }
}
