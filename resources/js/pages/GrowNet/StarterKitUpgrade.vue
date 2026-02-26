<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { ArrowUpIcon, CheckCircleIcon, AlertCircleIcon, BanknoteIcon, SparklesIcon } from 'lucide-vue-next';

interface Props {
    currentTier: string;
    upgradeCost: number;
    walletBalance: number;
    premiumBenefits: string[];
}

const props = defineProps<Props>();

const form = useForm({
    terms_accepted: false,
});

const submit = () => {
    form.post(route('mygrownet.starter-kit.upgrade.process'));
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
    <Head title="Upgrade to Premium" />

    <MemberLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-100 rounded-full mb-4">
                        <SparklesIcon class="h-8 w-8 text-purple-600" />
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900">Upgrade to Premium</h1>
                    <p class="mt-2 text-gray-600">Unlock LGR and enhanced benefits</p>
                </div>

                <!-- Upgrade Card -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <!-- Current vs Premium -->
                    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-8 text-white">
                        <div class="flex items-center justify-center gap-4">
                            <div class="text-center">
                                <p class="text-sm text-purple-200">Current Tier</p>
                                <p class="text-2xl font-bold">Basic</p>
                            </div>
                            <ArrowUpIcon class="h-8 w-8" />
                            <div class="text-center">
                                <p class="text-sm text-purple-200">Upgrade To</p>
                                <p class="text-2xl font-bold">Premium</p>
                            </div>
                        </div>
                        <div class="mt-6 text-center">
                            <p class="text-3xl font-bold">{{ formatCurrency(upgradeCost) }}</p>
                            <p class="text-sm text-purple-200">One-time upgrade fee</p>
                        </div>
                    </div>

                    <!-- Wallet Balance Check -->
                    <div class="px-6 py-6">
                        <div v-if="walletBalance >= upgradeCost" class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <CheckCircleIcon class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" />
                                <div class="flex-1">
                                    <h4 class="font-semibold text-green-900">✓ Sufficient Wallet Balance</h4>
                                    <p class="text-sm text-green-700 mt-1">
                                        Your wallet balance: <span class="font-semibold">{{ formatCurrency(walletBalance) }}</span>
                                    </p>
                                    <p class="text-sm text-green-700 mt-1">
                                        New balance after upgrade: <span class="font-semibold">{{ formatCurrency(walletBalance - upgradeCost) }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div v-else class="mb-6 bg-amber-50 border-2 border-amber-300 rounded-lg p-6">
                            <div class="flex items-start">
                                <AlertCircleIcon class="w-6 h-6 text-amber-600 mt-0.5 mr-3 flex-shrink-0" />
                                <div class="flex-1">
                                    <h4 class="font-semibold text-amber-900 text-lg">Insufficient Wallet Balance</h4>
                                    <p class="text-sm text-amber-700 mt-2">
                                        Your current balance: <span class="font-semibold">{{ formatCurrency(walletBalance) }}</span>
                                    </p>
                                    <p class="text-sm text-amber-700 mt-1">
                                        You need: <span class="font-semibold text-amber-900">{{ formatCurrency(upgradeCost - walletBalance) }} more</span>
                                    </p>
                                    <div class="mt-4">
                                        <Link
                                            :href="route('mygrownet.payments.create', { type: 'wallet_topup', amount: upgradeCost - walletBalance })"
                                            class="inline-flex items-center px-6 py-3 bg-amber-600 text-white font-semibold rounded-lg hover:bg-amber-700 transition-colors"
                                        >
                                            <BanknoteIcon class="w-5 h-5 mr-2" />
                                            Top Up Wallet
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Premium Benefits -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">What You'll Get with Premium:</h3>
                            <div class="space-y-3">
                                <div v-for="(benefit, index) in premiumBenefits" :key="index" class="flex items-start">
                                    <CheckCircleIcon class="w-5 h-5 text-purple-600 mt-0.5 mr-3 flex-shrink-0" />
                                    <span class="text-gray-700">{{ benefit }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Form -->
                        <form v-if="walletBalance >= upgradeCost" @submit.prevent="submit" class="space-y-6">
                            <!-- Terms -->
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <label class="flex items-start cursor-pointer">
                                    <input
                                        v-model="form.terms_accepted"
                                        type="checkbox"
                                        class="mt-1 h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
                                        required
                                    />
                                    <span class="ml-2 text-sm text-gray-700">
                                        I understand that {{ formatCurrency(upgradeCost) }} will be deducted from my wallet balance and I will be upgraded to Premium tier with immediate LGR access.
                                    </span>
                                </label>
                                <p v-if="form.errors.terms_accepted" class="mt-2 text-sm text-red-600">{{ form.errors.terms_accepted }}</p>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="flex items-center justify-between pt-4">
                                <Link
                                    :href="route('mygrownet.starter-kit.show')"
                                    class="text-gray-600 hover:text-gray-800"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-8 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition"
                                >
                                    <span v-if="form.processing">Processing...</span>
                                    <span v-else>Upgrade to Premium - {{ formatCurrency(upgradeCost) }}</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-sm text-blue-800 flex items-start gap-2">
                        <span class="text-lg">ℹ️</span>
                        <span>Your upgrade is instant! Once processed, you'll immediately have access to LGR quarterly profit sharing and all premium benefits.</span>
                    </p>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
