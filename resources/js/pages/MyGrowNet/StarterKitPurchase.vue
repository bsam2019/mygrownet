<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { ShoppingBagIcon, CheckCircleIcon, AlertCircleIcon, BanknoteIcon } from 'lucide-vue-next';

interface ContentItem {
    id: number;
    title: string;
    description: string | null;
    category: string;
    unlock_day: number;
    estimated_value: number;
    category_label: string;
}

interface Tier {
    name: string;
    price: number;
    shopCredit: number;
    lgrMultiplier?: number;
    lgrDailyRate?: number;
    lgrMaxEarnings?: number;
    lpAward?: number;
}

interface Props {
    tiers: {
        lite: Tier;
        basic: Tier;
        growth_plus: Tier;
        pro: Tier;
    };
    walletBalance: number;
    paymentMethods: Array<{
        id: string;
        name: string;
        description: string;
        icon: string;
    }>;
    contentItems: Record<string, ContentItem[]>;
}

const props = defineProps<Props>();

// Get selected tier based on form
const selectedTier = computed(() => {
    switch(form.tier) {
        case 'lite': return props.tiers.lite;
        case 'growth_plus': return props.tiers.growth_plus;
        case 'pro': return props.tiers.pro;
        default: return props.tiers.basic;
    }
});
const price = computed(() => selectedTier.value.price);
const shopCredit = computed(() => selectedTier.value.shopCredit);

const totalValue = computed(() => {
    let total = 0;
    Object.values(props.contentItems).forEach(items => {
        items.forEach(item => {
            total += item.estimated_value;
        });
    });
    return total + shopCredit.value;
});

const getCategoryIcon = (category: string) => {
    const icons: Record<string, string> = {
        training: '📚',
        ebook: '📖',
        video: '🎥',
        tool: '🛠️',
        library: '📚',
    };
    return icons[category] || '📄';
};

const form = useForm({
    tier: 'basic',
    payment_method: 'wallet', // Always wallet
    terms_accepted: false, // MUST be false - user must actively consent
});

const attemptedSubmit = ref(false);

const submit = () => {
    attemptedSubmit.value = true;
    
    // Validate terms acceptance
    if (!form.terms_accepted) {
        // Scroll to terms section
        const termsSection = document.querySelector('input[type="checkbox"]');
        if (termsSection) {
            termsSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
            termsSection.focus();
        }
        return;
    }
    
    form.post(route('mygrownet.starter-kit.store'));
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(amount);
};
</script>

