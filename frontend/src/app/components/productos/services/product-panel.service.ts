import { Injectable } from '@angular/core';
import { Product } from '../../../services/api/product.service';
import { ProductCreateForm } from '../forms/product-create.form';
import { parseActiveValue } from '../utils/product.utils';

@Injectable({
    providedIn: 'root'
})
export class ProductPanelService {

    buildCreatePayload(form: ProductCreateForm, restaurantId: number): any {
        return {
            name: form.name.trim(),
            family_id: form.family_id,
            tax_id: form.tax_id,
            price: Math.round(Number(form.price) * 100),
            stock: Number(form.stock),
            image_src: form.image_src.trim() || null,
            active: parseActiveValue(form.active),
            restaurant_id: Number(restaurantId),
        };
    }

    mapResponseToProduct(response: any, form: ProductCreateForm, restaurantId: any): Product {
        return {
            id: response?.id ?? response?.uuid,
            uuid: response?.uuid,
            name: response?.name ?? form.name.trim(),
            family_id: response?.family_id ?? form.family_id,
            tax_id: response?.tax_id ?? form.tax_id,
            price: response?.price,
            stock: response?.stock ?? form.stock,
            image_src: response?.image_src ?? form.image_src.trim(),
            active: response?.active ?? parseActiveValue(form.active),
            restaurant_id: response?.restaurant_id ?? restaurantId,
        };
    }
}