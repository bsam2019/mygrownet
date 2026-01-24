/**
 * Duplicate Detection Composable for Ubumi
 * Checks for potential duplicate persons when adding new family members
 */

import { ref } from 'vue';
import axios from 'axios';

export interface DuplicatePerson {
    id: string;
    name: string;
    photo_url: string | null;
    age: number | null;
    date_of_birth: string | null;
    similarity_score: number;
}

export interface DuplicateCheckData {
    name: string;
    date_of_birth?: string;
    approximate_age?: number;
}

export function useDuplicateDetection(familyId: string) {
    const checking = ref(false);
    const duplicates = ref<DuplicatePerson[]>([]);
    const error = ref<string | null>(null);

    const checkForDuplicates = async (data: DuplicateCheckData): Promise<DuplicatePerson[]> => {
        if (!data.name || data.name.length < 2) {
            duplicates.value = [];
            return [];
        }

        checking.value = true;
        error.value = null;

        try {
            const response = await axios.post(
                `/ubumi/families/${familyId}/persons/check-duplicates`,
                data
            );

            duplicates.value = response.data.duplicates || [];
            return duplicates.value;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to check for duplicates';
            console.error('Duplicate check error:', err);
            return [];
        } finally {
            checking.value = false;
        }
    };

    const clearDuplicates = () => {
        duplicates.value = [];
        error.value = null;
    };

    return {
        checking,
        duplicates,
        error,
        checkForDuplicates,
        clearDuplicates,
    };
}
