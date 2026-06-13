<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import MarketplaceLayout from '@/layouts/MarketplaceLayout.vue';
import CurrencySelector from '@/Components/CurrencySelector.vue';
import { 
    ShieldCheckIcon, 
    PhoneIcon, 
    ClipboardDocumentIcon,
    CurrencyDollarIcon,
    BanknotesIcon
} from '@heroicons/vue/24/outline';
import axios from 'axios';

interface Order {
    id: number;
    order_number: string;
    total: number;
    formatted_total: string;
    currency: string;
}

const props = defineProps<{ order: Order }>();

const form = useForm({ 
    payment_reference: '',
    payment_method: 'mobile_money' // mobile_money or crypto
});

const copied = ref(false);
const selectedCurrency = ref('ZMW');
const convertedAmount = ref<number | null>(null);
const exchangeRate = ref<number | null>(null);
const loading = ref(false);

// Payment method selection
const paymentMethod = ref<'mobile_money' | 'crypto'>('mobile_money');

const copyOrderNumber = () => {
    navigator.clipboard.writeText(props.order.order_number);
    copied.value = true;
    setTimeout(() => copied.value = false, 2000);
};

// Handle currency change from selector
const handleCurrencyChange = async (currency: string) => {
    selectedCurrency.value = currency;
    
    // If not ZMW, convert the amount
    if (currency !== 'ZMW') {
        loading.value = true;
        try {
            const response = await axios.post('/api/currency/convert', {
                amount: props.order.total,
                from: 'ZMW',
                to: currency
            });
            
            if (response.data.success) {
                convertedAmount.value = response.data.converted_amount;
                exchangeRate.value = response.data.exchange_rate;
            }
        } catch (error) {
            console.error('Currency conversion failed:', error);
        } finally {
            loading.value = false;
        }
    } else {
        convertedAmount.value = null;
        exchangeRate.value = null;
    }
};

// Display amount based on selected currency
const displayAmount = computed(() => {
    if (selectedCurrency.value === 'ZMW') {
        return props.order.formatted_total;
    }
    
    if (convertedAmount.value !== null) {
        return `${getCurrencySymbol(selectedCurrency.value)} ${convertedAmount.value.toFixed(2)}`;
    }
    
    return props.order.formatted_total;
});

const getCurrencySymbol = (currency: string): string => {
    const symbols: Record<string, string> = {
        'USD': '$',
        'EUR': '€',
        'GBP': '£',
        'ZMW': 'K',
        'ZAR': 'R',
        'KES': 'KSh',
        'NGN': '₦',
        'GHS': '₵',
        'CAD': 'C$',
        'AUD': 'A$'
    };
    return symbols[currency] || currency;
};

const submit = () => {
    form.payment_method = paymentMethod.value;
    form.post(route('marketplace.orders.confirm-payment', props.order.id));
};

