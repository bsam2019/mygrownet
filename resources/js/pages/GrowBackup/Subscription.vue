<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { CheckIcon, ArrowUpIcon } from '@heroicons/vue/24/outline';
import GrowBackupLayout from '@/Layouts/GrowBackupLayout.vue';

interface StoragePlan {
    id: string;
    name: string;
    slug: string;
    quota_bytes: number;
    max_file_size_bytes: number;
    price_monthly: number;
    price_annual: number;
    allow_sharing: boolean;
    allow_public_profile_files: boolean;
    features: string[];
    is_popular?: boolean;
}

interface Props {
    plans: StoragePlan[];
    currentPlan: StoragePlan;
    usage: {
        used_bytes: number;
        files_count: number;
        percent_used: number;
    };
}

const props = defineProps<Props>();

const billingCycle = ref<'monthly' | 'annual'>('monthly');

const formatStorage = (bytes: number) => {
    if (bytes === 0) return '0 B';
    
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    let size = bytes;
    let unitIndex = 0;
    
    while (size >= 1024 && unitIndex < units.length - 1) {
        size /= 1024;
        unitIndex++;
    }
    
    return `${size.toFixed(2)} ${units[unitIndex]}`;
};

const formatFileSize = (bytes: number) => {
    const mb = bytes / (1024 * 1024);
    if (mb >= 1024) {
        return `${(mb / 1024).toFixed(0)} GB`;
    }
    return `${mb.toFixed(0)} MB`;
};

const getPrice = (plan: StoragePlan) => {
    return billingCycle.value === 'monthly' ? plan.price_monthly : plan.price_annual;
};

const isCurrentPlan = (plan: StoragePlan) => {
    return plan.id === props.currentPlan.id;
};

const canUpgrade = (plan: StoragePlan) => {
    return plan.quota_bytes > props.currentPlan.quota_bytes;
};

const handleUpgrade = (planId: string) => {
    router.post(`/growbackup/subscription/upgrade`, {
        plan_id: planId,
        billing_cycle: billingCycle.value,
    });
};
</script>

<template>
    <GrowBackupLayout title="GrowBackup - Subscription Plans">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="text-center mb-12">
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">
                        Choose Your Storage Plan
                    </h1>
                    <p class="text-lg text-gray-600 mb-8">
                        Secure cloud storage that grows with your needs
                    </p>
                    
                    <!-- Billing Toggle -->
                    <div class="inline-flex items-center bg-white rounded-lg p-1 shadow-sm border border-gray-200">
                        <button
                            @click="billingCycle = 'monthly'"
                            class="px-6 py-2 rounded-md text-sm font-medium transition-colors"
                            :class="billingCycle === 'monthly' 
                                ? 'bg-blue-600 text-white' 
                                : 'text-gray-700 hover:text-gray-900'"
                        >
                            Monthly
                        </button>
                        <button
                            @click="billingCycle = 'annual'"
                            class="px-6 py-2 rounded-md text-sm font-medium transition-colors relative"
                            :class="billingCycle === 'annual' 
                                ? 'bg-blue-600 text-white' 
                                : 'text-gray-700 hover:text-gray-900'"
                        >
                            Annual
                            <span class="ml-2 text-xs px-2 py-0.5 rounded-full"
                                :class="billingCycle === 'annual' 
                                    ? 'bg-white/20 text-white' 
                                    : 'bg-green-100 text-green-800'">
                                Save 17%
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Current Usage -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Current Usage</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Plan</p>
                            <p class="text-2xl font-bold text-gray-900">{{ currentPlan.name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Storage Used</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ formatStorage(usage.used_bytes) }} / {{ formatStorage(currentPlan.quota_bytes) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Files</p>
                            <p class="text-2xl font-bold text-gray-900">{{ usage.files_count }}</p>
                        </div>
                    </div>
                </div>

                <!-- Plans Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div
                        v-for="plan in plans"
                        :key="plan.id"
                        class="bg-white rounded-lg shadow-sm overflow-hidden relative"
                        :class="{ 
                            'ring-2 ring-blue-600': isCurrentPlan(plan),
                            'ring-2 ring-green-500': plan.is_popular && !isCurrentPlan(plan)
                        }"
                    >
                        <!-- Popular Badge -->
                        <div v-if="plan.is_popular" class="absolute top-0 right-0 bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-bl-lg">
                            MOST POPULAR
                        </div>

                        <!-- Plan Header -->
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">
                                {{ plan.name }}
                            </h3>
                            <div class="flex items-baseline mb-4">
                                <span v-if="plan.price_monthly === 0" class="text-4xl font-bold text-gray-900">
                                    Free
                                </span>
                                <template v-else>
                                    <span class="text-4xl font-bold text-gray-900">
                                        K{{ getPrice(plan) }}
                                    </span>
                                    <span class="ml-2 text-gray-600">
                                        /{{ billingCycle === 'monthly' ? 'mo' : 'yr' }}
                                    </span>
                                </template>
                            </div>
                            <p class="text-sm font-medium text-gray-900">
                                {{ formatStorage(plan.quota_bytes) }} storage
                            </p>
                        </div>

                        <!-- Features -->
                        <div class="p-6">
                            <ul class="space-y-3 mb-6">
                                <li v-for="(feature, index) in plan.features" :key="index" class="flex items-start gap-2">
                                    <CheckIcon class="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" aria-hidden="true" />
                                    <span class="text-sm text-gray-700">{{ feature }}</span>
                                </li>
                            </ul>

                            <!-- CTA Button -->
                            <button
                                v-if="isCurrentPlan(plan)"
                                disabled
                                class="w-full py-2 px-4 bg-gray-100 text-gray-600 rounded-lg font-medium cursor-not-allowed"
                            >
                                Current Plan
                            </button>
                            <button
                                v-else-if="canUpgrade(plan)"
                                @click="handleUpgrade(plan.id)"
                                class="w-full py-2 px-4 rounded-lg font-medium transition-colors flex items-center justify-center gap-2"
                                :class="plan.is_popular 
                                    ? 'bg-green-600 text-white hover:bg-green-700' 
                                    : 'bg-blue-600 text-white hover:bg-blue-700'"
                            >
                                <ArrowUpIcon class="h-5 w-5" aria-hidden="true" />
                                {{ plan.price_monthly === 0 ? 'Get Started' : 'Upgrade' }}
                            </button>
                            <button
                                v-else
                                disabled
                                class="w-full py-2 px-4 bg-gray-100 text-gray-600 rounded-lg font-medium cursor-not-allowed"
                            >
                                Lower Tier
                            </button>
                        </div>
                    </div>
                </div>

                <!-- FAQ Section -->
                <div class="mt-16 bg-white rounded-lg shadow-sm p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Frequently Asked Questions</h2>
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                Can I change my plan later?
                            </h3>
                            <p class="text-gray-600">
                                Yes! You can upgrade your plan at any time. Downgrades take effect at the end of your current billing period.
                            </p>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                What happens if I exceed my storage limit?
                            </h3>
                            <p class="text-gray-600">
                                You'll receive notifications when you reach 80% and 90% of your quota. At 100%, you won't be able to upload new files until you upgrade or delete existing files.
                            </p>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                Is my data secure?
                            </h3>
                            <p class="text-gray-600">
                                Yes! All files are encrypted in transit and at rest. We use enterprise-grade security and comply with international data protection standards.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GrowBackupLayout>
</template>
