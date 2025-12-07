<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { 
    CheckIcon, 
    XMarkIcon,
    SparklesIcon,
    StarIcon,
    RocketLaunchIcon,
    BuildingOfficeIcon,
} from '@heroicons/vue/24/outline';
import { CheckCircleIcon } from '@heroicons/vue/24/solid';

interface LabeledFeature {
    key: string;
    label: string;
}

interface LabeledLimit {
    key: string;
    label: string;
    value: number;
}

interface TierConfig {
    key: string;
    name: string;
    description: string;
    price_monthly: number;
    price_annual: number;
    popular?: boolean;
    features: string[];
    labeled_features: LabeledFeature[];
    limits: Record<string, number>;
    labeled_limits: Record<string, LabeledLimit>;
}

interface UsageItem {
    used: number;
    limit: number;
    remaining?: number;
}

interface Props {
    currentTier: string;
    tiers: Record<string, TierConfig>;
    usageSummary: Record<string, UsageItem>;
    moduleId: string;
}

const props = defineProps<Props>();
const page = usePage();

const billingCycle = ref<'monthly' | 'annual'>('monthly');

// Check if user is admin
const isAdmin = computed(() => {
    const user = page.props.auth?.user as any;
    return user?.roles?.some((role: any) => 
        role.name?.toLowerCase().includes('admin')
    ) || false;
});

// Tier order for comparison
const tierOrder = ['free', 'basic', 'professional', 'business'];

const getTierIndex = (tier: string) => tierOrder.indexOf(tier);

const canUpgradeTo = (tierKey: string) => {
    if (isAdmin.value) return false; // Admins don't need to upgrade
    return getTierIndex(tierKey) > getTierIndex(props.currentTier);
};

const isCurrentTier = (tierKey: string) => {
    return props.currentTier === tierKey;
};

const selectTier = (tierKey: string) => {
    router.post(route('bizboost.subscription.checkout'), {
        tier: tierKey,
        billing_cycle: billingCycle.value,
    });
};

const formatPrice = (price: number) => {
    if (price === 0) return 'K0';
    return `K${price.toLocaleString()}`;
};

const formatLimit = (value: number) => {
    if (value === -1) return 'Unlimited';
    if (value === 0) return '—';
    return value.toLocaleString();
};

// Get tier icon
const getTierIcon = (tierKey: string) => {
    switch (tierKey) {
        case 'free': return SparklesIcon;
        case 'basic': return StarIcon;
        case 'professional': return RocketLaunchIcon;
        case 'business': return BuildingOfficeIcon;
        default: return SparklesIcon;
    }
};

// Get tier color classes
const getTierColors = (tierKey: string, isPopular: boolean = false) => {
    if (isPopular) {
        return {
            border: 'border-violet-500 ring-2 ring-violet-500',
            badge: 'bg-violet-500 text-white',
            button: 'bg-violet-600 hover:bg-violet-700 text-white',
            icon: 'text-violet-600',
        };
    }
    switch (tierKey) {
        case 'free':
            return {
                border: 'border-gray-200',
                badge: 'bg-gray-100 text-gray-600',
                button: 'bg-gray-600 hover:bg-gray-700 text-white',
                icon: 'text-gray-500',
            };
        case 'basic':
            return {
                border: 'border-blue-200',
                badge: 'bg-blue-100 text-blue-600',
                button: 'bg-blue-600 hover:bg-blue-700 text-white',
                icon: 'text-blue-600',
            };
        case 'professional':
            return {
                border: 'border-violet-200',
                badge: 'bg-violet-100 text-violet-600',
                button: 'bg-violet-600 hover:bg-violet-700 text-white',
                icon: 'text-violet-600',
            };
        case 'business':
            return {
                border: 'border-indigo-200',
                badge: 'bg-indigo-100 text-indigo-600',
                button: 'bg-indigo-600 hover:bg-indigo-700 text-white',
                icon: 'text-indigo-600',
            };
        default:
            return {
                border: 'border-gray-200',
                badge: 'bg-gray-100 text-gray-600',
                button: 'bg-gray-600 hover:bg-gray-700 text-white',
                icon: 'text-gray-500',
            };
    }
};

// Key limits to highlight
const keyLimits = ['posts_per_month', 'ai_credits_per_month', 'customers', 'team_members', 'locations'];
</script>

