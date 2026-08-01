<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import {
    ShieldCheckIcon,
    PhoneIcon,
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon,
    ArrowPathIcon,
} from '@heroicons/vue/24/outline';

interface GatewayOption {
    value: string;
    label: string;
    description: string;
}

const props = defineProps<{
    amount: number;
    currency: string;
    description?: string;
    gateway?: string;
    reference?: string;
    returnUrl?: string;
    organizationId?: number | null;
}>();

const phoneNumber = ref('');
const selectedGateway = ref(props.gateway ?? 'pawapay');
const gateways = ref<GatewayOption[]>([]);
const processing = ref(false);
const error = ref('');
const step = ref<'form' | 'pending' | 'completed' | 'failed'>('form');
const activeReference = ref<string | null>(null);
const pollTimer = ref<number | null>(null);

const isValidPhone = computed(() => /^(09[567]\d{7}|07[567]\d{7}|\+?2609[567]\d{7})$/.test(phoneNumber.value.trim()));
const canSubmit = computed(() => props.amount > 0 && isValidPhone.value && !processing.value);

const moneySymbol = computed(() => {
    const symbols: Record<string, string> = {
        ZMW: 'K', USD: '$', EUR: '€', GBP: '£', ZAR: 'R', KES: 'KSh', NGN: '₦', GHS: '₵',
    };
    return symbols[props.currency.toUpperCase()] ?? props.currency + ' ';
});

const formattedAmount = computed(() => `${moneySymbol.value}${props.amount.toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
})}`);

const fetchGateways = async () => {
    try {
        const response = await axios.get('/api/payments/shared/gateways');
        gateways.value = response.data.gateways ?? [];
        if (!gateways.value.find((g) => g.value === selectedGateway.value) && gateways.value.length) {
            selectedGateway.value = gateways.value[0].value;
        }
    } catch (e) {
        gateways.value = [{ value: 'pawapay', label: 'PawaPay', description: 'Mobile money' }];
    }
};

const initiate = async () => {
    if (!canSubmit.value) return;

    processing.value = true;
    error.value = '';
    step.value = 'pending';

    try {
        const response = await axios.post('/api/payments/shared/initiate', {
            phone_number: phoneNumber.value.trim(),
            amount: props.amount,
            currency: props.currency.toUpperCase(),
            gateway: selectedGateway.value,
            description: props.description,
            reference: props.reference,
            metadata: {
                return_url: props.returnUrl,
                organization_id: props.organizationId,
            },
        });

        if (!response.data.success) {
            throw new Error('Payment could not be initiated');
        }

        activeReference.value = response.data.transaction?.reference
            ?? response.data.transaction?.id
            ?? props.reference;

        if (response.data.transaction?.status === 'completed') {
            onCompleted();
        } else {
            startPolling();
        }
    } catch (e: any) {
        step.value = 'failed';
        const errors = e.response?.data?.errors;
        const msg = errors
            ? Object.values(errors).flat().join(', ')
            : e.response?.data?.message;
        error.value = msg || 'Payment failed to initiate. Please try again.';
    } finally {
        processing.value = false;
    }
};

const startPolling = () => {
    stopPolling();
    pollTimer.value = window.setInterval(async () => {
        if (!activeReference.value) return;
        try {
            const response = await axios.get(`/api/payments/shared/status/${activeReference.value}`);
            const status = response.data.transaction?.status;
            if (status === 'completed') {
                onCompleted();
            } else if (['failed', 'cancelled', 'expired'].includes(status)) {
                onFailed(response.data.transaction?.message);
            }
        } catch (e) {
            // keep polling; transient network errors ignored
        }
    }, 4000);
};

const stopPolling = () => {
    if (pollTimer.value) {
        window.clearInterval(pollTimer.value);
        pollTimer.value = null;
    }
};

const onCompleted = () => {
    stopPolling();
    step.value = 'completed';
    window.setTimeout(() => {
        if (props.returnUrl) {
            window.location.href = props.returnUrl;
        }
    }, 1500);
};

const onFailed = (message?: string) => {
    stopPolling();
    step.value = 'failed';
    error.value = message || 'Payment failed. Please try again.';
};

const reset = () => {
    stopPolling();
    step.value = 'form';
    error.value = '';
    activeReference.value = null;
};

onMounted(fetchGateways);
onBeforeUnmount(stopPolling);
</script>

