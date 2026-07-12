<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import QuickInvoiceLayout from '@/layouts/QuickInvoiceLayout.vue';
import {
    CheckIcon,
    XMarkIcon,
    DocumentTextIcon,
    StarIcon,
    RocketLaunchIcon,
    BuildingOfficeIcon,
} from '@heroicons/vue/24/outline';

interface Plan {
    id: number;
    name: string;
    price: number;
    currency: string;
    formatted_price: string;
    documents_per_month: number;
    features: Record<string, any>;
    is_free: boolean;
}

interface CurrentSubscription {
    id: string;
    tier: { id: number; name: string; price: number; formatted_price: string; documents_per_month: number };
    status: string;
    is_trial: boolean;
    trial_ends_at: string | null;
    expires_at: string | null;
    monthly_usage: number;
    remaining_documents: number;
    usage_percentage: number;
    can_create_document: boolean;
    is_paid: boolean;
    last_payment_at: string | null;
    payment_method: string | null;
}

const props = defineProps<{
    plans: Plan[];
    currentSubscription: CurrentSubscription | null;
}>();

const upgrading = ref<number | null>(null);

const isCurrentPlan = (planId: number) => props.currentSubscription?.tier.id === planId;

const getPlanIcon = (name: string) => {
    switch (name) {
        case 'Free': return DocumentTextIcon;
        case 'Basic': return StarIcon;
        case 'Pro': return RocketLaunchIcon;
        case 'Enterprise': return BuildingOfficeIcon;
        default: return DocumentTextIcon;
    }
};

const getPlanColor = (name: string) => {
    switch (name) {
        case 'Free': return 'gray';
        case 'Basic': return 'blue';
        case 'Pro': return 'purple';
        case 'Enterprise': return 'amber';
        default: return 'gray';
    }
};

const featureLabels: Record<string, string> = {
    templates: 'Available Templates',
    sharing: 'Sharing Options',
    watermark: 'No Watermark',
    customization: 'Customization',
    design_studio: 'Design Studio',
    custom_branding: 'Custom Branding',
    advanced_templates: 'Advanced Templates',
    api_access: 'API Access',
    priority_support: 'Priority Support',
    white_label: 'White Label',
    advanced_analytics: 'Advanced Analytics',
    cms_integration: 'CMS Integration',
};

const hasFeature = (features: Record<string, any>, key: string): boolean => {
    const val = features[key];
    if (Array.isArray(val)) return val.length > 0;
    if (typeof val === 'boolean') return val;
    if (val === 'all') return true;
    return !!val;
};

const documentLimitText = (plan: Plan) => {
    if (plan.documents_per_month === -1) return 'Unlimited documents';
    return `${plan.documents_per_month} documents/month`;
};

const upgradePlan = async (planId: number) => {
    upgrading.value = planId;
    try {
        await router.post(route('quick-invoice.subscription.upgrade'), {
            tier_id: planId,
            payment_method: 'wallet',
        });
    } finally {
        upgrading.value = null;
    }
};
</script>

