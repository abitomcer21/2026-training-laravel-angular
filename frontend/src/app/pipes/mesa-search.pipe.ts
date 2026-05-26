import { Pipe, PipeTransform } from '@angular/core';
import { Table } from '../services/api/table.service';

@Pipe({
  name: 'mesaSearch',
  standalone: true,
})
export class MesaSearchPipe implements PipeTransform {
  transform(mesas: Table[], query: string): Table[] {
    if (!query?.trim()) return mesas;
    const q = query.toLowerCase();
    return mesas.filter((m) => m.name.toLowerCase().includes(q));
  }
}