<template>
    <Head title="Checkout" />

    <div class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-blue-50 flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-md">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-600 shadow-lg mb-4">
                    <ShieldCheckIcon class="h-7 w-7 text-white" aria-hidden="true" />
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Secure Checkout</h1>
                <p class="mt-1 text-sm text-gray-500">Pay using mobile money — no card required</p>
            </div>

            <div class="bg-white rounded-3xl border border-gray-200 shadow-xl p-6">
                <!-- Completed -->
                <div v-if="step === 'completed'" class="text-center py-8">
                    <CheckCircleIcon class="h-16 w-16 text-emerald-500 mx-auto mb-4" aria-hidden="true" />
                    <h2 class="text-xl font-bold text-gray-900">Payment Successful</h2>
                    <p class="mt-2 text-sm text-gray-500">Your payment of {{ formattedAmount }} was received.</p>
                </div>

                <!-- Failed -->
                <div v-else-if="step === 'failed'" class="text-center py-8">
                    <XCircleIcon class="h-16 w-16 text-red-500 mx-auto mb-4" aria-hidden="true" />
                    <h2 class="text-xl font-bold text-gray-900">Payment Failed</h2>
                    <p v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>
                    <button @click="reset"
                        class="mt-6 inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700"
                    >
                        <ArrowPathIcon class="h-4 w-4" aria-hidden="true" /> Try Again
                    </button>
                </div>

                <!-- Pending (awaiting confirmation) -->
                <div v-else-if="step === 'pending'" class="text-center py-8">
                    <div class="flex justify-center mb-4">
                        <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center">
                            <ArrowPathIcon class="h-8 w-8 text-emerald-600 animate-spin" aria-hidden="true" />
                        </div>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Waiting for Confirmation</h2>
                    <p class="mt-2 text-sm text-gray-500 flex items-center justify-center gap-1">
                        <ClockIcon class="h-4 w-4" aria-hidden="true" />
                        Approve the prompt on your phone to complete payment
                    </p>
                    <p class="mt-4 text-xs text-gray-400">Reference: {{ activeReference }}</p>
                </div>

                <!-- Form -->
                <form v-else @submit.prevent="initiate">
                    <!-- Order Summary -->
                    <div class="bg-gray-50 rounded-xl p-4 mb-6">
                        <div v-if="description" class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Item</span>
                            <span class="font-semibold text-gray-900">{{ description }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Total</span>
                            <span class="text-2xl font-bold text-emerald-600">{{ formattedAmount }}</span>
                        </div>
                    </div>

                    <!-- Phone Number -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mobile Money Number</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 pl-3 flex items-center">
                                <PhoneIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                            </div>
                            <input
                                v-model="phoneNumber"
                                type="tel"
                                inputmode="tel"
                                placeholder="097 000 0000"
                                :disabled="processing"
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm disabled:opacity-60"
                            />
                        </div>
                        <p v-if="phoneNumber && !isValidPhone" class="mt-1 text-xs text-red-500">
                            Enter a valid Zambian mobile money number (097, 096, 095 or 076).
                        </p>
                    </div>

                    <!-- Gateway -->
                    <div v-if="gateways.length > 1" class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                        <div class="space-y-2">
                            <label
                                v-for="g in gateways"
                                :key="g.value"
                                class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition"
                                :class="selectedGateway === g.value ? 'border-emerald-500 bg-emerald-50' : 'border-gray-200 hover:border-gray-300'"
                            >
                                <input v-model="selectedGateway" type="radio" :value="g.value" class="text-emerald-600" />
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ g.label }}</p>
                                    <p class="text-xs text-gray-500">{{ g.description }}</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Error -->
                    <div v-if="error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                        {{ error }}
                    </div>

                    <button
                        type="submit"
                        :disabled="!canSubmit"
                        class="w-full py-3.5 bg-emerald-600 text-white rounded-xl font-semibold hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    >
                        {{ processing ? 'Processing...' : `Pay ${formattedAmount}` }}
                    </button>

                    <p class="mt-4 text-xs text-gray-400 text-center leading-relaxed">
                        Payments are processed securely by our payment partners. You will be
                        prompted to approve on your phone.
                    </p>
                </form>
            </div>

            <p class="mt-6 text-center text-xs text-gray-400">
                Powered by MyGrowNet Payments
            </p>
        </div>
    </div>
</template>
