import { Injectable } from '@angular/core';
import { Subscription } from 'rxjs';
import { Product } from '../../../services/api/product.service';
import { FamilyService, Family } from '../../../services/api/family.service';
import { TaxService, Tax } from '../../../services/api/tax.service';
import { FamilyStateService } from '../../../services/shared/family-state.service';
import { DataCacheService } from '../../../services/shared/data-cache.service';
import { AuthService } from '../../../services/auth/auth.service';

export interface ProductStateCallbacks {
    onFamilyStatusChange: (familyId: string, active: boolean) => void;
    onFamilyDeleted: (familyId: string) => void;
    onFamilyCreated: (family: Family) => void;
    onTaxCreated: (tax: Tax) => void;
    onError: (message: string) => void;
}

@Injectable({
    providedIn: 'root'
})
export class ProductStateService {

    constructor(
        private familyService: FamilyService,
        private taxService: TaxService,
        private familyStateService: FamilyStateService,
        private dataCacheService: DataCacheService,
        private authService: AuthService,
    ) {}

    suscribirseACambiosFamilia(callbacks: ProductStateCallbacks): Subscription[] {
        const sub1 = this.familyStateService.getFamilyStatusChange$().subscribe(change => {
            if (change) {
                callbacks.onFamilyStatusChange(change.familyId, change.active);
            }
        });

        const sub2 = this.familyStateService.getFamilyDeleted$().subscribe(deleted => {
            if (deleted) {
                callbacks.onFamilyDeleted(deleted.familyId);
            }
        });

        return [sub1, sub2];
    }

    eliminarProductosPorFamiliaEliminada(
        familyId: string,
        products: Product[],
        productosFiltrados: Product[]
    ): { products: Product[], productosFiltrados: Product[] } {
        const updatedProducts = products.filter(p => p.family_id?.toString() !== familyId);
        const updatedFiltrados = productosFiltrados.filter(p => p.family_id?.toString() !== familyId);

        this.dataCacheService.setProductsCache(updatedProducts);

        return { products: updatedProducts, productosFiltrados: updatedFiltrados };
    }

    actualizarEstadoProductosPorFamilia(
        familyId: string,
        newActive: boolean,
        products: Product[],
        productosFiltrados: Product[]
    ): { products: Product[], productosFiltrados: Product[] } {
        const update = (list: Product[]) => list.map(product =>
            product.family_id === familyId ? { ...product, active: newActive } : product
        );

        return {
            products: update(products),
            productosFiltrados: update(productosFiltrados)
        };
    }

    crearFamiliaRapida(
        name: string,
        onSuccess: (family: Family) => void,
        onError: () => void
    ) {
        const restaurantId = this.authService.getUserData()?.restaurant_id;

        if (!restaurantId) {
            console.error('No se pudo obtener el restaurant_id');
            return;
        }

        const payload = {
            name: name.trim(),
            active: true,
            restaurant_id: restaurantId
        };

        this.familyService.createFamily(payload).subscribe({
            next: (response: any) => {
                const newFamily: Family = {
                    id: response?.id ?? response?.uuid,
                    uuid: response?.uuid,
                    name: response?.name,
                    active: response?.active ?? true,
                    restaurant_id: response?.restaurant_id
                };

                this.familyStateService.notifyFamilyCreated(newFamily);
                this.familyService.invalidateFamiliesCache();
                onSuccess(newFamily);
            },
            error: () => {
                console.error('Error al crear familia');
                onError();
            }
        });
    }

    crearImpuestoRapido(
        name: string,
        percentage: number,
        onSuccess: (tax: Tax) => void,
        onError: () => void
    ) {
        const restaurantId = this.authService.getUserData()?.restaurant_id;

        if (!restaurantId) {
            console.error('No se pudo obtener el restaurant_id');
            return;
        }

        const payload = {
            name: name.trim(),
            percentage,
            restaurant_id: restaurantId
        };

        this.taxService.createTax(payload).subscribe({
            next: (response: any) => {
                const newTax: Tax = {
                    id: response?.id ?? response?.uuid,
                    name: response?.name,
                    percentage: response?.percentage,
                    restaurant_id: response?.restaurant_id
                };

                this.dataCacheService.setTaxesCache([]);
                this.taxService.invalidateTaxesCache();
                onSuccess(newTax);
            },
            error: () => {
                console.error('Error al crear impuesto');
                onError();
            }
        });
    }
}