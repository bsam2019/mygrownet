<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AppLayout from '@/Layouts/AppLayout.vue';
import CurrencySelector from '@/Components/CurrencySelector.vue';
import axios from 'axios';
import { 
    WalletIcon, 
    ArrowLeftIcon,
    DevicePhoneMobileIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
    BanknotesIcon,
    PhoneIcon,
} from '@heroicons/vue/24/outline';
import { CoinsIcon } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface PaymentMethod {
    id: string;
    name: string;
    type: string;
    provider: string;
}

interface Props {
    balance?: number;
    paymentMethods?: PaymentMethod[];
}

const props = withDefaults(defineProps<Props>(), {
    balance: 0,
    paymentMethods: () => [
        { id: 'mtn', name: 'MTN Mobile Money', type: 'mobile_money', provider: 'mtn' },
        { id: 'airtel', name: 'Airtel Money', type: 'mobile_money', provider: 'airtel' },
        { id: 'crypto', name: 'Cryptocurrency', type: 'crypto', provider: 'nowpayments' },
    ],
});

// Check if automated payments are enabled
const page = usePage();
const automatedPaymentsEnabled = computed(() => (page.props as any).automatedPaymentsEnabled ?? false);

const selectedMethod = ref('mtn');
const quickAmounts = [50, 100, 200, 500, 1000, 2000];

// Currency conversion
const selectedCurrency = ref('ZMW');
const convertedAmount = ref<number | null>(null);
const loading = ref(false);

const form = useForm({
    amount: '',
    payment_method: 'mtn',
    phone_number: '',
});

const displayAmount = computed(() => {
    if (!form.amount) return 'K 0.00';
    
    const amount = parseFloat(form.amount);
    if (isNaN(amount)) return 'K 0.00';
    
    if (selectedCurrency.value === 'ZMW') {
        return `K ${amount.toFixed(2)}`;
    }
    
    if (convertedAmount.value !== null) {
        const symbols: Record<string, string> = {
            'USD': '$', 'EUR': '€', 'GBP': '£', 'ZMW': 'K', 'ZAR': 'R',
            'KES': 'KSh', 'NGN': '₦', 'GHS': '₵', 'CAD': 'C$', 'AUD': 'A$'
        };
        const symbol = symbols[selectedCurrency.value] || selectedCurrency.value;
        return `${symbol} ${convertedAmount.value.toFixed(2)}`;
    }
    
    return `K ${amount.toFixed(2)}`;
});

const handleCurrencyChange = async (currency: string) => {
    selectedCurrency.value = currency;
    
    const amount = parseFloat(form.amount);
    if (currency !== 'ZMW' && form.amount && !isNaN(amount)) {
        loading.value = true;
        try {
            const response = await axios.post('/api/currency/convert', {
                amount: amount,
                from: 'ZMW',
                to: currency
            });
            
            if (response.data.success) {
                convertedAmount.value = response.data.converted_amount;
            }
        } catch (error) {
            console.error('Currency conversion failed:', error);
        } finally {
            loading.value = false;
        }
    } else {
        convertedAmount.value = null;
    }
};

const selectQuickAmount = (amount: number) => {
    form.amount = amount.toString();
    if (selectedCurrency.value !== 'ZMW') {
        handleCurrencyChange(selectedCurrency.value);
    }
};

// Automated payment submission
const submitAutomated = () => {
    form.payment_method = selectedMethod.value;
    form.post(route('wallet.topup.process'), {
        preserveScroll: true,
    });
};

// Crypto payment submission
const submitCrypto = async () => {
    const amount = parseFloat(form.amount);
    if (isNaN(amount) || amount < 50) {
        alert('Please enter a valid amount (minimum K50)');
        return;
    }

    form.processing = true;
    
    try {
        // For dual-currency system:
        // - Zambians use ZMW (currency selector shows ZMW)
        // - Foreigners use USD (currency selector shows USD)
        // Wallet stores in the user's base currency (ZMW or USD)
        const response = await axios.post('/api/payments/crypto/create', {
            order_id: `WALLET-${Date.now()}`,
            amount: convertedAmount.value || amount,
            currency: selectedCurrency.value, // User's base currency (ZMW or USD)
        });
        
        if (response.data.success && response.data.invoice_url) {
            window.location.href = response.data.invoice_url;
        } else {
            alert(response.data.message || 'Failed to create crypto payment');
            form.processing = false;
        }
    } catch (error: any) {
        alert(error.response?.data?.message || 'Failed to initiate cryptocurrency payment');
        form.processing = false;
    }
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(amount);
};

const isValidPhone = computed(() => {
    const phone = form.phone_number;
    return /^(09[567]\d{7}|07[567]\d{7})$/.test(phone);
});

const isValidAmount = computed(() => {
    const amount = parseFloat(form.amount);
    return !isNaN(amount) && amount >= 10 && amount <= 50000;
});

const canSubmit = computed(() => {
    if (selectedMethod.value === 'crypto') {
        return isValidAmount.value && !form.processing && !loading.value;
    }
    return isValidAmount.value && isValidPhone.value && selectedMethod.value;
});

