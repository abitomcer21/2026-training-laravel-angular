
export interface ProductCreateForm {
    name: string;
    family_id: string;
    tax_id: string;
    price: number;
    stock: number;
    image_src: string;
    active: boolean | string;
}

export function createEmptyProductForm(): ProductCreateForm {
        return {
            name: '',
            family_id: '',
            tax_id: '',
            price: 0,
            stock: 0,
            image_src: '',
            active: true,
        };
    }