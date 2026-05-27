import {
  Component,
  OnInit,
  Output,
  EventEmitter,
  ChangeDetectorRef,
} from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { inject } from '@angular/core';
import { BehaviorSubject } from 'rxjs';
import {
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
  IonButton as IonButtonModal,
  ToastController,
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
import { environment } from '../../../../../environments/environment';

import { ProductService } from '../../../../services/api/product.service';
import { FamilyService } from '../../../../services/api/family.service';
import { TableService } from '../../../../services/api/table.service';
import { TaxService } from '../../../../services/api/tax.service';
import { SesiónCamareroService } from '../../../../services/sesion-camarero.service';
import {
  OrderStateService,
  CurrentOrder,
  OrderItem,
} from '../../../../services/order-state.service';
import { AuthService } from '../../../../services/auth/auth.service';
import { OrderActionsService } from '../../../../services/order-actions.service';
import { CobroService, TipoCobro } from '../../../../services/cobro.service';
import { TicketService } from '../../../../services/ticket.service';

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
  selector: 'app-panel-productos',
  templateUrl: './panel-productos.component.html',
  styleUrls: ['./panel-productos.component.scss'],
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
  familias: { id: string; name: string }[] = [];
  familiaSeleccionada: string | null = null;
  filtroNombre = '';
  cargando = false;
  taxMap: Map<string, number> = new Map();

  currentOrder: CurrentOrder = {
    table: null,
    user: null,
    items: [],
    total: 0,
    comensales: 1,
  };
  estadoPedido: EstadoPedido = 'editando';
  pedidoInicialEnviado = false;
  itemsEnviadosACocina: OrderItem[] = [];

  mostrarModalCobro = false;
  tipoCobro: TipoCobro = 'completo';
  numeroComensales = 2;
  totalPorPersona = 0;
  articulosSeleccionados: Record<string, boolean> = {};
  metodoSeleccionado: 'efectivo' | 'tarjeta' | 'mixto' | null = 'tarjeta';
  totalsMetodoPago = { efectivo: 0, tarjeta: 0 };
  articulosPagados: Record<string, boolean> = {};
  totalPagado = 0;
  totalPorPagar = 0;

  mostrarModalTicket = false;
  ticketParaImprimir$ = new BehaviorSubject<string>('');

  get ticketParaImprimir(): string {
    return this.ticketParaImprimir$.value;
  }
  Math = Math;

  get productosFiltrados(): Product[] {
    const termino = this.filtroNombre.toLowerCase().trim();
    if (!termino) return this.productos;

    return this.productos.filter((p) =>
      p.name?.toLowerCase().includes(termino),
    );
  }

  get numeroTicketActual(): string | null {
    return this.ticketService.getNumeroTicketActual();
  }

  constructor(
    private productService: ProductService,
    private orderStateService: OrderStateService,
    private authService: AuthService,
    private familyService: FamilyService,
    private tableService: TableService,
    private taxService: TaxService,
    private toastController: ToastController,
    private changeDetector: ChangeDetectorRef,
    private orderActionsService: OrderActionsService,
    private cobroService: CobroService,
    private ticketService: TicketService,
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
    this.suscribirseAOrden();
  }

  readonly sesionCamarero = inject(SesiónCamareroService);

  async mostrarToast(mensaje: string, color = 'success', duracion = 3000) {
    const toast = await this.toastController.create({
      message: mensaje,
      duration: duracion,
      position: 'top',
      color,
      buttons: [{ text: 'Cerrar', role: 'cancel' }],
    });
    await toast.present();
  }

  getImageUrl(imageSrc: string | undefined): string {
    if (!imageSrc) return '';
    const apiUrl = environment.apiUrl.replace('/api', '');
    return `${apiUrl}/storage/${imageSrc}`;
  }

  cargarProductos() {
    this.cargando = true;
    const restaurantId = this.authService.getUserData()?.restaurant_id;
    if (!restaurantId) {
      this.cargando = false;
      return;
    }

    this.taxService.getTaxes().subscribe({
      next: (res: any) => {
        const taxes: any[] =
          res.tax ??
          res.taxes ??
          res.Tax ??
          res.data ??
          (Array.isArray(res) ? res : []);
        this.taxMap.clear();
        taxes.forEach((t: any) => {
          this.taxMap.set(t.id?.toString(), t.percentage || 0);
          if (t.uuid) this.taxMap.set(t.uuid, t.percentage || 0);
        });
        this.cargarProductosConTaxes(restaurantId);
      },
      error: () => {
        this.cargando = false;
      },
    });
  }

  private cargarProductosConTaxes(restaurantId: string) {
    this.productService.getProducts().subscribe({
      next: (res: any) => {
        this.productosOriginales = (res.products || [])
          .filter((p: any) => p.restaurant_id === restaurantId)
          .map((p: any) => ({
            ...p,
            price: p.price / 100,
            iva: this.taxMap.get(p.tax_id?.toString()) || 0,
          }));
        this.cargarFamilias(restaurantId);
        this.cargando = false;
      },
      error: () => {
        this.cargando = false;
        this.mostrarToast('Error al cargar productos', 'danger');
      },
    });
  }

  cargarFamilias(restaurantId: string) {
    this.familyService.getFamilies().subscribe({
      next: (res: any) => {
        const todas: any[] = res?.Family ?? res?.families ?? [];
        const activas = todas.filter(
          (f: any) => f.restaurant_id === restaurantId && f.active,
        );
        const familyMap = new Map(activas.map((f: any) => [f.id, f.name]));

        this.productosOriginales = this.productosOriginales.map((p) => ({
          ...p,
          family_name: p.family_id
            ? familyMap.get(p.family_id) || 'Sin familia'
            : 'Sin familia',
        }));

        this.productosPorFamilia.clear();
        this.productosOriginales.forEach((p) => {
          if (p.family_id) {
            if (!this.productosPorFamilia.has(p.family_id))
              this.productosPorFamilia.set(p.family_id, []);
            this.productosPorFamilia.get(p.family_id)!.push(p);
          }
        });

        this.familias = activas.map((f: any) => ({ id: f.id, name: f.name }));
        if (this.familias.length > 0 && !this.familiaSeleccionada)
          this.seleccionarFamilia(this.familias[0].id);
      },
      error: () => this.mostrarToast('Error al cargar categorías', 'danger'),
    });
  }

  seleccionarFamilia(familiaId: string) {
    this.familiaSeleccionada = familiaId;
    this.filtroNombre = '';
    this.productos = this.productosPorFamilia.get(familiaId) || [];
  }

  getNombreFamilia(): string {
    return (
      this.familias.find((f) => f.id === this.familiaSeleccionada)?.name || ''
    );
  }

  suscribirseAOrden() {
    this.orderStateService.getCurrentOrder().subscribe({
      next: (order) => {
        if (!order?.items?.length) {
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
    this.ticketService.resetNumeroTicket();
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
    this.totalPorPersona = 0;
    this.articulosSeleccionados = {};
    this.filtroNombre = '';

    if (this.familiaSeleccionada) {
      this.seleccionarFamilia(this.familiaSeleccionada);
    } else if (this.familias.length > 0) {
      this.seleccionarFamilia(this.familias[0].id);
    }
  }

  calcularTotalesPendientes() {
    let pagado = 0;
    let porPagar = 0;
    let total = 0;
    this.currentOrder.items.forEach((item) => {
      total += item.total;
      if (this.articulosPagados[item.productId]) pagado += item.total;
      else porPagar += item.total;
    });
    this.totalPagado = pagado;
    this.totalPorPagar = porPagar;
    if (this.currentOrder.total !== total) this.currentOrder.total = total;
  }

  esProductoNuevo(productId: string): boolean {
    return !this.itemsEnviadosACocina.some((e) => e.productId === productId);
  }

  esProductoPagado(productId: string): boolean {
    return this.articulosPagados[productId] === true;
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
    this.orderStateService.addItem({
      productId: producto.id,
      productName: producto.name,
      quantity: 1,
      price: producto.price,
      total: producto.price,
      iva: producto.iva || 0,
    });
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
    if (item)
      this.orderStateService.updateItemQuantity(productId, item.quantity + 1);
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
    if (!item) return;
    item.quantity === 1
      ? this.eliminarProducto(productId)
      : this.orderStateService.updateItemQuantity(productId, item.quantity - 1);
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
    if (!this.currentOrder.items.length) {
      this.mostrarToast('No hay productos para limpiar', 'warning', 2000);
      return;
    }
    const tableId = this.currentOrder.table
      ? String(this.currentOrder.table.id)
      : null;
    this.orderActionsService.limpiarOrden(tableId);
    this.resetearEstadoPedido();
    this.volverAMesas();
  }

  async enviarACocina() {
    if (!this.currentOrder.items.length) return;
    const restaurantId = this.authService.getUserData()?.restaurant_id;

    if (!this.pedidoInicialEnviado) {
      try {
        const orderId = await this.orderActionsService.crearPedidoInicial(
          this.currentOrder,
          restaurantId,
        );
        this.orderStateService.setOrderId(orderId);
        this.itemsEnviadosACocina = [...this.currentOrder.items];
        this.pedidoInicialEnviado = true;
        this.estadoPedido = 'confirmado';
        this.orderStateService.setPedidoInicialEnviado(
          true,
          this.itemsEnviadosACocina,
        );
        this.mostrarToast(
          `Pedido enviado a cocina — Mesa: ${this.currentOrder.table?.name}`,
          'success',
        );
      } catch {
        this.mostrarToast('Error al enviar pedido', 'danger');
      }
    } else {
      const nuevosItems = this.orderActionsService.calcularNuevosItems(
        this.currentOrder.items,
        this.itemsEnviadosACocina,
      );
      if (!nuevosItems.length) {
        this.mostrarToast(
          'No hay nuevos productos para enviar',
          'warning',
          2000,
        );
        return;
      }

      const orderId = this.orderStateService.getOrderIdValue();
      if (!orderId) {
        this.mostrarToast('ID de pedido no disponible', 'danger');
        return;
      }

      try {
        await this.orderActionsService.anadirLineas(
          orderId,
          nuevosItems,
          this.currentOrder.user!.id,
        );
        this.itemsEnviadosACocina = [...this.currentOrder.items];
        this.orderStateService.setPedidoInicialEnviado(
          true,
          this.itemsEnviadosACocina,
        );
        const total = nuevosItems.reduce((s, i) => s + i.quantity, 0);
        this.mostrarToast(`${total} productos añadidos a cocina`, 'success');
      } catch {
        this.mostrarToast('Error al añadir productos', 'danger');
      }
    }
  }

  volverAEditar() {
    this.estadoPedido = 'editando';
    this.mostrarToast('Modo edición activado', 'primary', 2000);
  }

  abrirModalCobro() {
    if (!this.currentOrder.items.length) {
      this.mostrarToast('No hay productos en el pedido', 'danger', 2000);
      return;
    }
    this.currentOrder.items.forEach((item) => {
      this.articulosSeleccionados[item.productId] =
        !this.articulosPagados[item.productId];
    });
    this.numeroComensales = 2;
    this.tipoCobro = 'completo';
    this.calculartotalPorPersona();
    this.mostrarModalCobro = true;
  }

  calculartotalPorPersona() {
    this.totalPorPersona = this.cobroService.calculartotalPorPersona(
      this.currentOrder,
      this.articulosPagados,
      this.numeroComensales,
    );
  }

  getTotalPendiente(): number {
    return this.cobroService.getTotalPendiente(
      this.currentOrder,
      this.articulosPagados,
    );
  }

  getTotalSeleccionado(): number {
    return this.cobroService.getTotalSeleccionado(
      this.currentOrder,
      this.articulosSeleccionados,
      this.articulosPagados,
    );
  }

  aumentarComensales() {
    this.orderStateService.setComensales(
      (this.currentOrder.comensales || 1) + 1,
    );
  }

  disminuirComensales() {
    const actual = this.currentOrder.comensales || 1;
    if (actual > 1) this.orderStateService.setComensales(actual - 1);
  }

  async confirmarCobro() {
    if (!this.metodoSeleccionado) {
      this.mostrarToast('Selecciona un método de pago', 'warning', 2000);
      return;
    }

    if (this.metodoSeleccionado === 'mixto') {
      const totalMixto =
        this.totalsMetodoPago.efectivo + this.totalsMetodoPago.tarjeta;
      const pendiente = this.getTotalPendiente();
      if (Math.abs(totalMixto - pendiente) > 0.01) {
        this.mostrarToast(
          `Total mixto (${totalMixto.toFixed(2)}€) no coincide con pendiente (${pendiente.toFixed(2)}€)`,
          'warning',
          3000,
        );
        return;
      }
    }

    const { articulosPagados } = this.cobroService.aplicarCobro(
      this.tipoCobro,
      this.currentOrder,
      this.articulosPagados,
      this.articulosSeleccionados,
      this.totalPorPersona,
      this.numeroComensales,
    );
    this.articulosPagados = articulosPagados;

    this.calcularTotalesPendientes();
    this.orderStateService.setArticulosPagados(this.articulosPagados);
    if (this.totalPorPagar === 0) this.estadoPedido = 'cobrado';

    const orderId = this.orderStateService.getOrderIdValue();
    if (this.currentOrder.user?.id && orderId) {
      try {
        await this.cobroService.registrarVenta(
          orderId,
          this.currentOrder.user.id,
        );
        this.mostrarToast('Venta registrada correctamente', 'success', 2000);
      } catch {
        this.mostrarToast('Error al registrar la venta', 'danger');
      }
    }

    this.mostrarModalCobro = false;

    setTimeout(() => {
      const orderActual = this.orderStateService.getCurrentOrderValue();
      if (orderActual?.items) this.currentOrder = orderActual;
      this.calcularTotalesPendientes();
      this.pedidoInicialEnviado =
        this.orderStateService.getPedidoInicialEnviadoValue();
      this.itemsEnviadosACocina =
        this.orderStateService.getItemsEnviadosACocinaValue();
      this.articulosPagados = this.orderStateService.getArticulosPagadosValue();

      const ticket = this.ticketService.generarTicket(this.currentOrder);
      this.ticketParaImprimir$.next(ticket);
      this.mostrarModalTicket = true;
    }, 300);
  }

  verTicketProvisional() {
    if (!this.currentOrder.items.length) return;
    this.ticketParaImprimir$.next(
      this.ticketService.generarTicket(this.currentOrder, 'PROVISIONAL'),
    );
    this.mostrarModalTicket = true;
  }

  imprimirTicketDesdeModal() {
    this.mostrarModalTicket = false;
    const tableId = this.currentOrder.table
      ? String(this.currentOrder.table.id)
      : null;
    setTimeout(() => {
      this.orderActionsService.limpiarOrden(tableId);
      this.resetearEstadoPedido();
      setTimeout(() => this.volverAMesas(), 500);
    }, 500);
  }

  cerrarModalTicket() {
    this.mostrarModalTicket = false;
    this.articulosPagados = this.orderStateService.getArticulosPagadosValue();
    this.calcularTotalesPendientes();
    if (this.totalPorPagar === 0) {
      const tableId = this.currentOrder.table
        ? String(this.currentOrder.table.id)
        : null;
      setTimeout(() => {
        this.orderActionsService.limpiarOrden(tableId);
        this.resetearEstadoPedido();
        this.volverAMesas();
      }, 500);
    }
  }

  volverAMesas() {
    this.resetearEstadoPedido();
    this.tableService.invalidateTablesCache();
    this.cambiarVista.emit('mesas');
  }

  nuevoPedido() {
    const tableId = this.currentOrder.table
      ? String(this.currentOrder.table.id)
      : null;
    this.orderActionsService.limpiarOrden(tableId);
    this.resetearEstadoPedido();
    this.volverAMesas();
  }
}
