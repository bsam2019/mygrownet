<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';

interface Tier {
    key: string;
    name: string;
    description: string;
    price_monthly: number;
    price_annual: number;
    popular: boolean;
    sort_order?: number;
    labeled_limits?: Record<string, { key: string; label: string; value: number }>;
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

const page = usePage();

const plansArray = computed(() => {
    return Object.values(props.tiers).sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));
});

const colorGradients: Record<string, string> = {
    emerald: 'from-emerald-500 to-teal-600',
    blue: 'from-blue-500 to-indigo-600',
    indigo: 'from-indigo-500 to-purple-600',
    orange: 'from-orange-500 to-amber-600',
    rose: 'from-rose-500 to-pink-600',
    fuchsia: 'from-fuchsia-500 to-purple-600',
    teal: 'from-teal-500 to-emerald-600',
    green: 'from-green-500 to-emerald-600',
    amber: 'from-amber-500 to-orange-600',
};

const gradient = computed(() => colorGradients[props.module.color ?? 'emerald'] ?? colorGradients.emerald);
const formatCurrency = (amount: number) => `K${amount.toLocaleString()}`;
const currentTierKey = computed(() => props.currentTier ?? 'free');
const tierOrder = computed(() => plansArray.value.map((t) => t.key));

const canUpgradeTo = (key: string) => {
    if (props.isAdmin) return true;
    const currentIndex = tierOrder.value.indexOf(currentTierKey.value);
    const targetIndex = tierOrder.value.indexOf(key);
    if (currentIndex === -1) return true;
    return targetIndex > currentIndex;
};

const checkoutUrl = (tier: Tier) => route(props.checkoutRoute ?? 'subscriptions.checkout', {
    module: props.module.id,
    tier: tier.key,
    billing_cycle: 'monthly',
    return_url: page.url,
});
</script>

<template>
    <Head :title="`${module.name} Plans`" />

    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br shadow-lg mb-4" :class="gradient">
                <span class="text-2xl font-bold text-white">{{ module.name.charAt(0) }}</span>
            </div>
            <h1 class="text-3xl font-bold text-on-surface">Choose your {{ module.name }} plan</h1>
            <p class="mt-2 text-on-surface-variant">Pay securely via mobile money — activate instantly.</p>
        </div>

        <!-- Plan Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div
                v-for="plan in plansArray"
                :key="plan.key"
                class="relative bg-surface-container-lowest rounded-3xl border shadow-sm p-6 flex flex-col transition"
                :class="plan.popular ? 'border-emerald-400 ring-2 ring-emerald-200' : 'border-gray-200'"
            >
                <span v-if="plan.popular" class="absolute -top-3 left-1/2 -translate-x-1/2 text-xs font-bold bg-emerald-600 text-white px-3 py-1 rounded-full">
                    RECOMMENDED
                </span>

                <h3 class="text-lg font-bold text-on-surface">{{ plan.name }}</h3>
                <p class="mt-1 text-sm text-on-surface-variant min-h-[40px]">{{ plan.description }}</p>

                <div class="mt-4 flex items-baseline gap-1">
                    <p class="text-3xl font-extrabold text-on-surface">
                        {{ plan.price_monthly === 0 ? 'Free' : formatCurrency(plan.price_monthly) }}
                    </p>
                    <span v-if="plan.price_monthly > 0" class="text-sm text-on-surface-variant">/month</span>
                </div>
                <p v-if="plan.price_annual > 0" class="mt-1 text-xs text-emerald-600">
                    or {{ formatCurrency(plan.price_annual) }}/year
                </p>

                <ul class="mt-5 space-y-2 flex-1">
                    <li v-for="(limit, key) in (plan.labeled_limits ?? {})" :key="key" class="flex items-center gap-2 text-sm text-on-surface-variant">
                        <svg class="h-4 w-4 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                        </svg>
                        {{ limit.label ?? key }}:
                        <span class="font-medium">{{ limit.value === -1 ? 'Unlimited' : limit.value }}</span>
                    </li>
                    <li v-for="feature in (plan.labeled_features ?? []).slice(0, 5)" :key="feature.key" class="flex items-center gap-2 text-sm text-on-surface-variant">
                        <svg class="h-4 w-4 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                        </svg>
                        {{ feature.label ?? feature.key }}
                    </li>
                </ul>

                <div v-if="canUpgradeTo(plan.key)" class="mt-6">
                    <a :href="checkoutUrl(plan)" class="block w-full text-center py-2.5 rounded-xl font-semibold text-white hover:opacity-90 transition" :class="`bg-gradient-to-r ${gradient}`">
                        {{ plan.key === currentTierKey ? 'Current Plan' : (plan.price_monthly === 0 ? 'Start Free' : 'Select Plan') }}
                    </a>
                </div>
                <div v-else class="mt-6">
                    <span class="block w-full text-center py-2.5 rounded-xl font-semibold bg-surface-container-high text-on-surface-variant cursor-not-allowed">
                        Current Plan
                    </span>
                </div>
            </div>
        </div>

        <!-- Contact -->
        <div class="mt-10 text-center">
            <Link :href="back?.url ?? route('workspace')" class="text-sm text-on-surface-variant hover:text-on-surface-variant">
                &larr; {{ back?.label ?? 'Back to workspace' }}
            </Link>
        </div>
    </div>
</template>

