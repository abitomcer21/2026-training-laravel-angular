import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';
import { Family } from '../api/family.service';

export interface FamilyStatusChange {
  familyId: string;
  active: boolean;
}

export interface FamilyDeleted {
  familyId: string;
}

@Injectable({ providedIn: 'root' })
export class FamilyStateService {
  private familyStatusChange$ = new BehaviorSubject<FamilyStatusChange | null>(null);
  private familyDeleted$ = new BehaviorSubject<FamilyDeleted | null>(null);
  private familyCreated$ = new BehaviorSubject<Family | null>(null);

  getFamilyStatusChange$(): Observable<FamilyStatusChange | null> {
    return this.familyStatusChange$.asObservable();
  }

  notifyFamilyStatusChange(familyId: string, active: boolean): void {
    this.familyStatusChange$.next({ familyId, active });
  }

  getFamilyDeleted$(): Observable<FamilyDeleted | null> {
    return this.familyDeleted$.asObservable();
  }

  notifyFamilyDeleted(familyId: string): void {
    this.familyDeleted$.next({ familyId });
  }

  getFamilyCreated$(): Observable<Family | null> {
    return this.familyCreated$.asObservable();
  }

  notifyFamilyCreated(family: Family): void {
    this.familyCreated$.next(family);
  }
}
