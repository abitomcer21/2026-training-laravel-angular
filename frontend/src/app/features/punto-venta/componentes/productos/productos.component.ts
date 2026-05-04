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
  documentTextOutline,
} from 'ionicons/icons';

import { ProductService } from '../../../../services/api/product.service';
import { FamilyService } from '../../../../services/api/family.service';
import { TableService } from '../../../../services/api/table.service';
import { TaxService } from '../../../../services/api/tax.service';
import { OrderStateService, CurrentOrder, OrderItem } from '../../../../services/order-state.service';
import { AuthService } from '../../../../services/auth/auth.service';

export interface Product {
  id: string;
  uuid: string;
  name: string;
  description?: string;
  price: number;
  iva?: number;  // IVA en porcentaje
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

  // Método de pago
  metodoSeleccionado: 'efectivo' | 'tarjeta' | 'mixto' | null = 'tarjeta';
  montosMetodoPago = {
    efectivo: 0,
    tarjeta: 0
  };

  articulosPagados: { [key: string]: boolean } = {};
  totalPagado = 0;
  totalPorPagar = 0;

  mostrarModalTicket = false;
  ticketParaImprimir$ = new BehaviorSubject<string>('');

  get ticketParaImprimir(): string {
    return this.ticketParaImprimir$.value;
  }

  // Número de ticket
  numeroTicketActual: string | null = null;

  // Mapa de tax_id -> porcentaje de IVA
  taxMap: Map<string, number> = new Map();