<template>
    <QuickInvoiceLayout>
        <Head title="Subscription Plans - Quick Invoice" />

        <div class="max-w-6xl mx-auto py-8 px-4">
            <div class="text-center mb-10">
                <h1 class="text-3xl font-bold text-gray-900">Choose Your Plan</h1>
                <p class="mt-2 text-gray-600">Create invoices, quotations, receipts, and delivery notes with ease</p>
            </div>

            <!-- Current Subscription Status -->
            <div v-if="currentSubscription" class="mb-10 bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Current Plan</p>
                        <p class="text-xl font-bold text-gray-900">{{ currentSubscription.tier.name }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-500">Status</p>
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium"
                            :class="{
                                'bg-green-100 text-green-800': currentSubscription.status === 'active',
                                'bg-blue-100 text-blue-800': currentSubscription.status === 'trial',
                                'bg-gray-100 text-gray-800': currentSubscription.status === 'free',
                                'bg-red-100 text-red-800': currentSubscription.status === 'expired',
                            }"
                        >
                            <span v-if="currentSubscription.is_trial" class="animate-pulse">●</span>
                            {{ currentSubscription.status === 'trial' ? 'Trial' : currentSubscription.status }}
                        </span>
                    </div>
                    <div class="text-center">
                        <p class="text-sm text-gray-500">Usage</p>
                        <p class="text-lg font-semibold text-gray-900">{{ currentSubscription.monthly_usage }} / {{ currentSubscription.tier.documents_per_month === -1 ? '∞' : currentSubscription.tier.documents_per_month }}</p>
                    </div>
                    <div v-if="currentSubscription.is_trial && currentSubscription.trial_ends_at">
                        <p class="text-sm text-gray-500">Trial Ends</p>
                        <p class="text-lg font-semibold text-amber-600">{{ currentSubscription.trial_ends_at }}</p>
                    </div>
                    <div v-if="currentSubscription.is_paid">
                        <p class="text-sm text-gray-500">Last Payment</p>
                        <p class="text-lg font-semibold text-gray-900">{{ currentSubscription.last_payment_at }}</p>
                    </div>
                </div>
                <div v-if="currentSubscription.tier.documents_per_month > 0" class="mt-4">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full transition-all" :style="{ width: Math.min(currentSubscription.usage_percentage, 100) + '%' }"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">{{ currentSubscription.remaining_documents.toLocaleString() }} documents remaining this month</p>
                </div>
            </div>

            <!-- Plans Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="plan in plans" :key="plan.id"
                    class="relative rounded-2xl border-2 p-6 flex flex-col transition"
                    :class="isCurrentPlan(plan.id) ? 'border-blue-500 bg-blue-50 shadow-lg' : 'border-gray-200 bg-white hover:shadow-lg hover:border-blue-300'"
                >
                    <div v-if="plan.name === 'Pro'" class="absolute -top-3 left-1/2 -translate-x-1/2 bg-purple-600 text-white text-xs font-bold px-4 py-1 rounded-full">
                        Most Popular
                    </div>

                    <div class="text-center mb-6">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl mb-3"
                            :class="{ 'bg-gray-100': getPlanColor(plan.name) === 'gray', 'bg-blue-100': getPlanColor(plan.name) === 'blue', 'bg-purple-100': getPlanColor(plan.name) === 'purple', 'bg-amber-100': getPlanColor(plan.name) === 'amber' }"
                        >
                            <component :is="getPlanIcon(plan.name)" class="h-6 w-6"
                                :class="{ 'text-gray-600': getPlanColor(plan.name) === 'gray', 'text-blue-600': getPlanColor(plan.name) === 'blue', 'text-purple-600': getPlanColor(plan.name) === 'purple', 'text-amber-600': getPlanColor(plan.name) === 'amber' }"
                            />
                        </div>
                        <h3 class="text-lg font-bold text-gray-900">{{ plan.name }}</h3>
                        <div class="mt-2">
                            <span class="text-3xl font-extrabold text-gray-900">{{ plan.price === 0 ? 'Free' : plan.formatted_price }}</span>
                            <span v-if="plan.price > 0" class="text-sm text-gray-500">/month</span>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">{{ documentLimitText(plan) }}</p>
                    </div>

                    <div class="flex-1 space-y-3 mb-6">
                        <div v-for="(value, key) in plan.features" :key="key" class="flex items-start gap-2">
                            <component :is="hasFeature(plan.features, key) ? CheckIcon : XMarkIcon" class="h-4 w-4 mt-0.5 flex-shrink-0"
                                :class="hasFeature(plan.features, key) ? 'text-green-500' : 'text-red-400'"
                            />
                            <span class="text-sm" :class="hasFeature(plan.features, key) ? 'text-gray-700' : 'text-gray-400'">
                                {{ featureLabels[key] || key }}
                            </span>
                        </div>
                    </div>

                    <button v-if="!isCurrentPlan(plan.id) && !plan.is_free"
                        @click="upgradePlan(plan.id)"
                        :disabled="upgrading === plan.id"
                        class="w-full py-3 rounded-lg font-semibold text-sm bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    >
                        {{ upgrading === plan.id ? 'Processing...' : 'Upgrade' }}
                    </button>
                    <div v-else-if="isCurrentPlan(plan.id)" class="text-center py-3 text-sm font-medium text-gray-500 bg-gray-50 rounded-lg">
                        Current Plan
                    </div>
                    <div v-else class="text-center py-3 text-sm text-gray-400">Free</div>
                </div>
            </div>
        </div>
    </QuickInvoiceLayout>
</template>
