<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { 
    WalletIcon, 
    ArrowLeftIcon,
    DevicePhoneMobileIcon,
    ShieldCheckIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    ExclamationCircleIcon
} from '@heroicons/vue/24/outline';
import { ref, computed } from 'vue';

interface VerificationLimits {
    daily_withdrawal: number;
    monthly_withdrawal: number;
    single_transaction: number;
}

interface WithdrawalMethod {
    id: string;
    name: string;
    type: string;
}

interface Props {
    balance?: number;
    remainingDailyLimit?: number;
    verificationLevel?: string;
    verificationLimits?: VerificationLimits;
    withdrawalMethods?: WithdrawalMethod[];
}

const props = withDefaults(defineProps<Props>(), {
    balance: 0,
    remainingDailyLimit: 1000,
    verificationLevel: 'basic',
    verificationLimits: () => ({ daily_withdrawal: 1000, monthly_withdrawal: 10000, single_transaction: 500 }),
    withdrawalMethods: () => [
        { id: 'mtn', name: 'MTN Mobile Money', type: 'mobile_money' },
        { id: 'airtel', name: 'Airtel Money', type: 'mobile_money' },
        { id: 'zamtel', name: 'Zamtel Kwacha', type: 'mobile_money' },
    ],
});

const selectedMethod = ref('mtn');
const step = ref(1);

const form = useForm({
    amount: '',
    payment_method: 'mtn',
    phone_number: '',
    account_name: '',
});

const maxWithdrawable = computed(() => {
    return Math.min(
        props.balance,
        props.remainingDailyLimit,
        props.verificationLimits.single_transaction
    );
});

const isValidPhone = computed(() => {
    const phone = form.phone_number;
    return /^(09[567]\d{7}|07[567]\d{7})$/.test(phone);
});

const isValidAmount = computed(() => {
    const amount = parseFloat(form.amount);
    return !isNaN(amount) && amount >= 10 && amount <= maxWithdrawable.value;
});

const amountError = computed(() => {
    const amount = parseFloat(form.amount);
    if (isNaN(amount) || amount < 10) return 'Minimum withdrawal is K10';
    if (amount > props.balance) return 'Insufficient balance';
    if (amount > props.remainingDailyLimit) return `Exceeds daily limit (K${props.remainingDailyLimit} remaining)`;
    if (amount > props.verificationLimits.single_transaction) return `Exceeds per-transaction limit (K${props.verificationLimits.single_transaction})`;
    return null;
});

const withdrawAll = () => {
    form.amount = maxWithdrawable.value.toString();
};

const selectMethod = (methodId: string) => {
    selectedMethod.value = methodId;
    form.payment_method = methodId;
};

const proceedToConfirm = () => {
    if (isValidAmount.value) {
        step.value = 2;
    }
};

const submitWithdrawal = () => {
    form.payment_method = selectedMethod.value;
    form.post(route('wallet.withdraw.process'), {
        preserveScroll: true,
        onSuccess: () => {
            step.value = 3;
        },
    });
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(amount);
};

const getVerificationLabel = (level: string) => {
    switch(level) {
        case 'premium': return 'Premium';
        case 'enhanced': return 'Enhanced';
        default: return 'Basic';
    }
};

