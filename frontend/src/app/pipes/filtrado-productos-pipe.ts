import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
  name: 'filtradoProductos',
  standalone: true
})
export class FiltradoProductosPipe implements PipeTransform {
  
  transform(productos: any[], filtro: string): any[] {
    if (!productos || !Array.isArray(productos)) {
      return [];
    }
    
    if (!filtro || filtro.trim() === '') {
      return productos;
    }
    
    const termino = filtro.toLowerCase().trim();
    
    return productos.filter(producto => 
      producto.name && producto.name.toLowerCase().includes(termino)
    );
  }
}