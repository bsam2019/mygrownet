<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import QuickInvoiceLayout from '@/layouts/QuickInvoiceLayout.vue';
import { CreditCardIcon, WalletIcon, CheckCircleIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    checkout: {
        tier_id: number;
        tier_name: string;
        amount: number;
        currency: string;
        wallet_balance: number;
        can_pay_with_wallet: boolean;
    };
}>();

const processing = ref(false);
const paymentMethod = ref('wallet');
const error = ref('');

const proceed = async () => {
    processing.value = true;
    error.value = '';

    try {
        await router.post(route('quick-invoice.subscription.upgrade'), {
            tier_id: props.checkout.tier_id,
            payment_method: paymentMethod.value,
        });
    } catch (e: any) {
        error.value = e?.response?.data?.message || 'Payment failed. Please try again.';
    } finally {
        processing.value = false;
    }
};

const formatMoney = (amount: number) => {
    return 'K' + amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};
</script>

<template>
    <QuickInvoiceLayout>
        <Head title="Checkout - Quick Invoice" />

        <div class="max-w-lg mx-auto py-8 px-4">
            <Link :href="route('quick-invoice.subscription.plans')"
                class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-6"
            >
                <ArrowLeftIcon class="h-4 w-4" />
                Back to Plans
            </Link>

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <h1 class="text-xl font-bold text-gray-900 mb-6">Confirm Upgrade</h1>

                <!-- Order Summary -->
                <div class="bg-gray-50 rounded-xl p-4 mb-6">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-sm text-gray-600">Plan</span>
                        <span class="font-semibold text-gray-900">{{ checkout.tier_name }}</span>
                    </div>
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-sm text-gray-600">Billing</span>
                        <span class="font-semibold text-gray-900">Monthly</span>
                    </div>
                    <div class="border-t border-gray-200 pt-3 flex justify-between items-center">
                        <span class="font-semibold text-gray-900">Total</span>
                        <span class="text-xl font-bold text-blue-600">{{ formatMoney(checkout.amount) }}</span>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">Payment Method</label>
                    <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer transition"
                        :class="paymentMethod === 'wallet' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'"
                    >
                        <input type="radio" v-model="paymentMethod" value="wallet" class="text-blue-600" />
                        <WalletIcon class="h-5 w-5 text-blue-600" />
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">Wallet Balance</p>
                            <p class="text-xs text-gray-500">Available: {{ formatMoney(checkout.wallet_balance) }}</p>
                        </div>
                        <span v-if="checkout.can_pay_with_wallet" class="text-xs text-green-600 font-medium">Sufficient</span>
                        <span v-else class="text-xs text-red-500 font-medium">Insufficient</span>
                    </label>
                </div>

                <!-- Error -->
                <div v-if="error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                    {{ error }}
                </div>

                <!-- Actions -->
                <button @click="proceed"
                    :disabled="processing || !checkout.can_pay_with_wallet"
                    class="w-full py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                >
                    {{ processing ? 'Processing...' : `Pay ${formatMoney(checkout.amount)}` }}
                </button>

                <p class="mt-3 text-xs text-gray-500 text-center">
                    By upgrading, you agree to our terms of service. You can cancel anytime.
                </p>
            </div>
        </div>
    </QuickInvoiceLayout>
</template>