const getVerificationBadgeColor = (level: string) => {
    switch(level) {
        case 'premium': return 'bg-purple-100 text-purple-800';
        case 'enhanced': return 'bg-blue-100 text-blue-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};
</script>

<template>
    <ClientLayout title="Withdraw Funds">
        <Head title="Withdraw Funds" />

        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link 
                        :href="route('wallet.index')"
                        class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-4"
                    >
                        <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                        Back to Wallet
                    </Link>
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <WalletIcon class="h-8 w-8 text-red-600" aria-hidden="true" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Withdraw Funds</h1>
                            <p class="text-sm text-gray-600">Available: {{ formatCurrency(balance) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Flash Messages -->
                <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <CheckCircleIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                        <p class="text-sm text-green-800">{{ $page.props.flash.success }}</p>
                    </div>
                </div>

                <div v-if="$page.props.flash?.error" class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <ExclamationCircleIcon class="h-5 w-5 text-red-600" aria-hidden="true" />
                        <p class="text-sm text-red-800">{{ $page.props.flash.error }}</p>
                    </div>
                </div>

                <!-- Progress Steps -->
                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium', step >= 1 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600']">1</div>
                            <span class="text-sm text-gray-600">Amount</span>
                        </div>
                        <div class="flex-1 h-1 mx-4 bg-gray-200">
                            <div :class="['h-full bg-blue-600 transition-all', step >= 2 ? 'w-full' : 'w-0']"></div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium', step >= 2 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600']">2</div>
                            <span class="text-sm text-gray-600">Confirm</span>
                        </div>
                        <div class="flex-1 h-1 mx-4 bg-gray-200">
                            <div :class="['h-full bg-blue-600 transition-all', step >= 3 ? 'w-full' : 'w-0']"></div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div :class="['w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium', step >= 3 ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-600']">✓</div>
                            <span class="text-sm text-gray-600">Done</span>
                        </div>
                    </div>
                </div>

                <!-- Limits Warning -->
                <div v-if="step < 3" class="mb-6 bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <ShieldCheckIcon class="h-5 w-5 text-amber-600 mt-0.5" aria-hidden="true" />
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="text-sm font-semibold text-amber-900">Withdrawal Limits</h3>
                                <span :class="['text-xs px-2 py-0.5 rounded-full', getVerificationBadgeColor(verificationLevel)]">
                                    {{ getVerificationLabel(verificationLevel) }}
                                </span>
                            </div>
                            <div class="grid grid-cols-3 gap-4 text-sm">
                                <div>
                                    <p class="text-amber-700">Daily</p>
                                    <p class="font-medium text-amber-900">{{ formatCurrency(verificationLimits.daily_withdrawal) }}</p>
                                    <p class="text-xs text-amber-600">{{ formatCurrency(remainingDailyLimit) }} left</p>
                                </div>
                                <div>
                                    <p class="text-amber-700">Per Transaction</p>
                                    <p class="font-medium text-amber-900">{{ formatCurrency(verificationLimits.single_transaction) }}</p>
                                </div>
                                <div>
                                    <p class="text-amber-700">Monthly</p>
                                    <p class="font-medium text-amber-900">{{ formatCurrency(verificationLimits.monthly_withdrawal) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 1: Amount & Method -->
                <div v-if="step === 1" class="space-y-6">
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Withdrawal Amount</h2>
                        
                        <div class="mb-4">
                            <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                Amount (ZMW)
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">K</span>
                                <input
                                    id="amount"
                                    v-model="form.amount"
                                    type="number"
                                    min="10"
                                    :max="maxWithdrawable"
                                    step="1"
                                    class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                                    placeholder="0.00"
                                />
                            </div>
                            <p v-if="amountError && form.amount" class="text-xs text-red-600 mt-1">
                                {{ amountError }}
                            </p>
                            <p v-else class="text-xs text-gray-500 mt-1">
                                Maximum withdrawable: {{ formatCurrency(maxWithdrawable) }}
                            </p>
                        </div>

                        <div class="flex gap-2 flex-wrap">
                            <button
                                type="button"
                                @click="form.amount = Math.min(100, maxWithdrawable).toString()"
                                class="py-2 px-4 rounded-lg text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200"
                            >
                                K100
                            </button>
                            <button
                                type="button"
                                @click="form.amount = Math.min(500, maxWithdrawable).toString()"
                                class="py-2 px-4 rounded-lg text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200"
                            >
                                K500
                            </button>
                            <button
                                type="button"
                                @click="withdrawAll"
                                class="py-2 px-4 rounded-lg text-sm font-medium bg-blue-100 text-blue-700 hover:bg-blue-200"
                            >
                                Max ({{ formatCurrency(maxWithdrawable) }})
                            </button>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Withdraw To</h2>
                        
                        <div class="space-y-3">
                            <button
                                v-for="method in withdrawalMethods"
                                :key="method.id"
                                type="button"
                                @click="selectMethod(method.id)"
                                :class="[
                                    'w-full flex items-center gap-4 p-4 rounded-lg border-2 transition-colors',
                                    selectedMethod === method.id
                                        ? 'border-blue-500 bg-blue-50'
                                        : 'border-gray-200 hover:border-gray-300'
                                ]"
                            >
                                <DevicePhoneMobileIcon class="h-6 w-6 text-gray-600" aria-hidden="true" />
                                <span class="font-medium text-gray-900">{{ method.name }}</span>
                                <CheckCircleIcon 
                                    v-if="selectedMethod === method.id" 
                                    class="h-5 w-5 text-blue-600 ml-auto" 
                                    aria-hidden="true" 
                                />
                            </button>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="proceedToConfirm"
                        :disabled="!isValidAmount"
                        :class="[
                            'w-full py-3 px-4 rounded-lg font-semibold text-white transition-colors',
                            isValidAmount
                                ? 'bg-blue-600 hover:bg-blue-700'
                                : 'bg-gray-300 cursor-not-allowed'
                        ]"
                    >
                        Continue
                    </button>
                </div>

                <!-- Step 2: Account Details & Confirm -->
                <div v-if="step === 2" class="space-y-6">
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Confirm Withdrawal</h2>
                        
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <div class="flex justify-between mb-2">
                                <span class="text-gray-600">Amount</span>
                                <span class="font-semibold text-gray-900">{{ formatCurrency(parseFloat(form.amount)) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Withdraw To</span>
                                <span class="font-medium text-gray-900">{{ withdrawalMethods.find(m => m.id === selectedMethod)?.name }}</span>
                            </div>
                        </div>

                        <div class="space-y-4 mb-4">
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Mobile Money Number
                                </label>
                                <div class="relative">
                                    <DevicePhoneMobileIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                                    <input
                                        id="phone"
                                        v-model="form.phone_number"
                                        type="tel"
                                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="097XXXXXXX"
                                    />
                                </div>
                                <p v-if="form.phone_number && !isValidPhone" class="mt-1 text-sm text-amber-600">
                                    Enter a valid Zambian mobile number
                                </p>
                            </div>
                            <div>
                                <label for="account_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Account Holder Name
                                </label>
                                <input
                                    id="account_name"
                                    v-model="form.account_name"
                                    type="text"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Full name as registered"
                                />
                            </div>
                        </div>

                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
                            <div class="flex items-start gap-2">
                                <ExclamationTriangleIcon class="h-4 w-4 text-amber-600 mt-0.5" aria-hidden="true" />
                                <p class="text-xs text-amber-700">
                                    Please ensure the phone number and name match your registered mobile money account. Withdrawals are processed within 24 hours.
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button
                                type="button"
                                @click="step = 1"
                                class="flex-1 py-3 px-4 rounded-lg font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors"
                            >
                                Back
                            </button>
                            <button
                                type="button"
                                @click="submitWithdrawal"
                                :disabled="form.processing || !isValidPhone || !form.account_name"
                                class="flex-1 py-3 px-4 rounded-lg font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-colors disabled:bg-gray-300 disabled:cursor-not-allowed"
                            >
                                {{ form.processing ? 'Processing...' : 'Submit Withdrawal' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Success -->
                <div v-if="step === 3" class="text-center py-12">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <CheckCircleIcon class="h-8 w-8 text-green-600" aria-hidden="true" />
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 mb-2">Withdrawal Submitted!</h2>
                    <p class="text-gray-600 mb-6">Your withdrawal request is being processed. You'll receive the funds within 24 hours.</p>
                    <Link
                        :href="route('wallet.index')"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors"
                    >
                        Return to Wallet
                    </Link>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>
