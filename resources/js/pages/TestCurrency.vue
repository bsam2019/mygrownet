<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CurrencySelector from '@/Components/CurrencySelector.vue';
import axios from 'axios';

const currentCurrency = ref('ZMW');
const testAmount = ref(100);
const convertedAmount = ref<number | null>(null);
const exchangeRate = ref<number | null>(null);
const loading = ref(false);
const detectedCountry = ref('Zambia');
const testResults = ref<any[]>([]);

const displayAmount = computed(() => {
    if (currentCurrency.value === 'ZMW') {
        return `K ${testAmount.value.toFixed(2)}`;
    }
    if (convertedAmount.value !== null) {
        return `$ ${convertedAmount.value.toFixed(2)}`;
    }
    return `K ${testAmount.value.toFixed(2)}`;
});

const handleCurrencyChange = async (currency: string) => {
    currentCurrency.value = currency;
    
    if (currency !== 'ZMW') {
        loading.value = true;
        try {
            const response = await axios.post('/api/currency/convert', {
                amount: testAmount.value,
                from: 'ZMW',
                to: currency
            });
            
            if (response.data.success) {
                convertedAmount.value = response.data.converted_amount;
                exchangeRate.value = response.data.exchange_rate;
            }
        } catch (error) {
            console.error('Conversion failed:', error);
        } finally {
            loading.value = false;
        }
    } else {
        convertedAmount.value = null;
        exchangeRate.value = null;
    }
};

const simulateZambian = async () => {
    loading.value = true;
    
    try {
        console.log('Setting currency to ZMW...');
        const response = await axios.post('/api/set-user-currency', { currency: 'ZMW' });
        console.log('Response:', response.data);
        
        if (response.data.success) {
            // Wait a moment for database to commit
            await new Promise(resolve => setTimeout(resolve, 500));
            
            alert('✅ Currency set to ZMW!\n\nRedirecting to wallet...\n\nLook for:\n- 🇿🇲 ZMW badge\n- K symbol on balance\n- "Stored in Zambian Kwacha"');
            
            // Hard redirect to wallet (bypasses Inertia cache)
            window.location.href = '/wallet';
        } else {
            alert('Failed: ' + (response.data.message || 'Unknown error'));
            loading.value = false;
        }
    } catch (error: any) {
        console.error('Error:', error);
        alert('Failed: ' + (error.response?.data?.message || error.message));
        loading.value = false;
    }
};

const simulateForeigner = async () => {
    loading.value = true;
    
    try {
        console.log('Setting currency to USD...');
        const response = await axios.post('/api/set-user-currency', { currency: 'USD' });
        console.log('Response:', response.data);
        
        if (response.data.success) {
            // Wait a moment for database to commit
            await new Promise(resolve => setTimeout(resolve, 500));
            
            alert('✅ Currency set to USD!\n\nRedirecting to wallet...\n\nLook for:\n- 🇺🇸 USD badge\n- $ symbol on balance\n- "Stored in US Dollars"');
            
            // Hard redirect to wallet (bypasses Inertia cache)
            window.location.href = '/wallet';
        } else {
            alert('Failed: ' + (response.data.message || 'Unknown error'));
            loading.value = false;
        }
    } catch (error: any) {
        console.error('Error:', error);
        alert('Failed: ' + (error.response?.data?.message || error.message));
        loading.value = false;
    }
};

