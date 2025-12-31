<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ClientLayout from '@/layouts/ClientLayout.vue';
import WalletTopUpModal from '@/components/Shared/WalletTopUpModal.vue';
import {
    ArrowLeftIcon,
    CheckIcon,
    XMarkIcon,
    WalletIcon,
    GlobeAltIcon,
    SparklesIcon,
    RocketLaunchIcon,
    BuildingOffice2Icon,
    ExclamationTriangleIcon,
    ChatBubbleLeftRightIcon,
    WrenchScrewdriverIcon,
    InformationCircleIcon,
} from '@heroicons/vue/24/outline';

interface TierFeature {
    key: string;
    name: string;
    value: boolean | number | string | null;
    display: string;
    available: boolean;
}

interface Tier {
    key: string;
    name: string;
    description: string;
    price_monthly: number;
    price_annual: number;
    discounted_monthly?: number;
    discounted_annual?: number;
    has_discount?: boolean;
    features: TierFeature[];
    limits?: Record<string, number | null>;
    is_popular?: boolean;
    is_default?: boolean;
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
        billing_cycle: string;
    } | null;
    usage: {
        sites: number;
        storage_used?: number;
        storage_used_formatted?: string;
        storage_limit?: number;
        storage_limit_formatted?: string;
        storage_percentage?: number;
    };
    tierStorageLimits?: Record<string, { bytes: number; formatted: string; mb: number }>;
    isMember?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    walletBalance: 0,
    currentTier: 'free',
    tiers: () => ({}),
    isMember: false,
});

const showCheckoutModal = ref(false);
const showDomainInfoModal = ref(false);
const showTopUpModal = ref(false);
const selectedPlan = ref<{ key: string; name: string; amount: number } | null>(null);
const processing = ref(false);
const billingCycle = ref<'monthly' | 'annual'>('monthly');

// Convert tiers object to sorted array
const plansArray = computed(() => {
    return Object.values(props.tiers).sort((a, b) => a.sort_order - b.sort_order);
});

// SMS bundles (these could also come from backend if needed)
const smsBundles = [
    { sms: 50, price: 15 },
    { sms: 200, price: 50 },
    { sms: 500, price: 100 },
    { sms: 1000, price: 180 },
];

const tierGradients: Record<string, string> = {
    free: 'from-gray-500 to-gray-600',
    starter: 'from-blue-500 to-indigo-600',
    business: 'from-emerald-500 to-teal-600',
    agency: 'from-purple-500 to-indigo-600',
};

const tierIcons: Record<string, any> = {
    free: GlobeAltIcon,
    starter: SparklesIcon,
    business: RocketLaunchIcon,
    agency: BuildingOffice2Icon,
};

// Plan-specific tags
const tierTags: Record<string, string | null> = {
    free: null,
    starter: null,
    business: 'RECOMMENDED',
    agency: 'BEST FOR AGENCIES',
};

const getGradient = (key: string) => tierGradients[key] || 'from-indigo-500 to-purple-600';
const getIcon = (key: string) => tierIcons[key] || GlobeAltIcon;
const getTag = (key: string, tier: Tier) => tier.is_popular ? 'RECOMMENDED' : tierTags[key] || null;

const currentPlan = computed(() => props.tiers[props.currentTier] || plansArray.value[0]);

// Build tier order from actual tiers
const tierOrder = computed(() => plansArray.value.map(t => t.key));

const canUpgradeTo = (key: string) => {
    const currentIndex = tierOrder.value.indexOf(props.currentTier || 'free');
    const targetIndex = tierOrder.value.indexOf(key);
    return targetIndex > currentIndex;
};

const getPrice = (tier: Tier) => {
    if (billingCycle.value === 'annual') {
        return tier.has_discount && tier.discounted_annual ? tier.discounted_annual : tier.price_annual;
    }
    return tier.has_discount && tier.discounted_monthly ? tier.discounted_monthly : tier.price_monthly;
};

const getAnnualSavings = (tier: Tier) => {
    if (tier.price_monthly === 0) return 0;
    const monthlyTotal = tier.price_monthly * 12;
    const annualPrice = tier.has_discount && tier.discounted_annual ? tier.discounted_annual : tier.price_annual;
    return monthlyTotal - annualPrice;
};

const openCheckout = (tier: Tier) => {
    selectedPlan.value = { key: tier.key, name: tier.name, amount: getPrice(tier) };
    // Show domain info modal for Business plan
    if (tier.key === 'business') {
        showDomainInfoModal.value = true;
    } else {
        showCheckoutModal.value = true;
    }
};

const proceedFromDomainInfo = () => {
    showDomainInfoModal.value = false;
    showCheckoutModal.value = true;
};

const closeCheckout = () => {
    showCheckoutModal.value = false;
    showDomainInfoModal.value = false;
    selectedPlan.value = null;
};

