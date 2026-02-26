<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { ArrowLeftIcon, WalletIcon, CreditCardIcon, CheckCircleIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

interface Venture {
    id: number;
    title: string;
    slug: string;
    funding_target: number;
    total_raised: number;
    minimum_investment: number;
    maximum_investment: number | null;
    investor_count: number;
    category: {
        name: string;
    };
}

const props = defineProps<{
    venture: Venture;
    walletBalance: number;
    fundingProgress: number;
}>();

const form = useForm({
    amount: props.venture.minimum_investment,
    payment_method: 'wallet',
});

const remainingFunding = computed(() => {
    return props.venture.funding_target - props.venture.total_raised;
});

const canAffordWallet = computed(() => {
    return props.walletBalance >= form.amount;
});

const isValidAmount = computed(() => {
    if (form.amount < props.venture.minimum_investment) return false;
    if (props.venture.maximum_investment && form.amount > props.venture.maximum_investment) return false;
    if (form.amount > remainingFunding.value) return false;
    return true;
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const submit = () => {
    form.post(route('mygrownet.ventures.invest.submit', props.venture.id));
};
</script>

<template>
    <Head :title="`Invest in ${venture.title}`" />

    <MemberLayout>
        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <!-- Back Button -->
                <Link
                    :href="route('ventures.show', venture.slug)"
                    class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-6"
                >
                    <ArrowLeftIcon class="h-4 w-4" />
                    Back to Venture
                </Link>

                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Make an Investment</h1>
                    <p class="mt-2 text-lg text-gray-600">{{ venture.title }}</p>
                    <span class="mt-2 inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-800">
                        {{ venture.category.name }}
                    </span>
                </div>

                <!-- Funding Status -->
                <div class="mb-8 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Funding Status</h2>
                    <div class="mb-2 flex justify-between text-sm">
                        <span class="text-gray-600">Progress</span>
                        <span class="font-semibold text-gray-900">{{ Math.round(fundingProgress) }}%</span>
                    </div>
                    <div class="h-3 w-full rounded-full bg-gray-200">
                        <div
                            class="h-3 rounded-full bg-green-600 transition-all"
                            :style="{ width: `${fundingProgress}%` }"
                        ></div>
                    </div>
                    <div class="mt-2 flex justify-between text-sm text-gray-500">
                        <span>{{ formatCurrency(venture.total_raised) }} raised</span>
                        <span>{{ formatCurrency(remainingFunding) }} remaining</span>
                    </div>
                </div>

                <!-- Investment Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Amount -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <h2 class="mb-6 text-lg font-semibold text-gray-900">Investment Amount</h2>
                        
                        <div>
                            <label for="amount" class="block text-sm font-semibold text-gray-900 mb-2">
                                Amount (ZMW) <span class="text-red-600">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-lg">K</span>
                                <input
                                    id="amount"
                                    v-model.number="form.amount"
                                    type="number"
                                    step="100"
                                    :min="venture.minimum_investment"
                                    :max="venture.maximum_investment || remainingFunding"
                                    class="block w-full rounded-lg border-gray-300 bg-white pl-10 pr-4 py-4 text-lg font-semibold text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                    required
                                />
                            </div>
                            <div class="mt-3 space-y-1 text-sm text-gray-600">
                                <p>• Minimum: {{ formatCurrency(venture.minimum_investment) }}</p>
                                <p v-if="venture.maximum_investment">• Maximum: {{ formatCurrency(venture.maximum_investment) }}</p>
                                <p>• Available: {{ formatCurrency(remainingFunding) }}</p>
                            </div>
                            <p v-if="form.errors.amount" class="mt-2 text-sm text-red-600">
                                {{ form.errors.amount }}
                            </p>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                        <h2 class="mb-6 text-lg font-semibold text-gray-900">Payment Method</h2>
                        
                        <div class="space-y-4">
                            <!-- Wallet Option -->
                            <label
                                :class="[
                                    'relative flex cursor-pointer rounded-lg border-2 p-4 transition-all',
                                    form.payment_method === 'wallet'
                                        ? 'border-blue-600 bg-blue-50'
                                        : 'border-gray-200 hover:border-gray-300'
                                ]"
                            >
                                <input
                                    v-model="form.payment_method"
                                    type="radio"
                                    value="wallet"
                                    class="sr-only"
                                />
                                <div class="flex flex-1 items-center">
                                    <WalletIcon class="h-8 w-8 text-blue-600 flex-shrink-0" />
                                    <div class="ml-4 flex-1">
                                        <div class="font-semibold text-gray-900">MyGrow Wallet</div>
                                        <div class="text-sm text-gray-600">
                                            Balance: {{ formatCurrency(walletBalance) }}
                                        </div>
                                        <div v-if="!canAffordWallet && form.payment_method === 'wallet'" class="mt-2 flex items-center gap-2 text-sm text-red-600">
                                            <ExclamationTriangleIcon class="h-4 w-4" />
                                            Insufficient balance
                                        </div>
                                    </div>
                                    <CheckCircleIcon
                                        v-if="form.payment_method === 'wallet'"
                                        class="h-6 w-6 text-blue-600"
                                    />
                                </div>
                            </label>

                            <!-- Mobile Money Option -->
                            <label
                                :class="[
                                    'relative flex cursor-pointer rounded-lg border-2 p-4 transition-all',
                                    form.payment_method === 'mobile_money'
                                        ? 'border-blue-600 bg-blue-50'
                                        : 'border-gray-200 hover:border-gray-300'
                                ]"
                            >
                                <input
                                    v-model="form.payment_method"
                                    type="radio"
                                    value="mobile_money"
                                    class="sr-only"
                                />
                                <div class="flex flex-1 items-center">
                                    <CreditCardIcon class="h-8 w-8 text-green-600 flex-shrink-0" />
                                    <div class="ml-4 flex-1">
                                        <div class="font-semibold text-gray-900">Mobile Money</div>
                                        <div class="text-sm text-gray-600">
                                            MTN MoMo / Airtel Money
                                        </div>
                                    </div>
                                    <CheckCircleIcon
                                        v-if="form.payment_method === 'mobile_money'"
                                        class="h-6 w-6 text-blue-600"
                                    />
                                </div>
                            </label>
                        </div>
                        <p v-if="form.errors.payment_method" class="mt-2 text-sm text-red-600">
                            {{ form.errors.payment_method }}
                        </p>
                    </div>

                    <!-- Summary -->
                    <div class="rounded-lg border-2 border-blue-200 bg-blue-50 p-6">
                        <h3 class="mb-4 text-lg font-semibold text-blue-900">Investment Summary</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-blue-800">Investment Amount:</span>
                                <span class="font-semibold text-blue-900">{{ formatCurrency(form.amount) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-800">Payment Method:</span>
                                <span class="font-semibold text-blue-900">
                                    {{ form.payment_method === 'wallet' ? 'MyGrow Wallet' : 'Mobile Money' }}
                                </span>
                            </div>
                            <div class="flex justify-between border-t border-blue-200 pt-3">
                                <span class="text-blue-800">You will become:</span>
                                <span class="font-semibold text-blue-900">Shareholder</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between border-t border-gray-200 pt-6">
                        <Link
                            :href="route('ventures.show', venture.slug)"
                            class="rounded-lg bg-white px-6 py-3 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing || !isValidAmount || (form.payment_method === 'wallet' && !canAffordWallet)"
                            class="rounded-lg bg-blue-600 px-8 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            {{ form.processing ? 'Processing...' : 'Confirm Investment' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </MemberLayout>
</template>