// Redirect to manual payment submission page
const goToManualPayment = () => {
    router.visit(route('mygrownet.payments.create', { type: 'wallet_topup' }));
};
</script>

<template>
    <AppLayout title="Top Up Wallet">
        <Head title="Top Up Wallet" />

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
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <WalletIcon class="h-8 w-8 text-green-600" aria-hidden="true" />
                            </div>
                            <div>
                            <h1 class="text-2xl font-bold text-gray-900">Top Up Wallet</h1>
                            <p class="text-sm text-gray-600">Current balance: {{ formatCurrency(balance) }}</p>
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

                <!-- ========== AUTOMATED PAYMENT MODE ========== -->
                <template v-if="automatedPaymentsEnabled">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <form @submit.prevent="submitAutomated" class="space-y-6">
                            <!-- Amount Input -->
                            <div>
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
                                        max="50000"
                                        step="0.01"
                                        placeholder="0.00"
                                        class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-lg"
                                        required
                                    />
                                </div>
                                <p v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</p>
                                <p class="mt-1 text-xs text-gray-500">Minimum: K10 | Maximum: K50,000</p>
                            </div>

                            <!-- Quick Amount Buttons -->
                            <div>
                                <p class="text-sm text-gray-600 mb-2">Quick select:</p>
                                <div class="grid grid-cols-3 gap-2">
                                    <button
                                        v-for="amount in quickAmounts"
                                        :key="amount"
                                        type="button"
                                        @click="selectQuickAmount(amount)"
                                        :class="[
                                            'py-2 px-4 rounded-lg text-sm font-medium transition-colors',
                                            form.amount === amount.toString()
                                                ? 'bg-green-100 text-green-700 border-2 border-green-500'
                                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 border-2 border-transparent'
                                        ]"
                                    >
                                        K{{ amount }}
                                    </button>
                                </div>
                            </div>

                            <!-- Payment Method Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Payment Method
                                </label>
                                <div class="space-y-2">
                                    <label
                                        v-for="method in paymentMethods"
                                        :key="method.id"
                                        :class="[
                                            'flex items-center gap-3 p-4 border rounded-lg cursor-pointer transition-colors',
                                            selectedMethod === method.id
                                                ? method.id === 'crypto' ? 'border-indigo-500 bg-indigo-50' : 'border-green-500 bg-green-50'
                                                : 'border-gray-200 hover:border-gray-300'
                                        ]"
                                    >
                                        <input
                                            type="radio"
                                            :value="method.id"
                                            v-model="selectedMethod"
                                            class="sr-only"
                                        />
                                        <CoinsIcon v-if="method.id === 'crypto'" class="h-6 w-6 text-indigo-600" />
                                        <DevicePhoneMobileIcon v-else class="h-6 w-6 text-gray-600" aria-hidden="true" />
                                        <div class="flex-1">
                                            <span class="font-medium text-gray-900">{{ method.name }}</span>
                                            <span v-if="method.id === 'crypto'" class="block text-xs text-gray-500">BTC, ETH, USDT +240</span>
                                        </div>
                                        <CheckCircleIcon 
                                            v-if="selectedMethod === method.id"
                                            :class="method.id === 'crypto' ? 'text-indigo-600' : 'text-green-600'"
                                            class="h-5 w-5 ml-auto" 
                                            aria-hidden="true" 
                                        />
                                    </label>
                                </div>
                            </div>

                            <!-- Crypto Payment Info -->
                            <div v-if="selectedMethod === 'crypto'" class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-lg p-4 border border-indigo-200">
                                <div class="space-y-2 text-sm text-gray-700 mb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-green-600">✓</span>
                                        <span>Instant confirmation</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-green-600">✓</span>
                                        <span>Low fees (0.5% - 1%)</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-green-600">✓</span>
                                        <span>240+ cryptocurrencies</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-green-600">✓</span>
                                        <span>Global payment</span>
                                    </div>
                                </div>
                                <div class="p-3 bg-white rounded-lg">
                                    <p class="text-xs text-gray-600 mb-1">You will pay:</p>
                                    <p class="text-lg font-bold text-indigo-600">{{ loading ? 'Converting...' : displayAmount }}</p>
                                </div>
                            </div>

                            <!-- Phone Number (only for mobile money) -->
                            <div v-if="selectedMethod !== 'crypto'">
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Mobile Money Number
                                </label>
                                <div class="relative">
                                    <DevicePhoneMobileIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                                    <input
                                        id="phone"
                                        v-model="form.phone_number"
                                        type="tel"
                                        placeholder="097XXXXXXX"
                                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                        required
                                    />
                                </div>
                                <p v-if="form.errors.phone_number" class="mt-1 text-sm text-red-600">{{ form.errors.phone_number }}</p>
                                <p v-else-if="form.phone_number && !isValidPhone" class="mt-1 text-sm text-amber-600">
                                    Enter a valid Zambian mobile number (e.g., 097XXXXXXX)
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <button
                                type="submit"
                                :disabled="form.processing || !canSubmit"
                                class="w-full bg-green-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                            >
                                <span v-if="form.processing">Processing...</span>
                                <span v-else>Top Up Wallet</span>
                            </button>
                        </form>
                    </div>

                    <!-- Info Card -->
                    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-blue-900 mb-2">How it works:</h3>
                        <ul class="text-sm text-blue-800 space-y-1">
                            <li>1. Enter the amount you want to add</li>
                            <li>2. Select your mobile money provider</li>
                            <li>3. Enter your mobile money number</li>
                            <li>4. Confirm the payment on your phone</li>
                            <li>5. Funds will be added once payment is confirmed</li>
                        </ul>
                    </div>
                </template>

                <!-- ========== MANUAL PAYMENT MODE ========== -->
                <template v-else>
                    <!-- Payment Instructions Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <BanknotesIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                            </div>
                            <h2 class="text-lg font-semibold text-gray-900">How to Top Up</h2>
                        </div>
                        
                        <p class="text-gray-600 mb-6">
                            Send money to one of our payment numbers below, then submit your payment proof for verification.
                        </p>

                        <!-- Payment Numbers -->
                        <div class="grid md:grid-cols-2 gap-4 mb-6">
                            <!-- MTN -->
                            <div class="bg-yellow-50 border-2 border-yellow-300 rounded-xl p-4">
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="w-10 h-10 rounded-full bg-yellow-400 flex items-center justify-center">
                                        <span class="text-xs font-bold text-yellow-900">MTN</span>
                                    </div>
                                    <span class="font-bold text-gray-900">MTN Mobile Money</span>
                                </div>
                                <div class="bg-white rounded-lg px-3 py-2 mb-2">
                                    <p class="text-xs text-gray-600">Company Number</p>
                                    <p class="text-2xl font-bold text-gray-900">0760491206</p>
                                </div>
                                <p class="text-sm text-gray-700">Rockshield Investments Ltd</p>
                                <div class="bg-yellow-100 border border-yellow-300 rounded-lg p-2 mt-2">
                                    <p class="text-xs font-semibold text-yellow-900">⚠️ IMPORTANT:</p>
                                    <p class="text-xs text-yellow-800">Use <strong>WITHDRAW</strong> method (Agent Number), not Send Money.</p>
                                </div>
                                <div class="text-xs text-gray-700 space-y-1 mt-3">
                                    <p class="font-semibold">Steps:</p>
                                    <p>1. Dial *115# and call</p>
                                    <p>2. Choose option 2 (Withdraw)</p>
                                    <p>3. Choose Cash Out</p>
                                    <p>4. Enter 1 to choose Agent Number</p>
                                    <p>5. Enter Agent Number: 0760491206</p>
                                    <p>6. Enter the amount</p>
                                    <p>7. Enter your PIN</p>
                                </div>
                            </div>

                            <!-- Airtel -->
                            <div class="bg-red-50 border-2 border-red-300 rounded-xl p-4">
                                <div class="flex items-center gap-2 mb-3">
                                    <div class="w-10 h-10 rounded-full bg-red-500 flex items-center justify-center">
                                        <span class="text-xs font-bold text-white">AIR</span>
                                    </div>
                                    <span class="font-bold text-gray-900">Airtel Money</span>
                                </div>
                                <div class="bg-white rounded-lg px-3 py-2 mb-2">
                                    <p class="text-xs text-gray-600">Phone Number</p>
                                    <p class="text-2xl font-bold text-gray-900">0979230669</p>
                                </div>
                                <p class="text-sm text-gray-700">Kafula Mbulo</p>
                                <div class="bg-red-100 border border-red-300 rounded-lg p-2 mt-2">
                                    <p class="text-xs font-semibold text-red-900">📱 Regular Account:</p>
                                    <p class="text-xs text-red-800">Send money normally to this number.</p>
                                </div>
                                <div class="text-xs text-gray-700 space-y-1 mt-3">
                                    <p class="font-semibold">Steps:</p>
                                    <p>1. Dial *115#</p>
                                    <p>2. Select "Send Money"</p>
                                    <p>3. Enter: 0979230669</p>
                                    <p>4. Enter amount</p>
                                    <p>5. Confirm transaction</p>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Payment Button -->
                        <button
                            @click="goToManualPayment"
                            class="w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white py-4 px-6 rounded-xl font-semibold hover:from-green-700 hover:to-emerald-700 transition-all shadow-lg flex items-center justify-center gap-2"
                        >
                            <PhoneIcon class="h-5 w-5" aria-hidden="true" />
                            Submit Payment Proof
                        </button>
                    </div>

                    <!-- Info Card -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-green-900 mb-2">✅ After Payment:</h3>
                        <ul class="text-sm text-green-800 space-y-1">
                            <li>1. Click "Submit Payment Proof" above</li>
                            <li>2. Enter the amount you sent</li>
                            <li>3. Enter your transaction reference number</li>
                            <li>4. Our team will verify and credit your wallet</li>
                            <li>5. Usually verified within a few hours</li>
                        </ul>
                    </div>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
