<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import GrowStreamLayout from '@/Layouts/GrowStreamLayout.vue';
import PlansBody from '@/pages/Payments/PlansBody.vue';

interface Tier {
    key: string;
    name: string;
    description: string;
    price_monthly: number;
    price_annual: number;
    popular: boolean;
    sort_order?: number;
    limits?: Record<string, number | null>;
    labeled_limits?: Record<string, { key: string; label: string; value: number }>;
    features?: string[];
    labeled_features?: { key: string; label: string }[];
}

interface ModuleInfo {
    id: string;
    name: string;
    color?: string;
}

interface BackLink {
    label: string;
    url: string;
}

const props = defineProps<{
    module: ModuleInfo;
    tiers: Record<string, Tier>;
    currentTier?: string;
    isAdmin?: boolean;
    back?: BackLink;
    checkoutRoute?: string;
}>();
</script>

<template>
    <Head :title="`${module.name} Plans`" />

    <GrowStreamLayout v-if="module.id === 'growstream'" :title="`${module.name} Plans`">
        <div class="px-margin-mobile pt-6 pb-8">
            <PlansBody :module="module" :tiers="tiers" :current-tier="currentTier" :is-admin="isAdmin" :back="back" :checkout-route="checkoutRoute" />
        </div>
    </GrowStreamLayout>

    <div v-else class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-blue-50 py-12 px-4">
        <PlansBody :module="module" :tiers="tiers" :current-tier="currentTier" :is-admin="isAdmin" :back="back" :checkout-route="checkoutRoute" />
    </div>
</template>
