import { Injectable } from '@angular/core';

@Injectable({ providedIn: 'root' })
export class ComponentStateService {
  private loadedComponents = new Set<string>();

  isComponentLoaded(componentName: string): boolean {
    return this.loadedComponents.has(componentName);
  }

  markComponentLoaded(componentName: string): void {
    this.loadedComponents.add(componentName);
  }

  resetComponentState(componentName: string): void {
    this.loadedComponents.delete(componentName);
  }

  resetAllComponentStates(): void {
    this.loadedComponents.clear();
  }
}