<template>
    <Head title="Purchase Starter Kit" />

    <MemberLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Back Button -->
                <div class="mb-6">
                    <Link :href="route('mygrownet.starter-kit.show')" class="text-blue-600 hover:text-blue-800 flex items-center text-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Starter Kit
                    </Link>
                </div>

                <!-- Purchase Form -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-8 text-white">
                        <h1 class="text-3xl font-bold">Complete Your Purchase</h1>
                        <p class="mt-2 text-blue-100">Get instant access to your Starter Kit</p>
                    </div>

                    <!-- Tier Selection -->
                    <div class="px-6 py-6 bg-white border-b">
                        <h3 class="font-semibold text-gray-900 mb-4">Choose Your Tier</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Lite Tier -->
                            <div
                                @click="form.tier = 'lite'"
                                :class="[
                                    'border-2 rounded-lg p-4 cursor-pointer transition',
                                    form.tier === 'lite'
                                        ? 'border-gray-600 bg-gray-50'
                                        : 'border-gray-200 hover:border-gray-300'
                                ]"
                            >
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <h4 class="text-base font-bold text-gray-900">Lite</h4>
                                        <p class="text-xl font-bold text-gray-600 mt-1">{{ formatCurrency(tiers.lite.price) }}</p>
                                    </div>
                                    <div v-if="form.tier === 'lite'" class="flex-shrink-0">
                                        <CheckCircleIcon class="w-5 h-5 text-gray-600" />
                                    </div>
                                </div>
                                <ul class="space-y-1.5 text-xs text-gray-700">
                                    <li class="flex items-start">
                                        <CheckCircleIcon class="w-3 h-3 text-gray-500 mr-1.5 mt-0.5 flex-shrink-0" />
                                        <span>Basic content</span>
                                    </li>
                                    <li class="flex items-start">
                                        <CheckCircleIcon class="w-3 h-3 text-gray-500 mr-1.5 mt-0.5 flex-shrink-0" />
                                        <span>{{ formatCurrency(tiers.lite.shopCredit) }} shop credit</span>
                                    </li>
                                    <li class="flex items-start">
                                        <CheckCircleIcon class="w-3 h-3 text-gray-500 mr-1.5 mt-0.5 flex-shrink-0" />
                                        <span>+{{ tiers.lite.lpAward || 15 }} LP</span>
                                    </li>
                                    <li class="flex items-start">
                                        <CheckCircleIcon class="w-3 h-3 text-gray-500 mr-1.5 mt-0.5 flex-shrink-0" />
                                        <span class="font-semibold">LGR: K{{ tiers.lite.lgrDailyRate || 12.50 }}/day</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Basic Tier -->
                            <div
                                @click="form.tier = 'basic'"
                                :class="[
                                    'border-2 rounded-lg p-4 cursor-pointer transition',
                                    form.tier === 'basic'
                                        ? 'border-blue-600 bg-blue-50'
                                        : 'border-gray-200 hover:border-blue-300'
                                ]"
                            >
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <h4 class="text-base font-bold text-gray-900">Basic</h4>
                                        <p class="text-xl font-bold text-blue-600 mt-1">{{ formatCurrency(tiers.basic.price) }}</p>
                                    </div>
                                    <div v-if="form.tier === 'basic'" class="flex-shrink-0">
                                        <CheckCircleIcon class="w-5 h-5 text-blue-600" />
                                    </div>
                                </div>
                                <ul class="space-y-1.5 text-xs text-gray-700">
                                    <li class="flex items-start">
                                        <CheckCircleIcon class="w-3 h-3 text-blue-500 mr-1.5 mt-0.5 flex-shrink-0" />
                                        <span>All content</span>
                                    </li>
                                    <li class="flex items-start">
                                        <CheckCircleIcon class="w-3 h-3 text-blue-500 mr-1.5 mt-0.5 flex-shrink-0" />
                                        <span>{{ formatCurrency(tiers.basic.shopCredit) }} shop credit</span>
                                    </li>
                                    <li class="flex items-start">
                                        <CheckCircleIcon class="w-3 h-3 text-blue-500 mr-1.5 mt-0.5 flex-shrink-0" />
                                        <span>+{{ tiers.basic.lpAward || 25 }} LP</span>
                                    </li>
                                    <li class="flex items-start">
                                        <CheckCircleIcon class="w-3 h-3 text-blue-500 mr-1.5 mt-0.5 flex-shrink-0" />
                                        <span class="font-semibold">LGR: K{{ tiers.basic.lgrDailyRate || 25 }}/day</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Growth Plus Tier -->
                            <div
                                @click="form.tier = 'growth_plus'"
                                :class="[
                                    'border-2 rounded-lg p-4 cursor-pointer transition relative',
                                    form.tier === 'growth_plus'
                                        ? 'border-emerald-600 bg-emerald-50'
                                        : 'border-gray-200 hover:border-emerald-300'
                                ]"
                            >
                                <div class="absolute top-0 right-0 bg-emerald-600 text-white text-xs font-bold px-2 py-0.5 rounded-bl-lg rounded-tr-lg">
                                    POPULAR
                                </div>
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <h4 class="text-base font-bold text-gray-900">Growth Plus</h4>
                                        <p class="text-xl font-bold text-emerald-600 mt-1">{{ formatCurrency(tiers.growth_plus.price) }}</p>
                                    </div>
                                    <div v-if="form.tier === 'growth_plus'" class="flex-shrink-0">
                                        <CheckCircleIcon class="w-5 h-5 text-emerald-600" />
                                    </div>
                                </div>
                                <ul class="space-y-1.5 text-xs text-gray-700">
                                    <li class="flex items-start">
                                        <CheckCircleIcon class="w-3 h-3 text-emerald-500 mr-1.5 mt-0.5 flex-shrink-0" />
                                        <span>All content + extras</span>
                                    </li>
                                    <li class="flex items-start">
                                        <CheckCircleIcon class="w-3 h-3 text-emerald-500 mr-1.5 mt-0.5 flex-shrink-0" />
                                        <span>{{ formatCurrency(tiers.growth_plus.shopCredit) }} shop credit</span>
                                    </li>
                                    <li class="flex items-start">
                                        <CheckCircleIcon class="w-3 h-3 text-emerald-500 mr-1.5 mt-0.5 flex-shrink-0" />
                                        <span>+{{ tiers.growth_plus.lpAward || 50 }} LP</span>
                                    </li>
                                    <li class="flex items-start">
                                        <CheckCircleIcon class="w-3 h-3 text-emerald-500 mr-1.5 mt-0.5 flex-shrink-0" />
                                        <span class="font-semibold">LGR: K{{ tiers.growth_plus.lgrDailyRate || 37.50 }}/day</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Pro Tier -->
                            <div
                                @click="form.tier = 'pro'"
                                :class="[
                                    'border-2 rounded-lg p-4 cursor-pointer transition relative',
                                    form.tier === 'pro'
                                        ? 'border-purple-600 bg-purple-50'
                                        : 'border-gray-200 hover:border-purple-300'
                                ]"
                            >
                                <div class="absolute top-0 right-0 bg-purple-600 text-white text-xs font-bold px-2 py-0.5 rounded-bl-lg rounded-tr-lg">
                                    BEST VALUE
                                </div>
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <h4 class="text-base font-bold text-gray-900">Pro</h4>
                                        <p class="text-xl font-bold text-purple-600 mt-1">{{ formatCurrency(tiers.pro.price) }}</p>
                                    </div>
                                    <div v-if="form.tier === 'pro'" class="flex-shrink-0">
                                        <CheckCircleIcon class="w-5 h-5 text-purple-600" />
                                    </div>
                                </div>
                                <ul class="space-y-1.5 text-xs text-gray-700">
                                    <li class="flex items-start">
                                        <CheckCircleIcon class="w-3 h-3 text-purple-500 mr-1.5 mt-0.5 flex-shrink-0" />
                                        <span>Full library access</span>
                                    </li>
                                    <li class="flex items-start">
                                        <CheckCircleIcon class="w-3 h-3 text-purple-500 mr-1.5 mt-0.5 flex-shrink-0" />
                                        <span>{{ formatCurrency(tiers.pro.shopCredit) }} shop credit</span>
                                    </li>
                                    <li class="flex items-start">
                                        <CheckCircleIcon class="w-3 h-3 text-purple-500 mr-1.5 mt-0.5 flex-shrink-0" />
                                        <span>+{{ tiers.pro.lpAward || 100 }} LP</span>
                                    </li>
                                    <li class="flex items-start">
                                        <CheckCircleIcon class="w-3 h-3 text-purple-500 mr-1.5 mt-0.5 flex-shrink-0" />
                                        <span class="font-semibold">LGR: K{{ tiers.pro.lgrDailyRate || 62.50 }}/day 🚀</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Value Summary -->
                    <div class="px-6 py-6 bg-gray-50 border-b">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">
                                    {{ form.tier === 'premium' ? 'Premium' : 'Basic' }} Starter Kit
                                </h2>
                                <p class="text-sm text-gray-600">Total Value: {{ formatCurrency(totalValue) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600 line-through">{{ formatCurrency(totalValue) }}</p>
                                <p class="text-3xl font-bold text-green-600">
                                    {{ formatCurrency(form.tier === 'premium' ? tiers.premium.price : tiers.basic.price) }}
                                </p>
                                <p class="text-sm text-green-600">
                                    Save {{ formatCurrency(totalValue - (form.tier === 'premium' ? tiers.premium.price : tiers.basic.price)) }} 
                                    ({{ Math.round((totalValue - (form.tier === 'premium' ? tiers.premium.price : tiers.basic.price)) / totalValue * 100) }}%)
                                </p>
                            </div>
                        </div>

                        <!-- What's Included Breakdown -->
                        <div class="border-t pt-6">
                            <h3 class="font-semibold text-gray-900 mb-4">What's Included:</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Dynamic Content Items by Category -->
                                <div
                                    v-for="(items, category) in contentItems"
                                    :key="category"
                                    class="bg-white rounded-lg p-4 border border-gray-200"
                                >
                                    <div class="flex items-start">
                                        <span class="text-2xl mr-3 flex-shrink-0">{{ getCategoryIcon(category) }}</span>
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-gray-900">
                                                {{ items.length }} {{ items[0]?.category_label || category }}{{ items.length > 1 ? 's' : '' }}
                                            </h4>
                                            <ul class="text-sm text-gray-600 mt-1 space-y-1">
                                                <li v-for="item in items" :key="item.id">
                                                    • {{ item.title }}
                                                </li>
                                            </ul>
                                            <p class="text-xs text-gray-500 mt-2">
                                                Value: {{ formatCurrency(items.reduce((sum, item) => sum + item.estimated_value, 0)) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bonuses -->
                                <div class="bg-white rounded-lg p-4 border border-gray-200 bg-gradient-to-br from-green-50 to-blue-50">
                                    <div class="flex items-start">
                                        <CheckCircleIcon class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" />
                                        <div>
                                            <h4 class="font-semibold text-gray-900">🎁 Instant Bonuses</h4>
                                            <ul class="text-sm text-gray-600 mt-1 space-y-1">
                                                <li>• {{ formatCurrency(form.tier === 'premium' ? tiers.premium.shopCredit : tiers.basic.shopCredit) }} Shop Credit (90 days)</li>
                                                <li>• +37.5 Lifetime Points</li>
                                                <li>• Achievement Badges</li>
                                                <li v-if="form.tier === 'premium'" class="font-semibold text-purple-700">• LGR Qualification 🚀</li>
                                            </ul>
                                            <p class="text-xs text-gray-500 mt-2">
                                                Value: {{ formatCurrency(form.tier === 'premium' ? tiers.premium.shopCredit : tiers.basic.shopCredit) }}
                                                <span v-if="form.tier === 'premium'"> + LGR Access</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="submit" class="px-6 py-8">
                        <!-- Wallet Balance Check -->
                        <div v-if="walletBalance >= price" class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <CheckCircleIcon class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" />
                                <div class="flex-1">
                                    <h4 class="font-semibold text-green-900">✓ Sufficient Wallet Balance</h4>
                                    <p class="text-sm text-green-700 mt-1">
                                        Your wallet balance: <span class="font-semibold">{{ formatCurrency(walletBalance) }}</span>
                                    </p>
                                    <p class="text-sm text-green-700 mt-1">
                                        {{ formatCurrency(price) }} will be deducted from your wallet.
                                    </p>
                                    <p class="text-sm text-green-700 mt-1">
                                        New balance after purchase: <span class="font-semibold">{{ formatCurrency(walletBalance - price) }}</span>
                                    </p>
                                    <p class="text-sm text-green-700 mt-2 font-semibold">
                                        You'll get instant access - no waiting for verification!
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Insufficient Balance - Redirect to Top Up -->
                        <div v-else class="mb-6 bg-amber-50 border-2 border-amber-300 rounded-lg p-6">
                            <div class="flex items-start">
                                <AlertCircleIcon class="w-6 h-6 text-amber-600 mt-0.5 mr-3 flex-shrink-0" />
                                <div class="flex-1">
                                    <h4 class="font-semibold text-amber-900 text-lg">Insufficient Wallet Balance</h4>
                                    <p class="text-sm text-amber-700 mt-2">
                                        Your current balance: <span class="font-semibold">{{ formatCurrency(walletBalance) }}</span>
                                    </p>
                                    <p class="text-sm text-amber-700 mt-1">
                                        Required amount: <span class="font-semibold">{{ formatCurrency(price) }}</span>
                                    </p>
                                    <p class="text-sm text-amber-700 mt-1">
                                        You need: <span class="font-semibold text-amber-900">{{ formatCurrency(price - walletBalance) }} more</span>
                                    </p>
                                    <div class="mt-4">
                                        <Link
                                            :href="route('mygrownet.payments.create', { type: 'wallet_topup', amount: price - walletBalance })"
                                            class="inline-flex items-center px-6 py-3 bg-amber-600 text-white font-semibold rounded-lg hover:bg-amber-700 transition-colors"
                                        >
                                            <BanknoteIcon class="w-5 h-5 mr-2" />
                                            Top Up Wallet
                                        </Link>
                                    </div>
                                    <p class="text-xs text-amber-600 mt-3">
                                        All purchases are made from your wallet balance. Please top up to continue.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mb-6">
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <h3 class="font-semibold text-gray-900 mb-2">Terms and Conditions</h3>
                                <div class="text-sm text-gray-700 space-y-2 max-h-48 overflow-y-auto">
                                    <p>By purchasing the MyGrowNet Starter Kit, you agree to:</p>
                                    <ul class="list-disc list-inside space-y-1 ml-2">
                                        <li>This is a one-time purchase of digital products and services</li>
                                        <li>All digital content is delivered instantly and is non-refundable once accessed</li>
                                        <li>{{ formatCurrency(shopCredit) }} shop credit expires in 90 days</li>
                                        <li>Digital library access is valid for 30 days (renewable)</li>
                                        <li>Content is for personal use only and cannot be resold</li>
                                        <li>This is NOT an investment and does not guarantee any returns</li>
                                        <li v-if="form.tier === 'premium'">Premium tier includes LGR qualification for quarterly profit sharing</li>
                                    </ul>
                                </div>
                            </div>

                            <label class="flex items-start mt-4 cursor-pointer group">
                                <input
                                    v-model="form.terms_accepted"
                                    type="checkbox"
                                    class="mt-1 h-5 w-5 text-blue-600 focus:ring-2 focus:ring-blue-500 border-gray-300 rounded cursor-pointer"
                                    :class="{ 'border-red-500': form.errors.terms_accepted }"
                                />
                                <span class="ml-3 text-sm text-gray-700 group-hover:text-gray-900">
                                    I have read and agree to the Terms and Conditions above and understand that this purchase is <strong>non-refundable</strong> once I access the digital content.
                                </span>
                            </label>
                            <p v-if="form.errors.terms_accepted" class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                <AlertCircleIcon class="w-4 h-4" />
                                {{ form.errors.terms_accepted }}
                            </p>
                            <p v-if="!form.terms_accepted && attemptedSubmit" class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                <AlertCircleIcon class="w-4 h-4" />
                                You must accept the terms and conditions to continue
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-between">
                            <Link
                                :href="route('mygrownet.starter-kit.show')"
                                class="text-gray-600 hover:text-gray-800"
                            >
                                Cancel
                            </Link>
                            <button
                                v-if="walletBalance >= price"
                                type="submit"
                                :disabled="form.processing"
                                class="px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition"
                            >
                                <span v-if="form.processing">Processing...</span>
                                <span v-else>Complete Purchase - {{ formatCurrency(price) }}</span>
                            </button>
                            <Link
                                v-else
                                :href="route('mygrownet.payments.create', { type: 'wallet_topup', amount: price - walletBalance })"
                                class="px-8 py-3 bg-amber-600 text-white font-semibold rounded-lg hover:bg-amber-700 transition-colors inline-flex items-center"
                            >
                                <BanknoteIcon class="w-5 h-5 mr-2" />
                                Top Up Wallet
                            </Link>
                        </div>
                    </form>
                </div>

                <!-- What Happens Next -->
                <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-blue-900 mb-4">What You'll Receive</h3>
                    <div class="space-y-3">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm">✓</div>
                            <div class="ml-4">
                                <p class="font-medium text-blue-900">Complete Training Package</p>
                                <p class="text-sm text-blue-700">3 modules, 3 eBooks, 3 videos + marketing tools</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm">✓</div>
                            <div class="ml-4">
                                <p class="font-medium text-blue-900">{{ formatCurrency(shopCredit) }} Shop Credit</p>
                                <p class="text-sm text-blue-700">Use in MyGrowNet shop (valid 90 days)</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm">✓</div>
                            <div class="ml-4">
                                <p class="font-medium text-blue-900">Progressive Content Unlocking</p>
                                <p class="text-sm text-blue-700">New content unlocks over 30 days for optimal learning</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm">✓</div>
                            <div class="ml-4">
                                <p class="font-medium text-blue-900">+37.5 Lifetime Points</p>
                                <p class="text-sm text-blue-700">Instant LP bonus to boost your level</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