<template>
    <Head title="Choose Your Plan - BizBoost" />

    <BizBoostLayout>
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="text-center mb-10">
                    <h1 class="text-3xl font-bold text-gray-900">Choose Your Plan</h1>
                    <p class="mt-3 text-lg text-gray-600">Scale your business with the right marketing tools</p>
                    
                    <!-- Admin Notice -->
                    <div v-if="isAdmin" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-700 rounded-lg">
                        <CheckCircleIcon class="h-5 w-5" aria-hidden="true" />
                        <span class="font-medium">Administrator - Full Access to All Features</span>
                    </div>
                    
                    <!-- Billing Toggle -->
                    <div v-if="!isAdmin" class="mt-8 inline-flex items-center bg-gray-100 rounded-xl p-1.5">
                        <button
                            @click="billingCycle = 'monthly'"
                            :class="[
                                'px-5 py-2.5 rounded-lg text-sm font-medium transition-all duration-200',
                                billingCycle === 'monthly' 
                                    ? 'bg-white shadow-sm text-gray-900' 
                                    : 'text-gray-600 hover:text-gray-900'
                            ]"
                        >
                            Monthly
                        </button>
                        <button
                            @click="billingCycle = 'annual'"
                            :class="[
                                'px-5 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-2',
                                billingCycle === 'annual' 
                                    ? 'bg-white shadow-sm text-gray-900' 
                                    : 'text-gray-600 hover:text-gray-900'
                            ]"
                        >
                            Annual
                            <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-full">
                                Save 20%
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Pricing Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div
                        v-for="(config, key) in tiers"
                        :key="key"
                        :class="[
                            'relative bg-white rounded-2xl border-2 overflow-hidden transition-all duration-200 hover:shadow-lg',
                            getTierColors(key as string, config.popular).border,
                            isCurrentTier(key as string) ? 'ring-2 ring-blue-500 ring-offset-2' : ''
                        ]"
                    >
                        <!-- Popular Badge -->
                        <div 
                            v-if="config.popular" 
                            class="absolute top-0 right-0 px-3 py-1 bg-violet-500 text-white text-xs font-semibold rounded-bl-lg"
                        >
                            Most Popular
                        </div>
                        
                        <!-- Current Plan Badge -->
                        <div 
                            v-if="isCurrentTier(key as string)" 
                            class="absolute top-0 left-0 px-3 py-1 bg-blue-500 text-white text-xs font-semibold rounded-br-lg"
                        >
                            Current Plan
                        </div>

                        <div class="p-6">
                            <!-- Tier Header -->
                            <div class="flex items-center gap-3 mb-4">
                                <div :class="['p-2 rounded-lg bg-gray-50', getTierColors(key as string, config.popular).icon]">
                                    <component :is="getTierIcon(key as string)" class="h-6 w-6" aria-hidden="true" />
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">{{ config.name }}</h3>
                                </div>
                            </div>
                            
                            <!-- Price -->
                            <div class="mb-4">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-4xl font-bold text-gray-900">
                                        {{ formatPrice(billingCycle === 'monthly' ? config.price_monthly : config.price_annual) }}
                                    </span>
                                    <span class="text-gray-500 text-sm">
                                        /{{ billingCycle === 'monthly' ? 'month' : 'year' }}
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">{{ config.description }}</p>
                            </div>

                            <!-- Key Limits -->
                            <div class="mb-6 space-y-2">
                                <div 
                                    v-for="limitKey in keyLimits" 
                                    :key="limitKey"
                                    v-if="config.labeled_limits[limitKey]"
                                    class="flex items-center justify-between text-sm"
                                >
                                    <span class="text-gray-600">{{ config.labeled_limits[limitKey].label }}</span>
                                    <span :class="[
                                        'font-semibold',
                                        config.labeled_limits[limitKey].value === -1 ? 'text-emerald-600' : 
                                        config.labeled_limits[limitKey].value === 0 ? 'text-gray-400' : 'text-gray-900'
                                    ]">
                                        {{ formatLimit(config.labeled_limits[limitKey].value) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Features List -->
                            <div class="border-t pt-4">
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Features</p>
                                <ul class="space-y-2.5">
                                    <li 
                                        v-for="feature in config.labeled_features.slice(0, 8)" 
                                        :key="feature.key" 
                                        class="flex items-start gap-2"
                                    >
                                        <CheckIcon class="h-4 w-4 text-emerald-500 flex-shrink-0 mt-0.5" aria-hidden="true" />
                                        <span class="text-sm text-gray-600">{{ feature.label }}</span>
                                    </li>
                                    <li v-if="config.labeled_features.length > 8" class="text-sm text-gray-500 pl-6">
                                        +{{ config.labeled_features.length - 8 }} more features
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Action Button -->
                        <div class="p-6 pt-0">
                            <button
                                v-if="canUpgradeTo(key as string)"
                                @click="selectTier(key as string)"
                                :class="[
                                    'w-full px-4 py-3 rounded-xl font-semibold transition-colors',
                                    getTierColors(key as string, config.popular).button
                                ]"
                            >
                                {{ props.currentTier === 'free' ? 'Get Started' : 'Upgrade' }}
                            </button>
                            <div 
                                v-else-if="isCurrentTier(key as string)" 
                                class="w-full px-4 py-3 text-center text-gray-500 font-medium bg-gray-50 rounded-xl"
                            >
                                Current Plan
                            </div>
                            <div 
                                v-else 
                                class="w-full px-4 py-3 text-center text-gray-400 font-medium bg-gray-50 rounded-xl"
                            >
                                {{ getTierIndex(key as string) < getTierIndex(currentTier) ? 'Included' : 'Contact Support' }}
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Current Usage Section -->
                <div class="mt-12 bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-900">Current Usage</h2>
                        <p class="text-sm text-gray-500">Track your usage across different metrics</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                            <div 
                                v-for="(usage, key) in usageSummary" 
                                :key="key" 
                                v-if="typeof usage === 'object' && 'used' in usage && 'limit' in usage"
                                class="p-4 bg-gray-50 rounded-xl"
                            >
                                <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">
                                    {{ String(key).replace(/_/g, ' ') }}
                                </div>
                                <div class="text-xl font-bold text-gray-900">
                                    {{ usage.used }}
                                    <span class="text-sm font-normal text-gray-500">
                                        / {{ usage.limit === -1 ? '∞' : usage.limit }}
                                    </span>
                                </div>
                                <div 
                                    v-if="usage.limit !== -1 && usage.limit > 0" 
                                    class="mt-2 h-1.5 bg-gray-200 rounded-full overflow-hidden"
                                >
                                    <div
                                        :class="[
                                            'h-full rounded-full transition-all',
                                            (usage.used / usage.limit) >= 0.9 ? 'bg-red-500' :
                                            (usage.used / usage.limit) >= 0.7 ? 'bg-amber-500' : 'bg-emerald-500'
                                        ]"
                                        :style="{ width: `${Math.min((usage.used / usage.limit) * 100, 100)}%` }"
                                    ></div>
                                </div>
                                <div v-else-if="usage.limit === -1" class="mt-2 text-xs text-emerald-600 font-medium">
                                    Unlimited
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feature Comparison Table (Desktop) -->
                <div class="mt-12 hidden lg:block">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h2 class="text-lg font-semibold text-gray-900">Feature Comparison</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Feature</th>
                                        <th 
                                            v-for="(config, key) in tiers" 
                                            :key="key"
                                            class="px-6 py-4 text-center text-sm font-semibold text-gray-900"
                                        >
                                            {{ config.name }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <!-- Collect all unique features -->
                                    <tr 
                                        v-for="feature in Object.values(tiers).flatMap(t => t.labeled_features).filter((f, i, arr) => arr.findIndex(x => x.key === f.key) === i)"
                                        :key="feature.key"
                                        class="hover:bg-gray-50"
                                    >
                                        <td class="px-6 py-3 text-sm text-gray-700">{{ feature.label }}</td>
                                        <td 
                                            v-for="(config, tierKey) in tiers" 
                                            :key="tierKey"
                                            class="px-6 py-3 text-center"
                                        >
                                            <CheckIcon 
                                                v-if="config.features.includes(feature.key)" 
                                                class="h-5 w-5 text-emerald-500 mx-auto" 
                                                aria-hidden="true"
                                            />
                                            <XMarkIcon 
                                                v-else 
                                                class="h-5 w-5 text-gray-300 mx-auto" 
                                                aria-hidden="true"
                                            />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- FAQ Section -->
                <div class="mt-12 text-center">
                    <p class="text-gray-600">
                        Have questions? 
                        <a href="#" class="text-violet-600 hover:text-violet-700 font-medium">Contact our support team</a>
                    </p>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
