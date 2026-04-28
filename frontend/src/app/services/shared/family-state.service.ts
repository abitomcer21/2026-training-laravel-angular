import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';

export interface FamilyStatusChange {
  familyId: string;
  active: boolean;
}

@Injectable({ providedIn: 'root' })
export class FamilyStateService {
  private familyStatusChange$ = new BehaviorSubject<FamilyStatusChange | null>(null);

  getFamilyStatusChange$(): Observable<FamilyStatusChange | null> {
    return this.familyStatusChange$.asObservable();
  }

  notifyFamilyStatusChange(familyId: string, active: boolean): void {
    this.familyStatusChange$.next({ familyId, active });
  }
}
