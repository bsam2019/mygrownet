<template>
    <div class="relative">
        <!-- Currency Button -->
        <button
            @click="isOpen = !isOpen"
            class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
            <span class="text-lg">{{ currentCurrencyInfo?.flag || '🌍' }}</span>
            <span>{{ currentCurrency }}</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown -->
        <div
            v-if="isOpen"
            class="absolute right-0 z-50 mt-2 w-72 bg-white rounded-lg shadow-lg border border-gray-200"
        >
            <div class="p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Select Currency</h3>
                
                <!-- Popular Currencies -->
                <div class="space-y-1">
                    <button
                        v-for="(info, code) in popularCurrencies"
                        :key="code"
                        @click="selectCurrency(code)"
                        class="w-full flex items-center justify-between px-3 py-2 text-sm rounded-lg hover:bg-gray-50 transition-colors"
                        :class="{ 'bg-blue-50 text-blue-700': currentCurrency === code }"
                    >
                        <div class="flex items-center gap-3">
                            <span class="text-lg">{{ info.flag }}</span>
                            <div class="text-left">
                                <div class="font-medium">{{ code }}</div>
                                <div class="text-xs text-gray-500">{{ info.name }}</div>
                            </div>
                        </div>
                        <span class="text-gray-400">{{ info.symbol }}</span>
                    </button>
                </div>

                <!-- Conversion Preview -->
                <div v-if="showConversion && conversionAmount" class="mt-4 p-3 bg-gray-50 rounded-lg">
                    <div class="text-xs text-gray-600 mb-1">Example conversion:</div>
                    <div class="text-sm font-medium">
                        {{ formatAmount(100, 'ZMW') }} ≈ {{ formatAmount(conversionAmount, currentCurrency) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Overlay -->
        <div
            v-if="isOpen"
            @click="isOpen = false"
            class="fixed inset-0 z-40"
        ></div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    showConversion: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['currency-changed']);

const isOpen = ref(false);
const currentCurrency = ref('USD');
const popularCurrencies = ref({});
const conversionAmount = ref(null);

const currentCurrencyInfo = computed(() => {
    return popularCurrencies.value[currentCurrency.value] || null;
});

// Detect user's currency on mount
onMounted(async () => {
    try {
        // Get popular currencies
        const popularResponse = await axios.get('/api/currency/popular');
        const allCurrencies = popularResponse.data.currencies;
        
        // Only show ZMW and USD for dual-currency system
        popularCurrencies.value = {
            'ZMW': allCurrencies['ZMW'] || { name: 'Zambian Kwacha', symbol: 'K', flag: '🇿🇲' },
            'USD': allCurrencies['USD'] || { name: 'US Dollar', symbol: '$', flag: '🇺🇸' },
        };

        // Detect user's currency (will be ZMW or USD)
        const detectResponse = await axios.get('/api/currency/detect');
        currentCurrency.value = detectResponse.data.currency;

        // Get conversion example if needed
        if (props.showConversion) {
            await updateConversion();
        }

        emit('currency-changed', currentCurrency.value);
    } catch (error) {
        console.error('Failed to detect currency:', error);
        // Fallback to ZMW
        currentCurrency.value = 'ZMW';
        popularCurrencies.value = {
            'ZMW': { name: 'Zambian Kwacha', symbol: 'K', flag: '🇿🇲' },
            'USD': { name: 'US Dollar', symbol: '$', flag: '🇺🇸' },
        };
    }
});

// Watch for currency changes
watch(currentCurrency, async (newCurrency) => {
    if (props.showConversion) {
        await updateConversion();
    }
    emit('currency-changed', newCurrency);
});

async function selectCurrency(code) {
    currentCurrency.value = code;
    isOpen.value = false;

    try {
        // Save preference
        await axios.post('/api/currency/set', { currency: code });
    } catch (error) {
        console.error('Failed to save currency preference:', error);
    }
}

async function updateConversion() {
    if (currentCurrency.value === 'ZMW') {
        conversionAmount.value = 100;
        return;
    }

    try {
        const response = await axios.post('/api/currency/convert', {
            amount: 100,
            from: 'ZMW',
            to: currentCurrency.value,
        });

        if (response.data.success) {
            conversionAmount.value = response.data.converted_amount;
        }
    } catch (error) {
        console.error('Failed to convert currency:', error);
    }
}

function formatAmount(amount, currency) {
    const info = popularCurrencies.value[currency];
    const symbol = info?.symbol || currency;
    return `${symbol} ${amount.toFixed(2)}`;
}

// Expose current currency for parent components
defineExpose({
    currentCurrency,
});
</script>
