import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';

export interface ZoneDeleted {
  zoneId: string;
}

@Injectable({ providedIn: 'root' })
export class ZoneStateService {
  private zoneDeleted$ = new BehaviorSubject<ZoneDeleted | null>(null);

  getZoneDeleted$(): Observable<ZoneDeleted | null> {
    return this.zoneDeleted$.asObservable();
  }

  notifyZoneDeleted(zoneId: string): void {
    this.zoneDeleted$.next({ zoneId });
  }
}