const confirmPurchase = () => {
    if (!selectedPlan.value) return;
    processing.value = true;

    router.post(route('growbuilder.subscription.purchase'), {
        tier: selectedPlan.value.key,
        amount: selectedPlan.value.amount,
        billing_cycle: billingCycle.value === 'annual' ? 'yearly' : 'monthly',
    }, {
        onFinish: () => {
            processing.value = false;
            closeCheckout();
        },
    });
};

const formatCurrency = (amount: number) => `K${amount.toLocaleString()}`;

const onTopUpSuccess = () => router.reload({ only: ['walletBalance'] });

// Get display features for a tier (limit to 8 for UI)
const getDisplayFeatures = (tier: Tier) => {
    if (!tier.features || tier.features.length === 0) return [];
    return tier.features.slice(0, 8);
};
</script>

<template>
    <ClientLayout>
        <Head title="Pricing - GrowBuilder" />

        <div class="max-w-5xl mx-auto px-4 py-6 pb-24">
            <!-- Header -->
            <div class="flex items-center gap-3 mb-6">
                <Link :href="route('growbuilder.index')" class="p-2 -ml-2 rounded-xl hover:bg-gray-100" aria-label="Back">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">GrowBuilder Pricing</h1>
                    <p class="text-sm text-gray-500">Choose the right plan for your business</p>
                </div>
            </div>

            <!-- Current Plan & Wallet -->
            <div class="grid md:grid-cols-3 gap-4 mb-6">
                <div :class="['rounded-2xl p-5 text-white bg-gradient-to-br', getGradient(currentTier)]">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-sm opacity-80">Current Plan</p>
                            <p class="text-2xl font-bold">{{ currentPlan?.name || 'Free' }}</p>
                        </div>
                        <component :is="getIcon(currentTier)" class="h-10 w-10 opacity-50" aria-hidden="true" />
                    </div>
                    <p class="text-sm opacity-80">{{ usage.sites }} site(s) created</p>
                </div>

                <!-- Storage Usage -->
                <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-sm text-gray-500">Storage Used</p>
                            <p class="text-xl font-bold text-gray-900">{{ usage.storage_used_formatted || '0 B' }}</p>
                        </div>
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                            </svg>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>{{ usage.storage_percentage || 0 }}% used</span>
                            <span>{{ usage.storage_limit_formatted || '0 B' }} limit</span>
                        </div>
                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div 
                                :class="[
                                    'h-full rounded-full transition-all',
                                    (usage.storage_percentage || 0) >= 90 ? 'bg-red-500' : 
                                    (usage.storage_percentage || 0) >= 80 ? 'bg-yellow-500' : 'bg-blue-500'
                                ]"
                                :style="{ width: Math.min(usage.storage_percentage || 0, 100) + '%' }"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Wallet Balance -->
                <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-5 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-emerald-100">Wallet Balance</p>
                            <p class="text-2xl font-bold">{{ formatCurrency(walletBalance) }}</p>
                        </div>
                        <WalletIcon class="h-10 w-10 opacity-50" aria-hidden="true" />
                    </div>
                    <button @click="showTopUpModal = true" class="mt-2 text-sm text-emerald-100 hover:text-white underline">
                        Top up wallet →
                    </button>
                </div>
            </div>

            <!-- Free Domain Info Strip -->
            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 rounded-xl p-4 mb-6 flex items-center gap-3">
                <div class="p-2 bg-emerald-100 rounded-full">
                    <GlobeAltIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                </div>
                <p class="text-emerald-800">
                    <span class="font-semibold">🌐 Free domain included</span> on Business Plan after 3 months prepayment
                </p>
            </div>

            <!-- Billing Toggle -->
            <div class="flex items-center justify-center gap-3 mb-6 bg-gray-100 rounded-xl p-1 max-w-xs mx-auto">
                <button @click="billingCycle = 'monthly'" :class="['flex-1 py-2 px-4 rounded-lg text-sm font-semibold transition-colors', billingCycle === 'monthly' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600']">
                    Monthly
                </button>
                <button @click="billingCycle = 'annual'" :class="['flex-1 py-2 px-4 rounded-lg text-sm font-semibold transition-colors', billingCycle === 'annual' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600']">
                    Annual <span class="text-xs text-emerald-600">(Save 17%)</span>
                </button>
            </div>

            <!-- Plans Grid (Dynamic from backend) -->
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div v-for="plan in plansArray" :key="plan.key" :class="[
                    'bg-white rounded-2xl shadow-sm overflow-hidden border-2 transition-all relative',
                    currentTier === plan.key ? 'border-indigo-500' : (plan.is_popular || plan.key === 'business') ? 'border-emerald-400' : 'border-transparent'
                ]">
                    <!-- Tag -->
                    <div v-if="getTag(plan.key, plan)" :class="[
                        'absolute top-0 right-0 px-3 py-1 text-xs font-bold rounded-bl-xl',
                        (plan.is_popular || plan.key === 'business') ? 'bg-emerald-500 text-white' : 'bg-purple-500 text-white'
                    ]">
                        {{ getTag(plan.key, plan) }}
                    </div>

                    <!-- Header -->
                    <div :class="['p-4', plan.key !== 'free' ? `bg-gradient-to-br ${getGradient(plan.key)} text-white` : 'bg-gray-50']">
                        <div class="flex items-center gap-2 mb-2">
                            <component :is="getIcon(plan.key)" class="h-6 w-6" :class="plan.key === 'free' ? 'text-gray-600' : 'opacity-80'" aria-hidden="true" />
                            <h3 class="font-bold text-lg">{{ plan.name }}</h3>
                        </div>
                        <div class="flex items-baseline gap-1">
                            <p class="text-2xl font-bold">{{ getPrice(plan) === 0 ? 'Free' : formatCurrency(getPrice(plan)) }}</p>
                            <span v-if="getPrice(plan) > 0" class="text-sm opacity-80">/{{ billingCycle === 'annual' ? 'year' : 'month' }}</span>
                        </div>
                        <p v-if="billingCycle === 'annual' && getAnnualSavings(plan) > 0" class="text-xs mt-1 opacity-80">
                            Save {{ formatCurrency(getAnnualSavings(plan)) }}/year
                        </p>
                        <p :class="['text-xs mt-2', plan.key === 'free' ? 'text-gray-600' : 'opacity-80']">{{ plan.description }}</p>
                    </div>

                    <!-- Features -->
                    <div class="p-4">
                        <ul class="space-y-2 mb-4">
                            <li v-for="feature in getDisplayFeatures(plan)" :key="feature.key" class="flex items-start gap-2 text-sm">
                                <CheckIcon v-if="feature.available" class="h-4 w-4 text-emerald-500 flex-shrink-0 mt-0.5" aria-hidden="true" />
                                <XMarkIcon v-else class="h-4 w-4 text-gray-300 flex-shrink-0 mt-0.5" aria-hidden="true" />
                                <span :class="feature.available ? 'text-gray-700' : 'text-gray-400 line-through'">
                                    {{ feature.name }}
                                    <span v-if="feature.display && feature.display !== '✓' && feature.display !== '✗'" class="text-gray-500">({{ feature.display }})</span>
                                </span>
                            </li>
                        </ul>

                        <!-- Action Button -->
                        <div v-if="canUpgradeTo(plan.key)">
                            <button @click="openCheckout(plan)" :class="['w-full py-2.5 rounded-xl font-semibold transition-colors', `bg-gradient-to-r ${getGradient(plan.key)} text-white hover:opacity-90`]">
                                Select Plan
                            </button>
                        </div>
                        <div v-else-if="currentTier === plan.key" class="text-center py-2.5 text-gray-500 text-sm bg-gray-100 rounded-xl">
                            Current Plan
                        </div>
                        <div v-else class="text-center py-2.5 text-gray-400 text-sm">
                            —
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Services Section -->
            <div class="grid md:grid-cols-2 gap-6">
                <!-- SMS Credits -->
                <div class="bg-white rounded-2xl border border-gray-200 p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-blue-100 rounded-xl">
                            <ChatBubbleLeftRightIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">SMS Credits</h3>
                            <p class="text-sm text-gray-500">Send notifications to your customers</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div v-for="bundle in smsBundles" :key="bundle.sms" class="bg-gray-50 rounded-xl p-3 text-center">
                            <p class="text-lg font-bold text-gray-900">{{ bundle.sms }} SMS</p>
                            <p class="text-sm text-gray-600">{{ formatCurrency(bundle.price) }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-3 text-center">Agency plan members get 10-15% discount</p>
                </div>

                <!-- Done-For-You Setup -->
                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl border border-indigo-200 p-5">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-indigo-100 rounded-xl">
                            <WrenchScrewdriverIcon class="h-6 w-6 text-indigo-600" aria-hidden="true" />
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900">Done-For-You Setup</h3>
                            <p class="text-sm text-gray-500">Let us build your site for you</p>
                        </div>
                    </div>
                    <div class="bg-white/60 rounded-xl p-4 mb-3">
                        <p class="text-2xl font-bold text-indigo-600">K1,500 – K3,500</p>
                        <p class="text-sm text-gray-600">One-time fee + subscription plan</p>
                    </div>
                    <ul class="space-y-1 text-sm text-gray-700">
                        <li class="flex items-center gap-2">
                            <CheckIcon class="h-4 w-4 text-indigo-500" aria-hidden="true" />
                            Professional site setup
                        </li>
                        <li class="flex items-center gap-2">
                            <CheckIcon class="h-4 w-4 text-indigo-500" aria-hidden="true" />
                            Template selection & customization
                        </li>
                        <li class="flex items-center gap-2">
                            <CheckIcon class="h-4 w-4 text-indigo-500" aria-hidden="true" />
                            Basic copywriting included
                        </li>
                        <li class="flex items-center gap-2">
                            <CheckIcon class="h-4 w-4 text-indigo-500" aria-hidden="true" />
                            1 training session
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Domain Info Modal (for Business Plan) -->
        <Teleport to="body">
            <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" leave-active-class="transition-opacity duration-150" leave-to-class="opacity-0">
                <div v-if="showDomainInfoModal" class="fixed inset-0 z-[100] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4" @click="closeCheckout">
                    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6" @click.stop>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-2 bg-emerald-100 rounded-full">
                                <InformationCircleIcon class="h-6 w-6 text-emerald-600" aria-hidden="true" />
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Free Domain Policy</h3>
                        </div>

                        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 mb-4">
                            <p class="text-sm text-emerald-900 leading-relaxed">
                                Your <span class="font-semibold">free domain</span> will be activated after <span class="font-semibold">3 months prepayment</span> to protect MyGrowNet from early cancellations.
                            </p>
                            <p class="text-sm text-emerald-800 mt-3 leading-relaxed">
                                If cancelled before 12 months, the domain remains under MyGrowNet until renewal fees are settled or a transfer fee is paid.
                            </p>
                        </div>

                        <div class="flex gap-3">
                            <button @click="closeCheckout" class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold">
                                Cancel
                            </button>
                            <button @click="proceedFromDomainInfo" class="flex-1 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-bold">
                                I Understand, Continue
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Checkout Modal -->
        <Teleport to="body">
            <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" leave-active-class="transition-opacity duration-150" leave-to-class="opacity-0">
                <div v-if="showCheckoutModal && selectedPlan" class="fixed inset-0 z-[100] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4" @click="closeCheckout">
                    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6" @click.stop>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-900">Confirm Purchase</h3>
                            <button @click="closeCheckout" class="p-2 hover:bg-gray-100 rounded-full" aria-label="Close">
                                <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div class="bg-indigo-50 rounded-2xl p-4">
                                <div class="flex justify-between mb-2">
                                    <span class="text-gray-600">Plan</span>
                                    <span class="font-bold">{{ selectedPlan.name }}</span>
                                </div>
                                <div class="flex justify-between mb-2">
                                    <span class="text-gray-600">Billing</span>
                                    <span class="font-semibold">{{ billingCycle === 'annual' ? 'Annual' : 'Monthly' }}</span>
                                </div>
                                <div class="flex justify-between pt-2 border-t border-indigo-200">
                                    <span class="font-semibold">Total</span>
                                    <span class="text-2xl font-bold text-indigo-600">{{ selectedPlan.amount === 0 ? 'Free' : formatCurrency(selectedPlan.amount) }}</span>
                                </div>
                            </div>

                            <div v-if="selectedPlan.amount > 0" class="bg-gray-50 rounded-2xl p-4">
                                <div class="flex justify-between mb-2">
                                    <span class="text-gray-600">Wallet Balance</span>
                                    <span class="font-bold">{{ formatCurrency(walletBalance) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">After Purchase</span>
                                    <span :class="walletBalance >= selectedPlan.amount ? 'text-emerald-600' : 'text-red-600'" class="font-semibold">
                                        {{ formatCurrency(walletBalance - selectedPlan.amount) }}
                                    </span>
                                </div>
                            </div>

                            <div v-if="selectedPlan.amount > 0 && walletBalance < selectedPlan.amount" class="bg-red-50 border border-red-200 rounded-2xl p-4 flex gap-3">
                                <ExclamationTriangleIcon class="h-5 w-5 text-red-600 flex-shrink-0" aria-hidden="true" />
                                <div>
                                    <p class="text-sm font-semibold text-red-900">Insufficient Balance</p>
                                    <button @click="closeCheckout(); showTopUpModal = true;" class="text-xs text-red-600 underline mt-1">Top up wallet →</button>
                                </div>
                            </div>

                            <div class="flex gap-3 pt-2">
                                <button @click="closeCheckout" class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold" :disabled="processing">Cancel</button>
                                <button @click="confirmPurchase" class="flex-1 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl font-bold disabled:opacity-50" :disabled="processing || (selectedPlan.amount > 0 && walletBalance < selectedPlan.amount)">
                                    {{ processing ? 'Processing...' : 'Confirm' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <WalletTopUpModal :show="showTopUpModal" :balance="walletBalance" @close="showTopUpModal = false" @success="onTopUpSuccess" />
    </ClientLayout>
</template>
