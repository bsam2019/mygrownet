import { ref, onMounted } from 'vue';
import axios from 'axios';

export interface StarterKitTier {
    key: string;
    name: string;
    price: number;
    storage_gb: number;
    earning_potential: number;
    description: string;
}

export interface CommissionRate {
    level: number;
    name: string;
    rate: number;
    positions: number;
}

export interface PerformanceTier {
    name: string;
    points: number;
    bonus: number;
}

export interface MatrixData {
    width: number;
    depth: number;
    total_capacity: number;
}

export interface ReferralBonus {
    rate: number;
    commission_base: number;
}

export interface PresentationData {
    tiers: StarterKitTier[];
    commission_rates: CommissionRate[];
    performance_tiers: PerformanceTier[];
    matrix: MatrixData;
    referral_bonus: ReferralBonus;
}

export function usePresentationData() {
    const data = ref<PresentationData | null>(null);
    const loading = ref(true);
    const error = ref<string | null>(null);

    const fetchData = async () => {
        try {
            loading.value = true;
            error.value = null;
            const response = await axios.get('/investor/api/presentation/data');
            data.value = response.data;
        } catch (err) {
            error.value = 'Failed to load presentation data';
            console.error('Error fetching presentation data:', err);
        } finally {
            loading.value = false;
        }
    };

    onMounted(() => {
        fetchData();
    });

    return {
        data,
        loading,
        error,
        refetch: fetchData,
    };
}
