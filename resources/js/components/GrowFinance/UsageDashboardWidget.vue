<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    ChartBarIcon,
    DocumentTextIcon,
    BanknotesIcon,
    UsersIcon,
    BuildingStorefrontIcon,
    CloudIcon,
    ArrowUpIcon,
    SparklesIcon,
} from '@heroicons/vue/24/outline';

interface UsageItem {
    used: number;
    limit: number;
    remaining?: number;
    allowed?: boolean;
}

interface UsageSummary {
    tier: string;
    tier_name: string;
    transactions?: UsageItem;
    invoices?: UsageItem;
    customers?: UsageItem;
    vendors?: UsageItem;
    storage?: {
        used_mb: number;
        limit_mb: number;
    };
    features?: string[];
    reports?: string[];
}

const props = defineProps<{
    usage: UsageSummary;
    compact?: boolean;
}>();

const tierColors: Record<string, { bg: string; text: string; badge: string }> = {
    free: { bg: 'bg-gray-100', text: 'text-gray-700', badge: 'bg-gray-200 text-gray-700' },
    basic: { bg: 'bg-blue-100', text: 'text-blue-700', badge: 'bg-blue-100 text-blue-700' },
    professional: { bg: 'bg-purple-100', text: 'text-purple-700', badge: 'bg-purple-100 text-purple-700' },
    business: { bg: 'bg-emerald-100', text: 'text-emerald-700', badge: 'bg-emerald-100 text-emerald-700' },
    full: { bg: 'bg-emerald-100', text: 'text-emerald-700', badge: 'bg-emerald-100 text-emerald-700' },
    none: { bg: 'bg-gray-100', text: 'text-gray-700', badge: 'bg-gray-200 text-gray-700' },
};

const tierColor = computed(() => tierColors[props.usage?.tier ?? 'free'] || tierColors.free);

const usageItems = computed(() => {
    const items = [];
    
    // Guard against undefined usage prop
    if (!props.usage) {
        return items;
    }

    // Transactions - safely check if exists and has a limit
    const transactions = props.usage.transactions;
    if (transactions && typeof transactions.limit === 'number' && transactions.limit !== -1 && transactions.limit > 0) {
        items.push({
            name: 'Transactions',
            icon: BanknotesIcon,
            used: transactions.used ?? 0,
            limit: transactions.limit,
            percentage: Math.round(((transactions.used ?? 0) / transactions.limit) * 100),
        });
    }

    // Invoices
    const invoices = props.usage.invoices;
    if (invoices && typeof invoices.limit === 'number' && invoices.limit !== -1 && invoices.limit > 0) {
        items.push({
            name: 'Invoices',
            icon: DocumentTextIcon,
            used: invoices.used ?? 0,
            limit: invoices.limit,
            percentage: Math.round(((invoices.used ?? 0) / invoices.limit) * 100),
        });
    }

    // Customers
    const customers = props.usage.customers;
    if (customers && typeof customers.limit === 'number' && customers.limit !== -1 && customers.limit > 0) {
        items.push({
            name: 'Customers',
            icon: UsersIcon,
            used: customers.used ?? 0,
            limit: customers.limit,
            percentage: Math.round(((customers.used ?? 0) / customers.limit) * 100),
        });
    }

    // Vendors
    const vendors = props.usage.vendors;
    if (vendors && typeof vendors.limit === 'number' && vendors.limit !== -1 && vendors.limit > 0) {
        items.push({
            name: 'Vendors',
            icon: BuildingStorefrontIcon,
            used: vendors.used ?? 0,
            limit: vendors.limit,
            percentage: Math.round(((vendors.used ?? 0) / vendors.limit) * 100),
        });
    }

    // Storage
    const storage = props.usage.storage;
    if (storage && typeof storage.limit_mb === 'number' && storage.limit_mb > 0) {
        items.push({
            name: 'Storage',
            icon: CloudIcon,
            used: storage.used_mb ?? 0,
            limit: storage.limit_mb,
            percentage: Math.round(((storage.used_mb ?? 0) / storage.limit_mb) * 100),
            unit: 'MB',
        });
    }

    return items;
});

const hasLimits = computed(() => usageItems.value.length > 0);
const isUnlimited = computed(() => !hasLimits.value);
const tierName = computed(() => props.usage?.tier_name ?? 'Free');
const tier = computed(() => props.usage?.tier ?? 'free');

const getProgressColor = (percentage: number) => {
    if (percentage >= 90) return 'bg-red-500';
    if (percentage >= 75) return 'bg-amber-500';
    return 'bg-emerald-500';
};

const getStatusColor = (percentage: number) => {
    if (percentage >= 90) return 'text-red-600';
    if (percentage >= 75) return 'text-amber-600';
    return 'text-emerald-600';
};
</script>

<template>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <ChartBarIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                <h3 class="text-sm font-semibold text-gray-900">Usage & Limits</h3>
            </div>
            <span :class="['px-2 py-0.5 text-xs font-medium rounded-full', tierColor.badge]">
                {{ tierName }}
            </span>
        </div>

        <!-- Content -->
        <div class="p-4">
            <!-- Unlimited Message -->
            <div v-if="isUnlimited" class="text-center py-4">
                <SparklesIcon class="h-8 w-8 text-emerald-500 mx-auto mb-2" aria-hidden="true" />
                <p class="text-sm font-medium text-gray-900">Unlimited Usage</p>
                <p class="text-xs text-gray-500 mt-1">
                    Your {{ tierName }} plan has no usage limits
                </p>
            </div>

            <!-- Usage Items -->
            <div v-else class="space-y-4">
                <div v-for="item in usageItems" :key="item.name">
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2">
                            <component :is="item.icon" class="h-4 w-4 text-gray-400" aria-hidden="true" />
                            <span class="text-sm text-gray-600">{{ item.name }}</span>
                        </div>
                        <span :class="['text-sm font-medium', getStatusColor(item.percentage)]">
                            {{ item.used }}{{ item.unit ? item.unit : '' }} / {{ item.limit }}{{ item.unit ? item.unit : '' }}
                        </span>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div
                            :class="['h-full rounded-full transition-all duration-300', getProgressColor(item.percentage)]"
                            :style="{ width: `${Math.min(item.percentage, 100)}%` }"
                        />
                    </div>
                    <p v-if="item.percentage >= 90" class="text-xs text-red-600 mt-1">
                        ⚠️ Almost at limit
                    </p>
                </div>
            </div>

            <!-- Upgrade CTA (hide for business and full tiers) -->
            <div v-if="tier !== 'business' && tier !== 'full'" class="mt-4 pt-4 border-t border-gray-100">
                <Link
                    :href="route('growfinance.upgrade')"
                    class="flex items-center justify-center gap-2 w-full px-4 py-2 text-sm font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded-lg transition-colors"
                >
                    <ArrowUpIcon class="h-4 w-4" aria-hidden="true" />
                    Upgrade for More
                </Link>
            </div>
        </div>
    </div>
</template>
