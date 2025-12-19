<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import {
    XMarkIcon,
    CheckIcon,
    WalletIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

interface Props {
    show: boolean;
    balance: number;
    quickAmounts?: number[];
    returnUrl?: string;
}

const props = withDefaults(defineProps<Props>(), {
    quickAmounts: () => [25, 50, 100, 200],
    returnUrl: '',
});

const emit = defineEmits<{
    close: [];
    success: [amount: number];
}>();

const selectedAmount = ref<number | null>(null);
const customAmount = ref('');
const selectedMethod = ref<'mtn' | 'airtel'>('mtn');
const phoneNumber = ref('');
const processing = ref(false);
const error = ref('');

const topUpAmount = computed(() => {
    if (selectedAmount.value) return selectedAmount.value;
    const custom = parseFloat(customAmount.value);
    return isNaN(custom) ? 0 : custom;
});

const isValidAmount = computed(() => topUpAmount.value >= 5);
const isValidPhone = computed(() => /^(09[567]\d{7}|07[567]\d{7})$/.test(phoneNumber.value));
const canSubmit = computed(() => isValidAmount.value && isValidPhone.value && !processing.value);

const selectQuickAmount = (amount: number) => {
    selectedAmount.value = amount;
    customAmount.value = '';
};

const onCustomAmountChange = () => {
    selectedAmount.value = null;
};

const formatCurrency = (amount: number) => `K${amount.toLocaleString()}`;

const submitTopUp = async () => {
    if (!canSubmit.value) return;

    processing.value = true;
    error.value = '';

    try {
        router.post(
            '/wallet/topup',
            {
                amount: topUpAmount.value,
                payment_method: selectedMethod.value,
                phone_number: phoneNumber.value,
                return_url: props.returnUrl || window.location.href,
            },
            {
                onSuccess: () => {
                    emit('success', topUpAmount.value);
                    emit('close');
                },
                onError: (errors) => {
                    error.value = Object.values(errors).flat().join(', ') || 'Payment failed. Please try again.';
                },
                onFinish: () => {
                    processing.value = false;
                },
            }
        );
    } catch (e) {
        error.value = 'An error occurred. Please try again.';
        processing.value = false;
    }
};

const close = () => {
    if (!processing.value) {
        emit('close');
    }
};
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-[100] bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
                @click="close"
            >
                <div
                    class="bg-white rounded-3xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto"
                    @click.stop
                >
                    <!-- Header -->
                    <div class="sticky top-0 bg-white rounded-t-3xl px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                                <WalletIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">Top Up Wallet</h3>
                        </div>
                        <button
                            @click="close"
                            :disabled="processing"
                            class="p-2 hover:bg-gray-100 rounded-full transition-colors disabled:opacity-50"
                            aria-label="Close modal"
                        >
                            <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                        </button>
                    </div>

                    <div class="p-6 space-y-5">
                        <!-- Current Balance -->
                        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-4 text-white">
                            <p class="text-sm text-emerald-100 mb-1">Current Balance</p>
                            <p class="text-3xl font-bold">{{ formatCurrency(balance) }}</p>
                        </div>

                        <!-- Error Message -->
                        <div v-if="error" class="bg-red-50 border border-red-200 rounded-xl p-3 flex items-start gap-2">
                            <ExclamationTriangleIcon class="h-5 w-5 text-red-500 flex-shrink-0" aria-hidden="true" />
                            <p class="text-sm text-red-700">{{ error }}</p>
                        </div>

                        <!-- Quick Amount Selection -->
                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-2">Select Amount</p>
                            <div class="grid grid-cols-4 gap-2">
                                <button
                                    v-for="amount in quickAmounts"
                                    :key="amount"
                                    @click="selectQuickAmount(amount)"
                                    :class="[
                                        'py-3 px-2 rounded-xl font-semibold transition-all text-sm',
                                        selectedAmount === amount
                                            ? 'bg-emerald-500 text-white shadow-lg scale-105'
                                            : 'bg-gray-100 hover:bg-emerald-100 text-gray-900'
                                    ]"
                                >
                                    K{{ amount }}
                                </button>
                            </div>
                        </div>

                        <!-- Custom Amount -->
                        <div>
                            <label class="text-sm font-semibold text-gray-700 mb-2 block">Or Enter Custom Amount</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">K</span>
                                <input
                                    v-model="customAmount"
                                    @input="onCustomAmountChange"
                                    type="number"
                                    min="5"
                                    placeholder="0.00"
                                    class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:border-emerald-500 focus:ring-0 text-lg font-semibold"
                                />
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Minimum top-up: K5</p>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-2">Payment Method</p>
                            <div class="space-y-2">
                                <button
                                    @click="selectedMethod = 'mtn'"
                                    :class="[
                                        'w-full flex items-center gap-3 p-3 border-2 rounded-xl transition-all',
                                        selectedMethod === 'mtn'
                                            ? 'border-emerald-500 bg-emerald-50'
                                            : 'border-gray-200 hover:border-gray-300'
                                    ]"
                                >
                                    <div class="w-10 h-10 rounded-full bg-yellow-400 flex items-center justify-center flex-shrink-0">
                                        <span class="text-xs font-bold text-yellow-900">MTN</span>
                                    </div>
                                    <span class="font-semibold text-gray-900">MTN Mobile Money</span>
                                    <CheckIcon
                                        v-if="selectedMethod === 'mtn'"
                                        class="h-5 w-5 text-emerald-600 ml-auto"
                                        aria-hidden="true"
                                    />
                                </button>
                                <button
                                    @click="selectedMethod = 'airtel'"
                                    :class="[
                                        'w-full flex items-center gap-3 p-3 border-2 rounded-xl transition-all',
                                        selectedMethod === 'airtel'
                                            ? 'border-emerald-500 bg-emerald-50'
                                            : 'border-gray-200 hover:border-gray-300'
                                    ]"
                                >
                                    <div class="w-10 h-10 rounded-full bg-red-500 flex items-center justify-center flex-shrink-0">
                                        <span class="text-xs font-bold text-white">AIR</span>
                                    </div>
                                    <span class="font-semibold text-gray-900">Airtel Money</span>
                                    <CheckIcon
                                        v-if="selectedMethod === 'airtel'"
                                        class="h-5 w-5 text-emerald-600 ml-auto"
                                        aria-hidden="true"
                                    />
                                </button>
                            </div>
                        </div>

                        <!-- Phone Number -->
                        <div>
                            <label class="text-sm font-semibold text-gray-700 mb-2 block">Phone Number</label>
                            <input
                                v-model="phoneNumber"
                                type="tel"
                                :placeholder="selectedMethod === 'mtn' ? '097XXXXXXX' : '097XXXXXXX'"
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-emerald-500 focus:ring-0 text-lg"
                            />
                            <p v-if="phoneNumber && !isValidPhone" class="text-xs text-red-500 mt-1">
                                Enter a valid Zambian mobile number
                            </p>
                        </div>

                        <!-- Summary -->
                        <div v-if="topUpAmount > 0" class="bg-gray-50 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-600">Top Up Amount</span>
                                <span class="font-bold text-gray-900">{{ formatCurrency(topUpAmount) }}</span>
                            </div>
                            <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                                <span class="text-sm text-gray-600">New Balance</span>
                                <span class="font-bold text-emerald-600">{{ formatCurrency(balance + topUpAmount) }}</span>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="bg-blue-50 rounded-xl p-3">
                            <p class="text-xs text-blue-700">
                                You will receive a payment prompt on your phone. Enter your PIN to confirm the transaction.
                                Funds are added instantly after confirmation.
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="sticky bottom-0 bg-white rounded-b-3xl px-6 py-4 border-t border-gray-100">
                        <div class="flex gap-3">
                            <button
                                @click="close"
                                :disabled="processing"
                                class="flex-1 py-3 px-4 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-colors disabled:opacity-50"
                            >
                                Cancel
                            </button>
                            <button
                                @click="submitTopUp"
                                :disabled="!canSubmit"
                                class="flex-1 py-3 px-4 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl font-bold hover:from-emerald-600 hover:to-teal-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {{ processing ? 'Processing...' : `Pay ${formatCurrency(topUpAmount)}` }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
