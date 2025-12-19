<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import WalletTopUpModal from '@/Components/Shared/WalletTopUpModal.vue';
import {
    ArrowLeftIcon,
    CheckIcon,
    XMarkIcon,
    WalletIcon,
    ChartBarIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

interface TierFeature {
    key: string;
    name: string;
    value: boolean | number | string;
    display: string;
    available: boolean;
}

interface Tier {
    key: string;
    name: string;
    description: string;
    price_monthly: number;
    price_annual: number;
    discounted_monthly: number;
    discounted_annual: number;
    has_discount: boolean;
    discount: any;
    features: TierFeature[];
    limits: Record<string, number>;
    user_limit: number | null;
    storage_limit_mb: number | null;
    is_popular: boolean;
    is_default: boolean;
    sort_order: number;
}

interface Props {
    walletBalance: number;
    currentTier: string;
    tiers: Record<string, Tier>;
    subscription?: {
        tier: string;
        expires_at: string;
        auto_renew: boolean;
    } | null;
}

const props = withDefaults(defineProps<Props>(), {
    walletBalance: 0,
    currentTier: 'free',
    tiers: () => ({}),
});

const showCheckoutModal = ref(false);
const showTopUpModal = ref(false);
const selectedPlan = ref<{ tier: string; name: string; amount: number } | null>(null);
const processing = ref(false);
const billingCycle = ref<'monthly' | 'annual'>('monthly');

// Convert tiers object to sorted array
const plansArray = computed(() => {
    return Object.values(props.tiers).sort((a, b) => a.sort_order - b.sort_order);
});

// Gradient colors for each tier
const tierGradients: Record<string, string> = {
    free: 'from-gray-500 to-gray-600',
    basic: 'from-blue-500 to-indigo-600',
    starter: 'from-blue-500 to-indigo-600',
    professional: 'from-emerald-500 to-teal-600',
    business: 'from-amber-500 to-orange-600',
    enterprise: 'from-purple-500 to-indigo-600',
};

const getGradient = (tierKey: string) => tierGradients[tierKey] || 'from-gray-500 to-gray-600';

const currentPlan = computed(() => {
    return props.tiers[props.currentTier] || plansArray.value[0];
});

const tierOrder = ['free', 'basic', 'starter', 'professional', 'business', 'enterprise'];

const canUpgradeTo = (tierKey: string) => {
    const currentIndex = tierOrder.indexOf(props.currentTier || 'free');
    const targetIndex = tierOrder.indexOf(tierKey);
    return targetIndex > currentIndex;
};

const getPrice = (tier: Tier) => {
    if (billingCycle.value === 'annual') {
        return tier.has_discount ? tier.discounted_annual : tier.price_annual;
    }
    return tier.has_discount ? tier.discounted_monthly : tier.price_monthly;
};

const openCheckout = (tier: Tier) => {
    selectedPlan.value = {
        tier: tier.key,
        name: tier.name,
        amount: getPrice(tier),
    };
    showCheckoutModal.value = true;
};

const closeCheckout = () => {
    showCheckoutModal.value = false;
    selectedPlan.value = null;
};

const confirmPurchase = () => {
    if (!selectedPlan.value) return;
    processing.value = true;

    router.post(
        route('growfinance.subscription.purchase'),
        {
            module_id: 'growfinance',
            tier: selectedPlan.value.tier,
            amount: selectedPlan.value.amount,
            billing_cycle: billingCycle.value === 'annual' ? 'yearly' : 'monthly',
        },
        {
            onFinish: () => {
                processing.value = false;
                closeCheckout();
            },
        }
    );
};

const formatCurrency = (amount: number) => `K${amount.toLocaleString()}`;

const onTopUpSuccess = () => {
    router.reload({ only: ['walletBalance'] });
};
</script>

<template>
    <GrowFinanceLayout>
        <Head title="Subscription - GrowFinance" />

        <div class="px-4 py-4 pb-24">
            <!-- Header -->
            <div class="flex items-center gap-3 mb-6">
                <Link
                    :href="route('growfinance.dashboard')"
                    class="p-2 -ml-2 rounded-xl hover:bg-gray-100 transition-colors"
                    aria-label="Back to dashboard"
                >
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </Link>
                <h1 class="text-xl font-bold text-gray-900">Subscription</h1>
            </div>

            <!-- Current Plan -->
            <div
                v-if="currentPlan"
                :class="['rounded-2xl p-5 mb-6 text-white bg-gradient-to-br', getGradient(currentTier)]"
            >
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-sm opacity-80">Current Plan</p>
                        <p class="text-2xl font-bold">{{ currentPlan.name }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                        <ChartBarIcon class="h-6 w-6" aria-hidden="true" />
                    </div>
                </div>
                <p class="text-sm opacity-80">{{ currentPlan.description }}</p>
            </div>

            <!-- Wallet Balance -->
            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-5 mb-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-emerald-100">Wallet Balance</p>
                        <p class="text-3xl font-bold">{{ formatCurrency(walletBalance) }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                        <WalletIcon class="h-6 w-6" aria-hidden="true" />
                    </div>
                </div>
                <button
                    @click="showTopUpModal = true"
                    class="mt-3 text-sm text-emerald-100 hover:text-white underline"
                >
                    Top up wallet →
                </button>
            </div>

            <!-- Billing Cycle Toggle -->
            <div class="flex items-center justify-center gap-3 mb-6 bg-gray-100 rounded-xl p-1">
                <button
                    @click="billingCycle = 'monthly'"
                    :class="[
                        'flex-1 py-2 px-4 rounded-lg text-sm font-semibold transition-colors',
                        billingCycle === 'monthly'
                            ? 'bg-white text-gray-900 shadow-sm'
                            : 'text-gray-600 hover:text-gray-900',
                    ]"
                >
                    Monthly
                </button>
                <button
                    @click="billingCycle = 'annual'"
                    :class="[
                        'flex-1 py-2 px-4 rounded-lg text-sm font-semibold transition-colors',
                        billingCycle === 'annual'
                            ? 'bg-white text-gray-900 shadow-sm'
                            : 'text-gray-600 hover:text-gray-900',
                    ]"
                >
                    Annual
                    <span class="ml-1 text-xs text-emerald-600">(Save 17%)</span>
                </button>
            </div>

            <!-- Plans -->
            <h2 class="text-lg font-bold text-gray-900 mb-4">Available Plans</h2>
            <div class="space-y-4">
                <div
                    v-for="plan in plansArray"
                    :key="plan.key"
                    :class="[
                        'bg-white rounded-2xl shadow-sm overflow-hidden border-2 transition-all',
                        currentTier === plan.key ? 'border-emerald-500' : 'border-transparent',
                    ]"
                >
                    <div
                        :class="[
                            'p-5',
                            plan.key !== 'free' ? `bg-gradient-to-br ${getGradient(plan.key)} text-white` : '',
                        ]"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-lg">{{ plan.name }}</h3>
                                <span
                                    v-if="plan.is_popular"
                                    class="px-2 py-0.5 bg-yellow-400 text-yellow-900 rounded text-xs font-bold"
                                >
                                    POPULAR
                                </span>
                                <span
                                    v-if="plan.has_discount"
                                    class="px-2 py-0.5 bg-red-500 text-white rounded text-xs font-bold"
                                >
                                    SALE
                                </span>
                            </div>
                            <span
                                v-if="currentTier === plan.key"
                                :class="[
                                    'px-3 py-1 rounded-full text-xs font-semibold',
                                    plan.key === 'free' ? 'bg-gray-200 text-gray-700' : 'bg-white/20',
                                ]"
                            >
                                Current
                            </span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <p class="text-2xl font-bold">
                                {{ getPrice(plan) === 0 ? 'Free' : formatCurrency(getPrice(plan)) }}
                            </p>
                            <span v-if="getPrice(plan) > 0" class="text-sm font-normal opacity-80">
                                /{{ billingCycle === 'annual' ? 'year' : 'month' }}
                            </span>
                            <span
                                v-if="plan.has_discount && billingCycle === 'monthly'"
                                class="text-sm line-through opacity-60"
                            >
                                {{ formatCurrency(plan.price_monthly) }}
                            </span>
                        </div>
                    </div>

                    <div class="p-5">
                        <ul class="space-y-2 mb-4">
                            <li
                                v-for="feature in plan.features.slice(0, 7)"
                                :key="feature.key"
                                class="flex items-center gap-2 text-sm"
                            >
                                <CheckIcon
                                    v-if="feature.available"
                                    class="h-4 w-4 text-emerald-500"
                                    aria-hidden="true"
                                />
                                <XMarkIcon v-else class="h-4 w-4 text-gray-300" aria-hidden="true" />
                                <span :class="feature.available ? 'text-gray-700' : 'text-gray-400'">
                                    {{ feature.name }}
                                    <span v-if="feature.display !== '✓' && feature.display !== '✗'" class="text-gray-500">
                                        ({{ feature.display }})
                                    </span>
                                </span>
                            </li>
                        </ul>

                        <button
                            v-if="canUpgradeTo(plan.key)"
                            @click="openCheckout(plan)"
                            :class="[
                                'w-full py-3 rounded-xl font-bold transition-colors',
                                `bg-gradient-to-r ${getGradient(plan.key)} text-white hover:opacity-90`,
                            ]"
                        >
                            Upgrade to {{ plan.name }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checkout Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                leave-active-class="transition-opacity duration-150"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="showCheckoutModal && selectedPlan"
                    class="fixed inset-0 z-[100] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
                    @click="closeCheckout"
                >
                    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6" @click.stop>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-900">Confirm Purchase</h3>
                            <button
                                @click="closeCheckout"
                                class="p-2 hover:bg-gray-100 rounded-full"
                                aria-label="Close modal"
                            >
                                <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div class="bg-emerald-50 rounded-2xl p-4">
                                <div class="flex justify-between mb-2">
                                    <span class="text-gray-600">Plan</span>
                                    <span class="font-bold">{{ selectedPlan.name }}</span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span class="text-gray-600">Billing</span>
                                    <span class="font-semibold">{{ billingCycle === 'annual' ? 'Annual' : 'Monthly' }}</span>
                                </div>
                                <div class="flex justify-between pt-2 border-t border-emerald-200">
                                    <span class="font-semibold">Total</span>
                                    <span class="text-2xl font-bold text-emerald-600">
                                        {{ formatCurrency(selectedPlan.amount) }}
                                    </span>
                                </div>
                            </div>

                            <div class="bg-gray-50 rounded-2xl p-4">
                                <div class="flex justify-between mb-2">
                                    <span class="text-gray-600">Wallet Balance</span>
                                    <span class="font-bold">{{ formatCurrency(walletBalance) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">After Purchase</span>
                                    <span
                                        :class="
                                            walletBalance >= selectedPlan.amount ? 'text-emerald-600' : 'text-red-600'
                                        "
                                        class="font-semibold"
                                    >
                                        {{ formatCurrency(walletBalance - selectedPlan.amount) }}
                                    </span>
                                </div>
                            </div>

                            <div
                                v-if="walletBalance < selectedPlan.amount"
                                class="bg-red-50 border border-red-200 rounded-2xl p-4 flex gap-3"
                            >
                                <ExclamationTriangleIcon class="h-5 w-5 text-red-600 flex-shrink-0" aria-hidden="true" />
                                <div>
                                    <p class="text-sm font-semibold text-red-900">Insufficient Balance</p>
                                    <button
                                        @click="
                                            closeCheckout();
                                            showTopUpModal = true;
                                        "
                                        class="text-xs text-red-600 underline mt-1"
                                    >
                                        Top up wallet →
                                    </button>
                                </div>
                            </div>

                            <div class="flex gap-3 pt-2">
                                <button
                                    @click="closeCheckout"
                                    class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold"
                                    :disabled="processing"
                                >
                                    Cancel
                                </button>
                                <button
                                    @click="confirmPurchase"
                                    class="flex-1 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-bold disabled:opacity-50"
                                    :disabled="processing || walletBalance < selectedPlan.amount"
                                >
                                    {{ processing ? 'Processing...' : 'Confirm' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Wallet Top Up Modal -->
        <WalletTopUpModal
            :show="showTopUpModal"
            :balance="walletBalance"
            @close="showTopUpModal = false"
            @success="onTopUpSuccess"
        />
    </GrowFinanceLayout>
</template>
