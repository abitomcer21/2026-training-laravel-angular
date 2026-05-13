import { Family } from '../../../services/api/family.service';

export function parseActiveValue(value: boolean | string): boolean {
    if (typeof value === 'string') return value === 'true' || value === '1';
    return Boolean(value);
}

export function obtenerNombreFamilia(familyId: string, familias: Family[]): string {
    if (!familyId) return 'Sin familia';
    return familias.find(f => f.id?.toString() === familyId)?.name ?? `Familia ${familyId}`;
}