import { Injectable } from '@angular/core';
import { forkJoin } from 'rxjs';
import { ProductService, Product } from '../../../services/api/product.service';
import { FamilyService, Family } from '../../../services/api/family.service';
import { TaxService, Tax } from '../../../services/api/tax.service';
import { DataCacheService } from '../../../services/shared/data-cache.service';
import { AuthService } from '../../../services/auth/auth.service';

export interface ProductsLoadResult {
    products: Product[];
    families: Family[];
    taxes: Tax[];
}

@Injectable({
    providedIn: 'root'
})
export class ProductLoaderService {

    constructor(
        private productService: ProductService,
        private familyService: FamilyService,
        private taxService: TaxService,
        private dataCacheService: DataCacheService,
        private authService: AuthService,
    ) {}

    cargarProductos(
        familiasActuales: Family[],
        taxesActuales: Tax[],
        onSuccess: (result: ProductsLoadResult) => void,
        onError: () => void
    ) {
        const userRestaurantId = this.authService.getUserData()?.restaurant_id;
        const requests: any = {};

        if (familiasActuales.length === 0) {
            requests.families = this.familyService.getFamilies();
        }

        if (taxesActuales.length === 0) {
            requests.taxes = this.taxService.getTaxes();
        }

        if (Object.keys(requests).length > 0) {
            forkJoin(requests).subscribe({
                next: (responses: any) => {
                    const families = responses.families
                        ? this.parseFamilies(responses.families, userRestaurantId)
                        : familiasActuales;

                    const taxes = responses.taxes
                        ? this.parseTaxes(responses.taxes, userRestaurantId)
                        : taxesActuales;

                    this.cargarProductosData(userRestaurantId, families, taxes, onSuccess, onError);
                },
                error: () => {
                    console.error('Error cargando dependencias');
                    this.cargarProductosData(userRestaurantId, familiasActuales, taxesActuales, onSuccess, onError);
                }
            });
        } else {
            this.cargarProductosData(userRestaurantId, familiasActuales, taxesActuales, onSuccess, onError);
        }
    }

    private parseFamilies(response: any, userRestaurantId: number | undefined): Family[] {
        let families: any[] = [];

        if (Array.isArray(response)) {
            families = response;
        } else if (response?.Family && Array.isArray(response.Family)) {
            families = response.Family;
        } else if (response?.data && Array.isArray(response.data)) {
            families = response.data;
        }

        families = families.map(f => {
            if (!f.database_id && f.id && !isNaN(Number(f.id))) {
                return { ...f, database_id: Number(f.id) };
            }
            return f;
        });

        const result = userRestaurantId
            ? families.filter(f => f.restaurant_id === userRestaurantId)
            : families;

        this.dataCacheService.setFamiliesCache(result);
        return result;
    }

    private parseTaxes(response: any, userRestaurantId: number | undefined): Tax[] {
        let taxes: any[] = [];

        if (Array.isArray(response)) {
            taxes = response;
        } else if (response?.tax && Array.isArray(response.tax)) {
            taxes = response.tax;
        } else if (response?.Tax && Array.isArray(response.Tax)) {
            taxes = response.Tax;
        } else if (response?.data && Array.isArray(response.data)) {
            taxes = response.data;
        }

        const result = userRestaurantId
            ? taxes.filter(t => t.restaurant_id === userRestaurantId)
            : taxes;

        this.dataCacheService.setTaxesCache(result);
        return result;
    }

    private cargarProductosData(
        userRestaurantId: number | undefined,
        families: Family[],
        taxes: Tax[],
        onSuccess: (result: ProductsLoadResult) => void,
        onError: () => void
    ) {
        this.productService.getProducts().subscribe({
            next: (response: any) => {
                let products: any[] = [];

                if (Array.isArray(response)) {
                    products = response;
                } else if (response?.products && Array.isArray(response.products)) {
                    products = response.products;
                } else if (response?.data && Array.isArray(response.data)) {
                    products = response.data;
                }

                const filtered = userRestaurantId
                    ? products.filter(p => p.restaurant_id === userRestaurantId)
                    : products;

                this.dataCacheService.setProductsCache(filtered);
                onSuccess({ products: filtered, families, taxes });
            },
            error: () => {
                console.error('Error cargando productos');
                onError();
            }
        });
    }
}