import { Family } from '../../../services/api/family.service';
import { Tax } from '../../../services/api/tax.service';
import { ProductCreateForm } from '../forms/product-create.form';

export interface ValidationResult {
    valid: boolean;
    error?: string;
}

export function validateNewProductForm(
    form: ProductCreateForm,
    families: Family[],
    taxes: Tax[],
): ValidationResult {
    if (!families?.length)
        return {
        valid: false,
        error: 'Debes crear al menos una familia antes de crear productos.',
    };

    if (!taxes?.length)
        return {
        valid: false,
        error: 'Debes crear al menos un impuesto antes de crear productos.',
        };

    if (!form.name.trim())
        return { valid: false, error: 'El nombre del producto es obligatorio' };

    if (!form.family_id?.trim())
        return { valid: false, error: 'Debe seleccionar una familia' };

    if (!form.tax_id?.trim())
        return { valid: false, error: 'Debe seleccionar un impuesto' };

    const price = Number(form.price);
    const stock = Number(form.stock);

    if (isNaN(price) || price < 0)
        return { valid: false, error: 'El precio no es válido' };

    if (isNaN(stock) || stock < 0)
        return { valid: false, error: 'El stock no es válido' };

    if (!families.find((f) => f.id?.toString() === form.family_id))
        return { valid: false, error: 'La familia seleccionada no es válida' };

    if (!taxes.find((t) => t.id === form.tax_id))
        return { valid: false, error: 'El impuesto seleccionado no es válido' };

    return { valid: true };
}
