<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';

const props = defineProps<{
    currentCurrency: string;
    balance: number;
}>();

const processing = ref(false);

const setCurrency = (currency: string) => {
    if (processing.value) return;
    
    processing.value = true;
    
    router.post('/settings/currency', { currency }, {
        onSuccess: () => {
            alert(`✅ Currency changed to ${currency}!\n\nYour wallet is now in ${currency === 'USD' ? 'US Dollars ($)' : 'Zambian Kwacha (K)'}`);
            processing.value = false;
        },
        onError: (errors) => {
            alert('Failed: ' + (errors.currency || 'Unknown error'));
            processing.value = false;
        }
    });
};
</script>

<template>
    <AppLayout title="Currency Settings">
        <Head title="Currency Settings" />

        <div class="py-12">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Wallet Currency</h1>
                    <p class="text-sm text-gray-600 mb-8">Choose your base currency. Your wallet balance will be stored in this currency.</p>

                    <!-- Current Status -->
                    <div class="mb-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-sm text-blue-900 mb-2">Current Settings:</p>
                        <div class="flex items-center gap-3">
                            <span class="text-3xl">{{ currentCurrency === 'USD' ? '🇺🇸' : '🇿🇲' }}</span>
                            <div>
                                <p class="text-lg font-bold text-gray-900">{{ currentCurrency === 'USD' ? 'US Dollars' : 'Zambian Kwacha' }}</p>
                                <p class="text-sm text-gray-600">Balance: {{ currentCurrency === 'USD' ? '$' : 'K' }} {{ balance.toFixed(2) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Currency Options -->
                    <div class="space-y-4 mb-8">
                        <button
                            @click="setCurrency('ZMW')"
                            :disabled="processing || currentCurrency === 'ZMW'"
                            :class="[
                                'w-full p-6 border-2 rounded-xl text-left transition-all',
                                currentCurrency === 'ZMW' 
                                    ? 'border-green-500 bg-green-50' 
                                    : 'border-gray-200 hover:border-blue-300 hover:bg-blue-50'
                            ]"
                        >
                            <div class="flex items-center gap-4">
                                <span class="text-4xl">🇿🇲</span>
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-gray-900">Zambian Kwacha (ZMW)</h3>
                                    <p class="text-sm text-gray-600">Best for Zambian users • Symbol: K</p>
                                </div>
                                <div v-if="currentCurrency === 'ZMW'" class="text-green-600 font-bold">
                                    ✓ ACTIVE
                                </div>
                            </div>
                        </button>

                        <button
                            @click="setCurrency('USD')"
                            :disabled="processing || currentCurrency === 'USD'"
                            :class="[
                                'w-full p-6 border-2 rounded-xl text-left transition-all',
                                currentCurrency === 'USD' 
                                    ? 'border-green-500 bg-green-50' 
                                    : 'border-gray-200 hover:border-blue-300 hover:bg-blue-50'
                            ]"
                        >
                            <div class="flex items-center gap-4">
                                <span class="text-4xl">🇺🇸</span>
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-gray-900">US Dollars (USD)</h3>
                                    <p class="text-sm text-gray-600">Best for international users • Symbol: $</p>
                                </div>
                                <div v-if="currentCurrency === 'USD'" class="text-green-600 font-bold">
                                    ✓ ACTIVE
                                </div>
                            </div>
                        </button>
                    </div>

                    <!-- Important Note -->
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <p class="text-sm text-amber-900">
                            <strong>⚠️ Important:</strong> Your wallet balance is stored in your selected currency. 
                            Changing currency does NOT convert your existing balance.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