const testCryptoPayment = async () => {
    loading.value = true;
    
    try {
        const amount = currentCurrency.value === 'ZMW' ? testAmount.value : (convertedAmount.value || testAmount.value);
        
        const response = await axios.post('/api/payments/crypto/create', {
            order_id: `TEST-${Date.now()}`,
            amount: amount,
            currency: currentCurrency.value,
        });
        
        if (response.data.success) {
            alert(`✓ Crypto invoice created!\n\nYour currency: ${currentCurrency.value}\nAmount: ${displayAmount.value}\n\nNOTE: After payment, wallet credited in ${currentCurrency.value}`);
            
            if (confirm('Open invoice?')) {
                window.open(response.data.invoice_url, '_blank');
            }
        }
    } catch (error: any) {
        alert(`Failed: ${error.response?.data?.message || error.message}`);
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <AppLayout title="Test Currency System">
        <Head title="Test Currency System" />

        <div class="py-12">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8 text-center">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">💱 Currency System Test</h1>
                    <p class="text-gray-600">Test the dual-currency wallet system (ZMW for Zambians, USD for Foreigners)</p>
                </div>

                <!-- Current Status -->
                <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Current Settings</h3>
                            <p class="text-sm text-gray-600">Your current currency configuration</p>
                        </div>
                        <CurrencySelector 
                            :show-conversion="false"
                            @currency-changed="handleCurrencyChange"
                        />
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white rounded-lg p-4">
                            <p class="text-xs text-gray-500 mb-1">Detected Location</p>
                            <p class="text-lg font-bold text-gray-900">{{ detectedCountry }}</p>
                        </div>
                        <div class="bg-white rounded-lg p-4">
                            <p class="text-xs text-gray-500 mb-1">Base Currency</p>
                            <p class="text-lg font-bold text-gray-900">{{ currentCurrency }}</p>
                        </div>
                        <div class="bg-white rounded-lg p-4">
                            <p class="text-xs text-gray-500 mb-1">Sample Amount (ZMW)</p>
                            <p class="text-lg font-bold text-gray-900">K {{ testAmount }}</p>
                        </div>
                        <div class="bg-white rounded-lg p-4">
                            <p class="text-xs text-gray-500 mb-1">Displayed As</p>
                            <p class="text-lg font-bold text-indigo-600">{{ loading ? 'Converting...' : displayAmount }}</p>
                        </div>
                    </div>
                </div>

                <!-- Test Buttons -->
                <div class="mb-6 bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Simulation Tests</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <button
                            @click="simulateZambian"
                            :disabled="loading"
                            class="p-4 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors disabled:opacity-50"
                        >
                            <div class="text-2xl mb-2">🇿🇲</div>
                            <div>Simulate Zambian</div>
                            <div class="text-xs mt-1 opacity-90">Currency: ZMW</div>
                        </button>
                        
                        <button
                            @click="simulateForeigner"
                            :disabled="loading"
                            class="p-4 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50"
                        >
                            <div class="text-2xl mb-2">🌍</div>
                            <div>Simulate Foreigner</div>
                            <div class="text-xs mt-1 opacity-90">Currency: USD</div>
                        </button>
                        
                        <button
                            @click="testCryptoPayment"
                            :disabled="loading"
                            class="p-4 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors disabled:opacity-50"
                        >
                            <div class="text-2xl mb-2">🪙</div>
                            <div>Test Crypto Payment</div>
                            <div class="text-xs mt-1 opacity-90">{{ displayAmount }}</div>
                        </button>
                    </div>
                </div>

                <!-- Test Results -->
                <div v-if="testResults.length > 0" class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Test Results</h3>
                    <div class="space-y-3">
                        <div
                            v-for="(result, index) in testResults"
                            :key="index"
                            class="flex items-start gap-4 p-4 bg-gray-50 rounded-lg"
                        >
                            <div class="text-3xl">{{ result.icon }}</div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900">{{ result.step }}</h4>
                                <p class="text-sm text-gray-600">{{ result.description }}</p>
                                <p class="text-lg font-bold text-indigo-600 mt-1">{{ result.result }}</p>
                            </div>
                            <div class="text-green-600">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Cards -->
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h4 class="font-semibold text-green-900 mb-2">🇿🇲 Zambian Users</h4>
                        <ul class="text-sm text-green-800 space-y-1">
                            <li>• Wallet in ZMW (Kwacha)</li>
                            <li>• All amounts shown in K</li>
                            <li>• Deposits in ZMW</li>
                            <li>• Crypto converts to ZMW</li>
                        </ul>
                    </div>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h4 class="font-semibold text-blue-900 mb-2">🌍 Foreign Users</h4>
                        <ul class="text-sm text-blue-800 space-y-1">
                            <li>• Wallet in USD (Dollar)</li>
                            <li>• All amounts shown in $</li>
                            <li>• Deposits in USD</li>
                            <li>• Crypto converts to USD</li>
                        </ul>
                    </div>
                </div>

                <!-- Exchange Rate Info -->
                <div v-if="exchangeRate" class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-2">📊 Current Exchange Rate</h4>
                    <p class="text-2xl font-bold text-indigo-600">1 ZMW = {{ exchangeRate.toFixed(4) }} USD</p>
                    <p class="text-sm text-gray-600 mt-1">Live rate from ExchangeRate-API</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