  constructor(
    private productService: ProductService,
    private orderStateService: OrderStateService,
    private authService: AuthService,
    private familyService: FamilyService,
    private tableService: TableService,
    private taxService: TaxService,
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
      arrowBackOutline,
      documentTextOutline,
    });
    
    // Exponer método de debug en la consola para inspeccionar localStorage
    (window as any).debugPedidos = () => {
      console.log('📂 Llamando debugLocalStorage()...');
      this.orderStateService.debugLocalStorage();
    };
    
    // Exponer método de debug para ver estado de bloqueos
    (window as any).debugBlockStatus = () => {
      console.log('🔐 Llamando debugBlockStatus()...');
      this.orderStateService.debugBlockStatus();
    };
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
    console.log('\n🔙 [volverAMesas] Volviendo a mesas - limpiando estado local');
    
    // Limpiar el estado local COMPLETO antes de volver
    this.resetearEstadoPedido();
    console.log('✓ Estado local reseteado');
    
    // Invalidar cache para que MesasComponent cargue datos frescos
    this.tableService.invalidateTablesCache();
    console.log('✓ Cache de mesas invalidado');
    
    // Emitir evento para cambiar de vista
    this.cambiarVista.emit('mesas');
    console.log('✓ Evento cambiarVista emitido');
    
    console.log('✅ Listo para nueva mesa\n');
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

    // Primero cargar los taxes para crear el mapa
    this.taxService.getTaxes().subscribe({
      next: (taxesResponse: any) => {
        console.log('=== RESPUESTA CRUDA getTaxes() ===');
        console.log('Tipo:', typeof taxesResponse);
        console.log('Es array:', Array.isArray(taxesResponse));
        console.log('Contenido completo:', taxesResponse);
        console.log('Claves principales:', Object.keys(taxesResponse));
        
        // Intentar extraer de múltiples formas
        let todosLosTaxes: any[] = [];
        
        if (Array.isArray(taxesResponse)) {
          todosLosTaxes = taxesResponse;
          console.log('✓ Era un array directamente');
        } else if (taxesResponse.tax) {
          todosLosTaxes = taxesResponse.tax;
          console.log('✓ Extraído de response.tax');
        } else if (taxesResponse.taxes) {
          todosLosTaxes = taxesResponse.taxes;
          console.log('✓ Extraído de response.taxes');
        } else if (taxesResponse.Tax) {
          todosLosTaxes = taxesResponse.Tax;
          console.log('✓ Extraído de response.Tax');
        } else if (taxesResponse.data) {
          todosLosTaxes = taxesResponse.data;
          console.log('✓ Extraído de response.data');
        } else {
          console.warn('✗ No se encontró lista de taxes en ninguna propiedad');
          console.warn('Propiedades disponibles:', Object.keys(taxesResponse));
        }
        
        console.log('Taxes finales:', todosLosTaxes);
        console.log('Cantidad:', todosLosTaxes.length);
        
        // Crear el mapa de tax_id -> percentage
        this.taxMap.clear();
        todosLosTaxes.forEach((tax: any) => {
          console.log(`Tax encontrado:`, tax);
          this.taxMap.set(tax.id?.toString(), tax.percentage || 0);
          if (tax.uuid) {
            this.taxMap.set(tax.uuid?.toString(), tax.percentage || 0);
          }
        });
        
        console.log('Tax Map final:', Object.fromEntries(this.taxMap));
        
        // Ahora cargar los productos
        this.cargarProductosConTaxes(restaurantId);
      },
      error: (error) => {
        console.error('❌ Error al cargar taxes:', error);
        this.cargando = false;
      }
    });
  }

  private cargarProductosConTaxes(restaurantId: string) {
    this.productService.getProducts().subscribe({
      next: (productsResponse: any) => {
        const todosLosProductos = productsResponse.products || [];
        
        this.productosOriginales = todosLosProductos
          .filter((producto: any) => producto.restaurant_id === restaurantId)
          .map((producto: any) => {
            // Usar el mapa para obtener el IVA del tax_id
            const ivaFromMap = this.taxMap.get(producto.tax_id?.toString()) || 0;
            
            console.log(`Producto: "${producto.name}", tax_id: "${producto.tax_id}", IVA: ${ivaFromMap}%`);
            
            return {
              ...producto,
              price: producto.price / 100,
              iva: ivaFromMap
            };
          });
        
        console.log('Productos con IVA mapeado:', this.productosOriginales.slice(0, 3));
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
        console.log('\n📦 [suscribirseAorden] Orden recibida del servicio');
        console.log('   table.id:', order.table?.id, `(tipo: ${typeof order.table?.id})`);
        console.log('   user.id:', order.user?.id, `(tipo: ${typeof order.user?.id})`);
        console.log('   items.length:', order.items?.length || 0);
        
        if (order.items && order.items.length > 0) {
          console.log('   📋 ITEMS ENCONTRADOS EN LA ORDEN:');
          order.items.forEach((item, idx) => {
            console.log(`     ${idx + 1}. ${item.productName} x${item.quantity} = ${item.total}€`);
          });
        }
        
        // Si es una orden vacía (nueva mesa), resetear completamente
        if (!order || !order.items || order.items.length === 0) {
          console.log('🆕 Orden vacía - reseteando estado local');
          this.resetearEstadoPedido();
          // Asignar la referencia de table y user si los tiene
          if (order) {
            this.currentOrder.table = order.table;
            this.currentOrder.user = order.user;
          }
        } else {
          // Orden existente con items - cargar todos los datos
          console.log(`📋 Orden existente con ${order.items.length} items`);
          this.currentOrder = order;
          this.pedidoInicialEnviado = this.orderStateService.getPedidoInicialEnviadoValue();
          this.itemsEnviadosACocina = this.orderStateService.getItemsEnviadosACocinaValue();
          this.articulosPagados = this.orderStateService.getArticulosPagadosValue();
        }
        
        this.calcularTotalesPendientes();
        console.log(`✓ Estado sincronizado - Total: ${this.currentOrder.total}€\n`);
      }
    });
  }

  resetearEstadoPedido() {
    console.log('🔄 Reseteando estado del pedido...');
    
    // Resetear estado del pedido
    this.estadoPedido = 'editando';
    this.pedidoInicialEnviado = false;
    this.itemsEnviadosACocina = [];
    this.articulosPagados = {};
    this.totalPagado = 0;
    this.totalPorPagar = 0;
    this.numeroTicketActual = null;

    // Resetear carrito completo
    this.currentOrder = {
      table: null,
      user: null,
      items: [],
      total: 0,
    };

    // Resetear modales y flags de UI
    this.mostrarModalCobro = false;
    this.mostrarModalTicket = false;
    this.tipoCobro = 'completo';
    this.numeroComensales = 2;
    this.montoPorPersona = 0;
    this.articulosSeleccionados = {};
    this.filtroNombre = '';
    this.familiaSeleccionada = null;

    console.log('✓ Estado del pedido completamente reseteado');
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
      iva: producto.iva || 0,  // Incluir el IVA del producto
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
    this.currentOrder.items.forEach(item => {
      if (this.articulosSeleccionados[item.productId] && !this.articulosPagados[item.productId]) {
        total += item.total;
      }
    });
    return total;
  }

  // Exponer Math para usar en template
  Math = Math;

  getTotalPendiente(): number {
    return this.currentOrder.items
      .filter(item => !this.articulosPagados[item.productId])
      .reduce((sum, item) => sum + item.total, 0);
  }
  confirmarCobro() {
    // 1. Validar que hay método de pago seleccionado
    if (!this.metodoSeleccionado) {
      this.mostrarToast('Selecciona un método de pago', 'warning', 2000);
      return;
    }

    // 2. Validar pago mixto
    if (this.metodoSeleccionado === 'mixto') {
      const totalMixto = this.montosMetodoPago.efectivo + this.montosMetodoPago.tarjeta;
      const totalPendiente = this.getTotalPendiente();
      
      if (Math.abs(totalMixto - totalPendiente) > 0.01) {
        this.mostrarToast(
          `Total mixto (${totalMixto.toFixed(2)}€) no coincide con pendiente (${totalPendiente.toFixed(2)}€)`,
          'warning',
          3000
        );
        return;
      }
    }

    // 3. Procesar el cobro según el tipo seleccionado
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

    // 4. Mostrar mensaje del cobro y método
    let mensajeMetodo = '';
    switch (this.metodoSeleccionado) {
      case 'efectivo':
        mensajeMetodo = ' (Efectivo)';
        break;
      case 'tarjeta':
        mensajeMetodo = ' (Tarjeta)';
        break;
      case 'mixto':
        mensajeMetodo = ` (Mixto: ${this.montosMetodoPago.efectivo.toFixed(2)}€ + ${this.montosMetodoPago.tarjeta.toFixed(2)}€)`;
        break;
    }
    
    this.mostrarToast(mensaje + mensajeMetodo, 'success', 2000);
    this.mostrarModalCobro = false;

    // 5. Generar ticket inmediatamente
    setTimeout(() => {
      // Sincronizar TODOS los datos del ticket ANTES de generarlo
      const currentOrderFromService = this.orderStateService.getCurrentOrderValue();
      
      if (currentOrderFromService && currentOrderFromService.items) {
        this.currentOrder = currentOrderFromService;
      }
      
      // Sincronizar estados
      this.calcularTotalesPendientes();
      this.pedidoInicialEnviado = this.orderStateService.getPedidoInicialEnviadoValue();
      this.itemsEnviadosACocina = this.orderStateService.getItemsEnviadosACocinaValue();
      this.articulosPagados = this.orderStateService.getArticulosPagadosValue();
      
      // Asegurar que currentOrder.total está calculado
      if (!this.currentOrder.total || this.currentOrder.total === 0) {
        this.currentOrder.total = this.currentOrder.items.reduce((sum, item) => sum + item.total, 0);
      }
      
      // Generar número de ticket (solo si no se ha generado aún)
      if (!this.numeroTicketActual) {
        this.numeroTicketActual = this.generarNumeroTicket();
      }
      
      // Generar el ticket y guardarlo en el BehaviorSubject
      const ticket = this.generarTicket();
      this.ticketParaImprimir$.next(ticket);
      console.log('Ticket generado:', ticket);
      
      // Mostrar popup
      this.mostrarModalTicket = true;
    }, 300);
  }

  imprimirTicketDesdeModal() {
    console.log('\n\n🔴🔴🔴 === INICIANDO CICLO DE LIBERACIÓN ===');
    console.log('📋 Imprimiendo ticket y liberando mesa...');
    console.log('Datos actuales ANTES de limpiar:');
    console.log(`   currentOrder.table: ${this.currentOrder.table?.id} (${this.currentOrder.table?.name})`);
    console.log(`   currentOrder.user: ${this.currentOrder.user?.id} (${this.currentOrder.user?.name})`);
    console.log(`   currentOrder.items: ${this.currentOrder.items.length} items`);
    
    this.mostrarToast('Ticket impreso correctamente ✓', 'primary', 2000);
    this.mostrarModalTicket = false;

    // Limpiar SIEMPRE la orden y mesa cuando se imprime el ticket
    // No depender de cálculos de totalPorPagar que pueden fallar
    const tableId = this.currentOrder.table ? String(this.currentOrder.table.id) : null;
    const userId = this.currentOrder.user ? String(this.currentOrder.user.id) : null;

    console.log(`🗑️ Preparando limpieza - Mesa: ${tableId}, Usuario: ${userId}`);

    setTimeout(() => {
      console.log(`\n⏱️ Ejecutando limpieza con delay...`);
      
      // Limpiar la orden del servicio
      if (tableId && userId) {
        console.log(`📢 Llamando clearTableOrder(${tableId}, ${userId})`);
        this.orderStateService.clearTableOrder(tableId, userId);
        console.log('✓ clearTableOrder() completado');
      } else {
        console.log('📢 Llamando clearOrder() - no hay mesa/usuario válido');
        this.orderStateService.clearOrder();
        console.log('✓ clearOrder() completado');
      }

      // Resetear el estado local del componente
      console.log('🔄 Reseteando estado local del componente');
      this.resetearEstadoPedido();
      console.log('✓ resetearEstadoPedido() completado');

      // Mostrar confirmación y volver a mesas
      this.mostrarToast('Mesa liberada. Volviendo...', 'success', 1500);
      
      setTimeout(() => {
        console.log('↩️ Redirigiendo a mesas');
        this.volverAMesas();
        console.log('🔴🔴🔴 === FIN CICLO DE LIBERACIÓN ===\n');
      }, 500);
    }, 500);
  }

  cerrarModalTicket() {
    this.mostrarModalTicket = false;

    // Sincronizar datos actuales para saber si hay pendiente
    this.articulosPagados = this.orderStateService.getArticulosPagadosValue();
    this.calcularTotalesPendientes();

    console.log(`📋 Cerrando ticket - totalPorPagar: ${this.totalPorPagar.toFixed(2)}€`);

    // SOLO redirigir si NO hay nada pendiente de pago
    // Si el usuario cierra SIN pagar todo, permanece en el carrito
    if (this.totalPorPagar === 0) {
      console.log('✓ Todo pagado - limpiando mesa');
      
      const tableId = this.currentOrder.table ? String(this.currentOrder.table.id) : null;
      const userId = this.currentOrder.user ? String(this.currentOrder.user.id) : null;

      this.mostrarToast('Pedido completado. Volviendo a mesas...', 'success', 1500);
      
      setTimeout(() => {
        if (tableId && userId) {
          this.orderStateService.clearTableOrder(tableId, userId);
        } else {
          this.orderStateService.clearOrder();
        }
        this.resetearEstadoPedido();
        this.volverAMesas();
      }, 500);
    } else {
      console.log(`⚠️ Aún quedan por pagar: ${this.totalPorPagar.toFixed(2)}€`);
      // Permanecer en el carrito para continuar el pago
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

  verTicketProvisional() {
    if (this.currentOrder.items.length === 0) {
      this.mostrarToast('No hay productos en el pedido', 'danger', 2000);
      return;
    }
    const ticket = this.generarTicket('PROVISIONAL');
    this.ticketParaImprimir$.next(ticket);
    this.mostrarModalTicket = true;
  }

  private generarNumeroTicket(): string {
    // Obtener el último número de ticket del localStorage
    const ultimoNumero = localStorage.getItem('ultimoNumeroTicket');
    const numero = ultimoNumero ? parseInt(ultimoNumero) + 1 : 1;
    
    // Guardar el nuevo número
    localStorage.setItem('ultimoNumeroTicket', numero.toString());
    
    // Formatear: T-001, T-002, etc.
    return `T-${numero.toString().padStart(3, '0')}`;
  }


  private generarTicket(tipoTicket?: string): string {
    // Asegurar que tenemos los datos más recientes
    const order = this.currentOrder || this.orderStateService.getCurrentOrderValue();
    const items = (order && order.items) ? order.items : [];

    let ticket = '================================\n';
    ticket += '           RESTAURANTE\n';
    ticket += '================================\n';
    
    // Añadir número de ticket si es un ticket final
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
      items.forEach(item => {
        // Usar el IVA real del producto
        const ivaRate = (item.iva || 0) / 100;  // Convertir porcentaje a decimal
        
        // Calcular desglose de IVA
        const precioConIva = item.price;
        const precioSinIva = ivaRate > 0 ? precioConIva / (1 + ivaRate) : precioConIva;
        const ivaItem = precioConIva - precioSinIva;
        
        // Acumular totales
        subtotalSinIva += precioSinIva * item.quantity;
        totalIva += ivaItem * item.quantity;

        const nombre = item.productName.length > 12 ? item.productName.substring(0, 11) + '.' : item.productName;
        const ivaDisplay = (item.iva || 0).toString().padStart(3);
        
        // Formato alineado: Producto | Cant | Precio | IVA%
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