// Initiate crypto payment
const initiateCryptoPayment = async () => {
    loading.value = true;
    try {
        // This would call your backend to create a NOWPayments invoice
        const response = await axios.post('/api/payments/crypto/create', {
            order_id: props.order.id,
            amount: convertedAmount.value || props.order.total,
            currency: selectedCurrency.value
        });
        
        if (response.data.success && response.data.invoice_url) {
            // Redirect to NOWPayments invoice page
            window.location.href = response.data.invoice_url;
        }
    } catch (error) {
        console.error('Failed to create crypto payment:', error);
        alert('Failed to initiate cryptocurrency payment. Please try again.');
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <Head title="Complete Payment - Enhanced" />
    <MarketplaceLayout>
        <div class="max-w-2xl mx-auto px-4 py-12">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <!-- Header with Currency Selector -->
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 mb-1">Complete Payment</h1>
                        <p class="text-gray-500">Order: {{ order.order_number }}</p>
                    </div>
                    <CurrencySelector 
                        :show-conversion="true"
                        @currency-changed="handleCurrencyChange"
                    />
                </div>
                
                <!-- Amount Display -->
                <div class="text-center p-6 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl mb-6 border border-blue-100">
                    <p class="text-sm text-gray-600 mb-1">Amount to Pay</p>
                    <p class="text-3xl font-bold text-blue-600">
                        {{ loading ? 'Converting...' : displayAmount }}
                    </p>
                    <p v-if="exchangeRate && selectedCurrency !== 'ZMW'" class="text-xs text-gray-500 mt-2">
                        Exchange rate: 1 ZMW = {{ exchangeRate.toFixed(4) }} {{ selectedCurrency }}
                    </p>
                </div>

                <!-- Payment Method Selection -->
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-900 mb-3">Select Payment Method</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Mobile Money -->
                        <button
                            @click="paymentMethod = 'mobile_money'"
                            class="p-4 border-2 rounded-lg transition-all"
                            :class="paymentMethod === 'mobile_money' 
                                ? 'border-blue-500 bg-blue-50' 
                                : 'border-gray-200 hover:border-gray-300'"
                        >
                            <PhoneIcon class="h-6 w-6 mx-auto mb-2" 
                                :class="paymentMethod === 'mobile_money' ? 'text-blue-600' : 'text-gray-400'"
                                aria-hidden="true" />
                            <p class="font-semibold text-sm text-gray-900">Mobile Money</p>
                            <p class="text-xs text-gray-500 mt-1">MTN, Airtel</p>
                        </button>

                        <!-- Cryptocurrency -->
                        <button
                            @click="paymentMethod = 'crypto'"
                            class="p-4 border-2 rounded-lg transition-all"
                            :class="paymentMethod === 'crypto' 
                                ? 'border-indigo-500 bg-indigo-50' 
                                : 'border-gray-200 hover:border-gray-300'"
                        >
                            <BanknotesIcon class="h-6 w-6 mx-auto mb-2" 
                                :class="paymentMethod === 'crypto' ? 'text-indigo-600' : 'text-gray-400'"
                                aria-hidden="true" />
                            <p class="font-semibold text-sm text-gray-900">Cryptocurrency</p>
                            <p class="text-xs text-gray-500 mt-1">Pay with crypto from abroad</p>
                        </button>
                    </div>
                </div>

                <!-- Mobile Money Instructions -->
                <div v-if="paymentMethod === 'mobile_money'" class="space-y-4 mb-6">
                    <h3 class="font-semibold text-gray-900">Payment Instructions</h3>
                    
                    <!-- Mobile Money Options -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="p-4 border-2 border-orange-500 rounded-lg bg-orange-50">
                            <div class="flex items-center gap-2 mb-2">
                                <PhoneIcon class="h-5 w-5 text-orange-600" aria-hidden="true" />
                                <span class="font-semibold text-gray-900">MTN MoMo</span>
                            </div>
                            <p class="text-xs text-gray-600">Dial *303#</p>
                        </div>
                        <div class="p-4 border-2 border-red-500 rounded-lg bg-red-50">
                            <div class="flex items-center gap-2 mb-2">
                                <PhoneIcon class="h-5 w-5 text-red-600" aria-hidden="true" />
                                <span class="font-semibold text-gray-900">Airtel Money</span>
                            </div>
                            <p class="text-xs text-gray-600">Dial *778#</p>
                        </div>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg text-sm space-y-3 text-gray-700">
                        <div class="flex gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
                            <p>Send <strong class="text-gray-900">{{ displayAmount }}</strong> via MTN MoMo or Airtel Money</p>
                        </div>
                        <div class="flex gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
                            <div>
                                <p class="mb-1">Use this order number as reference:</p>
                                <button 
                                    type="button"
                                    @click="copyOrderNumber" 
                                    class="inline-flex items-center gap-2 px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-blue-600 font-mono text-sm hover:bg-gray-50 transition-colors"
                                >
                                    {{ order.order_number }}
                                    <ClipboardDocumentIcon class="h-4 w-4" aria-hidden="true" />
                                </button>
                                <span v-if="copied" class="text-green-600 text-xs ml-2 font-medium">✓ Copied!</span>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs font-bold">3</span>
                            <p>Enter your transaction reference below and click "Confirm Payment"</p>
                        </div>
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Reference *</label>
                            <input v-model="form.payment_reference" type="text" 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-gray-900 bg-white placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                placeholder="Enter MoMo/Airtel transaction ID" />
                            <p v-if="form.errors.payment_reference" class="mt-1 text-sm text-red-600">
                                {{ form.errors.payment_reference }}
                            </p>
                        </div>
                        
                        <div class="flex items-center gap-2 p-3 bg-green-50 rounded-lg">
                            <ShieldCheckIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                            <span class="text-sm text-green-800">Payment protected by escrow</span>
                        </div>

                        <button type="submit" :disabled="form.processing"
                            class="w-full py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                            {{ form.processing ? 'Confirming...' : 'Confirm Payment' }}
                        </button>
                    </form>
                </div>

                <!-- Cryptocurrency Instructions -->
                <div v-else class="space-y-4 mb-6">
                    <h3 class="font-semibold text-gray-900">Cryptocurrency Payment</h3>
                    
                    <div class="p-4 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-lg border border-indigo-100">
                        <div class="flex items-start gap-3 mb-4">
                            <BanknotesIcon class="h-6 w-6 text-indigo-600 flex-shrink-0" aria-hidden="true" />
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-1">Pay with 240+ Cryptocurrencies</h4>
                                <p class="text-sm text-gray-600">
                                    300+ cryptocurrencies supported including BTC, ETH, USDT, and more
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2 text-sm text-gray-700 mb-4">
                            <div class="flex items-center gap-2">
                                <span class="text-green-600">✓</span>
                                <span>Instant payment confirmation</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-green-600">✓</span>
                                <span>Low transaction fees (0.5% - 1%)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-green-600">✓</span>
                                <span>Secure blockchain transactions</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-green-600">✓</span>
                                <span>No bank account required</span>
                            </div>
                        </div>

                        <div class="p-3 bg-white rounded-lg border border-indigo-200 mb-4">
                            <p class="text-xs text-gray-600 mb-1">You will pay approximately:</p>
                            <p class="text-lg font-bold text-indigo-600">{{ displayAmount }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                Final amount in crypto will be calculated at checkout
                            </p>
                        </div>

                        <button 
                            @click="initiateCryptoPayment"
                            :disabled="loading"
                            class="w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-lg hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 transition-all shadow-md hover:shadow-lg"
                        >
                            {{ loading ? 'Processing...' : 'Pay with Cryptocurrency' }}
                        </button>
                    </div>

                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-blue-800">
                            <strong>How it works:</strong> You'll be redirected to our secure payment processor where you can select your preferred cryptocurrency and complete the payment.
                        </p>
                    </div>
                </div>

                <!-- Help Text -->
                <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                    <p class="text-sm text-gray-700">
                        <strong>Need help?</strong> Contact support or visit our Help Center
                    </p>
                </div>
            </div>
        </div>
    </MarketplaceLayout>
</template>
