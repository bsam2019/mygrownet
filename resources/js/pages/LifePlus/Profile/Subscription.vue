<template>
    <LifePlusLayout>
        <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 pb-24">
            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white">
                <div class="max-w-7xl mx-auto px-4 py-8">
                    <div class="flex items-center gap-3 mb-2">
                        <SparklesIcon class="h-8 w-8" aria-hidden="true" />
                        <h1 class="text-2xl font-bold">My Subscription</h1>
                    </div>
                    <p class="text-indigo-100">Manage your Life+ plan and features</p>
                </div>
            </div>

            <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">
                <!-- Current Plan Badge -->
                <div class="bg-white rounded-2xl shadow-lg p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 mb-1">Current Plan</p>
                            <div class="flex items-center gap-2">
                                <span
                                    :class="[
                                        'px-3 py-1 rounded-full text-sm font-bold',
                                        currentTierStyle.badge,
                                    ]"
                                >
                                    {{ currentTierStyle.name }}
                                </span>
                                <span class="text-lg font-bold text-gray-900">{{
                                    currentTierStyle.price
                                }}</span>
                            </div>
                        </div>
                        <div
                            :class="[
                                'w-12 h-12 rounded-full flex items-center justify-center',
                                currentTierStyle.iconBg,
                            ]"
                        >
                            <component
                                :is="currentTierStyle.icon"
                                class="h-6 w-6"
                                :class="currentTierStyle.iconColor"
                                aria-hidden="true"
                            />
                        </div>
                    </div>
                </div>

                <!-- Wallet Balance Card -->
                <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl shadow-lg p-5 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-emerald-100 text-sm mb-1">Wallet Balance</p>
                            <p class="text-3xl font-bold">{{ formatCurrency(walletBalance) }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                            <WalletIcon class="h-6 w-6" aria-hidden="true" />
                        </div>
                    </div>
                    <button
                        @click="showTopUpModal = true"
                        class="inline-block mt-3 text-sm text-emerald-100 hover:text-white underline"
                    >
                        Top up wallet →
                    </button>
                </div>

                <!-- Upgrade CTA for Free Users -->
                <div
                    v-if="canUpgradeToPremium"
                    class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-2xl shadow-lg p-5 text-white"
                >
                    <div class="flex items-center gap-3 mb-3">
                        <SparklesIcon class="h-8 w-8" aria-hidden="true" />
                        <div>
                            <h3 class="font-bold text-lg">Unlock Full Potential</h3>
                            <p class="text-indigo-100 text-sm">Get unlimited access to all Life+ features</p>
                        </div>
                    </div>
                    <button
                        @click="openCheckout('premium', 'Premium', 25)"
                        class="w-full py-3 bg-white text-indigo-600 rounded-xl font-bold hover:bg-indigo-50 transition-colors"
                    >
                        {{ isFreeTier ? 'Upgrade to Premium - K25/month' : 'Subscribe to Premium - K25/month' }}
                    </button>
                </div>

                <!-- Usage Stats -->
                <div class="bg-white rounded-2xl shadow-lg p-5">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <ChartBarIcon class="h-5 w-5 text-indigo-500" aria-hidden="true" />
                        Your Usage
                    </h3>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="text-center p-3 bg-indigo-50 rounded-xl">
                            <div class="text-2xl font-bold text-indigo-600">
                                {{ access.usage?.tasks || 0 }}
                            </div>
                            <div class="text-xs text-gray-600">
                                / {{ access.limits?.tasks === -1 ? '∞' : access.limits?.tasks || 10 }} Tasks
                            </div>
                        </div>
                        <div class="text-center p-3 bg-purple-50 rounded-xl">
                            <div class="text-2xl font-bold text-purple-600">
                                {{ access.usage?.habits || 0 }}
                            </div>
                            <div class="text-xs text-gray-600">
                                / {{ access.limits?.habits === -1 ? '∞' : access.limits?.habits || 1 }} Habits
                            </div>
                        </div>
                        <div class="text-center p-3 bg-pink-50 rounded-xl">
                            <div class="text-2xl font-bold text-pink-600">
                                {{ access.usage?.chilimba_groups || 0 }}
                            </div>
                            <div class="text-xs text-gray-600">
                                /
                                {{
                                    access.limits?.chilimba_groups === -1
                                        ? '∞'
                                        : access.limits?.chilimba_groups || 0
                                }}
                                Chilimba
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Plans -->
                <div>
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2 px-1">
                        <CreditCardIcon class="h-5 w-5 text-indigo-500" aria-hidden="true" />
                        Available Plans
                    </h3>

                    <div class="space-y-4">
                        <!-- Free Plan -->
                        <div
                            :class="[
                                'bg-white rounded-2xl shadow-lg overflow-hidden border-2',
                                isFreeTier ? 'border-gray-400' : 'border-transparent',
                            ]"
                        >
                            <div class="p-5">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <h4 class="font-bold text-gray-900">Free</h4>
                                        <p class="text-2xl font-bold text-gray-900">K0</p>
                                    </div>
                                    <span
                                        v-if="isFreeTier"
                                        class="px-3 py-1 bg-gray-200 text-gray-700 rounded-full text-xs font-semibold"
                                    >
                                        Current
                                    </span>
                                </div>
                                <ul class="space-y-2 text-sm">
                                    <li class="flex items-center gap-2">
                                        <CheckIcon class="h-4 w-4 text-green-500" aria-hidden="true" />
                                        <span>10 tasks</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <CheckIcon class="h-4 w-4 text-green-500" aria-hidden="true" />
                                        <span>1 habit tracker</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <CheckIcon class="h-4 w-4 text-green-500" aria-hidden="true" />
                                        <span>Basic expense tracking</span>
                                    </li>
                                    <li class="flex items-center gap-2 text-gray-400">
                                        <XMarkIcon class="h-4 w-4" aria-hidden="true" />
                                        <span>No Chilimba groups</span>
                                    </li>
                                    <li class="flex items-center gap-2 text-gray-400">
                                        <XMarkIcon class="h-4 w-4" aria-hidden="true" />
                                        <span>No community posting</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Premium Plan -->
                        <div
                            :class="[
                                'bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl shadow-lg overflow-hidden border-2',
                                access.tier === 'premium' ? 'border-yellow-400' : 'border-transparent',
                            ]"
                        >
                            <div class="p-5 text-white">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-bold">Premium</h4>
                                            <span
                                                class="px-2 py-0.5 bg-yellow-400 text-yellow-900 rounded text-xs font-bold"
                                                >POPULAR</span
                                            >
                                        </div>
                                        <p class="text-2xl font-bold">K25<span class="text-sm font-normal">/month</span></p>
                                    </div>
                                    <span
                                        v-if="access.tier === 'premium'"
                                        class="px-3 py-1 bg-white/20 text-white rounded-full text-xs font-semibold"
                                    >
                                        Current
                                    </span>
                                </div>
                                <ul class="space-y-2 text-sm">
                                    <li class="flex items-center gap-2">
                                        <CheckIcon class="h-4 w-4 text-green-300" aria-hidden="true" />
                                        <span>Unlimited tasks</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <CheckIcon class="h-4 w-4 text-green-300" aria-hidden="true" />
                                        <span>Unlimited habits</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <CheckIcon class="h-4 w-4 text-green-300" aria-hidden="true" />
                                        <span>2 Chilimba groups</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <CheckIcon class="h-4 w-4 text-green-300" aria-hidden="true" />
                                        <span>Community posting</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <CheckIcon class="h-4 w-4 text-green-300" aria-hidden="true" />
                                        <span>Budget planning & analytics</span>
                                    </li>
                                </ul>
                                <button
                                    v-if="canUpgradeToPremium"
                                    @click="openCheckout('premium', 'Premium', 25)"
                                    class="w-full mt-4 py-3 bg-white text-indigo-600 rounded-xl font-bold hover:bg-indigo-50 transition-colors"
                                >
                                    {{ isFreeTier ? 'Upgrade to Premium - K25/month' : 'Subscribe - K25/month' }}
                                </button>
                                <p v-else class="mt-4 text-center text-sm text-indigo-100">
                                    You already have Premium or higher access
                                </p>
                            </div>
                        </div>

                        <!-- Member Free Plan -->
                        <div
                            :class="[
                                'bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl shadow-lg overflow-hidden border-2',
                                access.tier === 'member_free' ? 'border-yellow-400' : 'border-transparent',
                            ]"
                        >
                            <div class="p-5 text-white">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-bold">MyGrowNet Member</h4>
                                            <StarIcon class="h-5 w-5 text-yellow-300" aria-hidden="true" />
                                        </div>
                                        <p class="text-2xl font-bold">FREE</p>
                                    </div>
                                    <span
                                        v-if="access.tier === 'member_free'"
                                        class="px-3 py-1 bg-white/20 text-white rounded-full text-xs font-semibold"
                                    >
                                        Current
                                    </span>
                                </div>
                                <ul class="space-y-2 text-sm">
                                    <li class="flex items-center gap-2">
                                        <CheckIcon class="h-4 w-4 text-green-300" aria-hidden="true" />
                                        <span>All Premium features</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <CheckIcon class="h-4 w-4 text-green-300" aria-hidden="true" />
                                        <span>Unlimited Chilimba groups</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <CheckIcon class="h-4 w-4 text-green-300" aria-hidden="true" />
                                        <span>MyGrowNet integration</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <CheckIcon class="h-4 w-4 text-green-300" aria-hidden="true" />
                                        <span>Earnings tracking</span>
                                    </li>
                                </ul>
                                <p class="mt-4 text-sm text-emerald-100">
                                    Included with your MyGrowNet membership subscription
                                </p>
                            </div>
                        </div>

                        <!-- Elite Plan -->
                        <div
                            :class="[
                                'bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl shadow-lg overflow-hidden border-2',
                                access.tier === 'elite' ? 'border-yellow-400' : 'border-transparent',
                            ]"
                        >
                            <div class="p-5 text-white">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-bold">Elite</h4>
                                            <TrophyIcon class="h-5 w-5 text-yellow-300" aria-hidden="true" />
                                        </div>
                                        <p class="text-2xl font-bold">FREE</p>
                                    </div>
                                    <span
                                        v-if="access.tier === 'elite'"
                                        class="px-3 py-1 bg-white/20 text-white rounded-full text-xs font-semibold"
                                    >
                                        Current
                                    </span>
                                </div>
                                <ul class="space-y-2 text-sm">
                                    <li class="flex items-center gap-2">
                                        <CheckIcon class="h-4 w-4 text-green-300" aria-hidden="true" />
                                        <span>All Member features</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <CheckIcon class="h-4 w-4 text-green-300" aria-hidden="true" />
                                        <span>Priority support</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <CheckIcon class="h-4 w-4 text-green-300" aria-hidden="true" />
                                        <span>Advanced analytics</span>
                                    </li>
                                    <li class="flex items-center gap-2">
                                        <CheckIcon class="h-4 w-4 text-green-300" aria-hidden="true" />
                                        <span>Early access to new features</span>
                                    </li>
                                </ul>
                                <p class="mt-4 text-sm text-amber-100">
                                    For Professional+ level MyGrowNet members
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Join MyGrowNet CTA (for free/premium users) -->
                <div
                    v-if="showJoinCTA"
                    class="bg-white rounded-2xl shadow-lg p-5 border-2 border-dashed border-emerald-300"
                >
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                            <UserGroupIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900">Join MyGrowNet</h4>
                            <p class="text-sm text-gray-600 mt-1">
                                Get Life+ Premium FREE plus earn income through our community network.
                            </p>
                            <Link
                                href="/register"
                                class="inline-block mt-3 px-4 py-2 bg-emerald-500 text-white rounded-lg text-sm font-semibold hover:bg-emerald-600 transition-colors"
                            >
                                Learn More
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checkout Modal -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showCheckoutModal"
                class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
                @click="closeCheckout"
            >
                <div
                    class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6"
                    @click.stop
                >
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-900">Confirm Purchase</h3>
                        <button
                            @click="closeCheckout"
                            class="p-2 hover:bg-gray-100 rounded-full transition-colors"
                            aria-label="Close modal"
                        >
                            <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                        </button>
                    </div>

                    <!-- Plan Details -->
                    <div v-if="selectedPlan" class="space-y-4">
                        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-600">Plan</span>
                                <span class="font-bold text-gray-900">{{ selectedPlan.name }}</span>
                            </div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-600">Billing</span>
                                <span class="font-semibold text-gray-700">Monthly</span>
                            </div>
                            <div class="flex items-center justify-between pt-2 border-t border-indigo-200">
                                <span class="text-sm font-semibold text-gray-900">Total</span>
                                <span class="text-2xl font-bold text-indigo-600">{{ formatCurrency(selectedPlan.amount) }}</span>
                            </div>
                        </div>

                        <!-- Wallet Balance -->
                        <div class="bg-gray-50 rounded-2xl p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <WalletIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                                    <span class="text-sm text-gray-600">Wallet Balance</span>
                                </div>
                                <span class="font-bold text-gray-900">{{ formatCurrency(walletBalance) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">After Purchase</span>
                                <span
                                    class="font-semibold"
                                    :class="walletBalance >= selectedPlan.amount ? 'text-emerald-600' : 'text-red-600'"
                                >
                                    {{ formatCurrency(walletBalance - selectedPlan.amount) }}
                                </span>
                            </div>
                        </div>

                        <!-- Insufficient Balance Warning -->
                        <div
                            v-if="walletBalance < selectedPlan.amount"
                            class="bg-red-50 border border-red-200 rounded-2xl p-4 flex items-start gap-3"
                        >
                            <ExclamationTriangleIcon class="h-5 w-5 text-red-600 flex-shrink-0 mt-0.5" aria-hidden="true" />
                            <div>
                                <p class="text-sm font-semibold text-red-900">Insufficient Balance</p>
                                <p class="text-xs text-red-700 mt-1">
                                    You need {{ formatCurrency(selectedPlan.amount - walletBalance) }} more to complete this purchase.
                                </p>
                                <button
                                    @click="closeCheckout(); showTopUpModal = true;"
                                    class="inline-block mt-2 text-xs font-semibold text-red-600 hover:text-red-700 underline"
                                >
                                    Top up wallet →
                                </button>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3 pt-2">
                            <button
                                @click="closeCheckout"
                                class="flex-1 py-3 px-4 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-colors"
                                :disabled="processing"
                            >
                                Cancel
                            </button>
                            <button
                                @click="confirmPurchase"
                                class="flex-1 py-3 px-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl font-bold hover:from-indigo-600 hover:to-purple-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="processing || walletBalance < selectedPlan.amount"
                            >
                                {{ processing ? 'Processing...' : 'Confirm Purchase' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Shared Top Up Modal -->
        <WalletTopUpModal
            :show="showTopUpModal"
            :balance="walletBalance"
            @close="showTopUpModal = false"
            @success="onTopUpSuccess"
        />
    </LifePlusLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import LifePlusLayout from '@/layouts/LifePlusLayout.vue';
import WalletTopUpModal from '@/Components/Shared/WalletTopUpModal.vue';
import {
    SparklesIcon,
    CheckIcon,
    XMarkIcon,
    ChartBarIcon,
    CreditCardIcon,
    StarIcon,
    TrophyIcon,
    UserGroupIcon,
    GiftIcon,
    WalletIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

interface AccessInfo {
    tier: string;
    tier_name: string;
    features: Record<string, boolean>;
    limits: Record<string, number>;
    usage: {
        tasks: number;
        habits: number;
        chilimba_groups: number;
    };
    can_upgrade: boolean;
    upgrade_benefits: string[];
}

const props = defineProps<{
    walletBalance: number;
}>();

const page = usePage();
const access = computed<AccessInfo>(
    () =>
        (page.props.lifeplusAccess as AccessInfo) || {
            tier: 'free',
            tier_name: 'Free',
            features: {},
            limits: { tasks: 10, habits: 1, chilimba_groups: 0 },
            usage: { tasks: 0, habits: 0, chilimba_groups: 0 },
            can_upgrade: true,
            upgrade_benefits: [],
        },
);

const showCheckoutModal = ref(false);
const showTopUpModal = ref(false);
const selectedPlan = ref<{
    tier: string;
    name: string;
    amount: number;
    billing_cycle: string;
} | null>(null);
const processing = ref(false);

// Helper computed properties for tier checks
const isFreeTier = computed(() => {
    const tier = access.value?.tier;
    // Show as free if tier is free, none, empty, undefined
    return !tier || tier === 'free' || tier === 'none' || tier === '';
});

const isPremiumTier = computed(() => {
    const tier = access.value?.tier;
    return tier === 'premium';
});

const isMemberTier = computed(() => {
    const tier = access.value?.tier;
    return tier === 'member_free';
});

const isEliteTier = computed(() => {
    const tier = access.value?.tier;
    return tier === 'elite';
});

const isBusinessTier = computed(() => {
    const tier = access.value?.tier;
    return tier === 'business';
});

const canUpgradeToPremium = computed(() => {
    const tier = access.value?.tier;
    // Can upgrade to premium if NOT already on premium or higher
    const higherTiers = ['premium', 'member_free', 'elite', 'business'];
    return !tier || !higherTiers.includes(tier);
});

const canUpgrade = computed(() => {
    const tier = access.value?.tier;
    // Can upgrade if on free or none tier (not premium, member, elite, or business)
    return !tier || tier === 'free' || tier === 'none' || tier === '';
});

const showJoinCTA = computed(() => {
    const tier = access.value?.tier;
    // Show join CTA for free and premium users (not for members/elite/business)
    const memberTiers = ['member_free', 'elite', 'business'];
    return !tier || !memberTiers.includes(tier);
});

const currentTierStyle = computed(() => {
    switch (access.value?.tier) {
        case 'premium':
            return {
                name: 'Premium',
                price: 'K25/month',
                badge: 'bg-gradient-to-r from-indigo-500 to-purple-500 text-white',
                iconBg: 'bg-indigo-100',
                iconColor: 'text-indigo-600',
                icon: SparklesIcon,
            };
        case 'business':
            return {
                name: 'Business (Admin)',
                price: 'Full Access',
                badge: 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white',
                iconBg: 'bg-blue-100',
                iconColor: 'text-blue-600',
                icon: TrophyIcon,
            };
        case 'member_free':
            return {
                name: 'Member',
                price: 'Free',
                badge: 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white',
                iconBg: 'bg-emerald-100',
                iconColor: 'text-emerald-600',
                icon: StarIcon,
            };
        case 'elite':
            return {
                name: 'Elite',
                price: 'Free',
                badge: 'bg-gradient-to-r from-amber-500 to-orange-500 text-white',
                iconBg: 'bg-amber-100',
                iconColor: 'text-amber-600',
                icon: TrophyIcon,
            };
        default:
            return {
                name: 'Free',
                price: 'K0',
                badge: 'bg-gray-200 text-gray-700',
                iconBg: 'bg-gray-100',
                iconColor: 'text-gray-600',
                icon: GiftIcon,
            };
    }
});

const openCheckout = (tier: string, name: string, amount: number) => {
    selectedPlan.value = {
        tier,
        name,
        amount,
        billing_cycle: 'monthly',
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
        route('lifeplus.subscription.purchase'),
        {
            module_id: 'lifeplus',
            tier: selectedPlan.value.tier,
            amount: selectedPlan.value.amount,
            billing_cycle: selectedPlan.value.billing_cycle,
        },
        {
            onFinish: () => {
                processing.value = false;
                closeCheckout();
            },
        }
    );
};

const formatCurrency = (amount: number) => {
    return `K${amount.toLocaleString()}`;
};

const onTopUpSuccess = (amount: number) => {
    // Refresh the page to get updated wallet balance
    router.reload({ only: ['walletBalance'] });
};
</script>
