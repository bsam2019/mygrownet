<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { BanknotesIcon, CreditCardIcon, DevicePhoneMobileIcon, BuildingLibraryIcon } from '@heroicons/vue/24/outline';

const props = defineProps<{
    companyId: number;
    initialDate?: string;
}>();

const selectedDate = ref(props.initialDate || new Date().toISOString().split('T')[0]);
const summary = ref<any>(null);
const loading = ref(false);

const fetchSummary = async () => {
    loading.value = true;
    try {
        const response = await fetch(`/cms/payments/daily-summary?date=${selectedDate.value}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        summary.value = await response.json();
    } catch (error) {
        console.error('Failed to fetch daily summary:', error);
    } finally {
        loading.value = false;
    }
};

const getMethodIcon = (method: string) => {
    switch (method) {
        case 'cash':
            return BanknotesIcon;
        case 'mobile_money':
            return DevicePhoneMobileIcon;
        case 'bank_transfer':
            return BuildingLibraryIcon;
        case 'card':
        case 'cheque':
            return CreditCardIcon;
        default:
            return BanknotesIcon;
    }
};

const getMethodColor = (method: string) => {
    switch (method) {
        case 'cash':
            return 'text-green-600 bg-green-50';
        case 'mobile_money':
            return 'text-blue-600 bg-blue-50';
        case 'bank_transfer':
            return 'text-indigo-600 bg-indigo-50';
        case 'card':
            return 'text-purple-600 bg-purple-50';
        case 'cheque':
            return 'text-amber-600 bg-amber-50';
        default:
            return 'text-gray-600 bg-gray-50';
    }
};

onMounted(() => {
    fetchSummary();
});
</script>

<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Daily Cash Summary</h3>
            <input
                v-model="selectedDate"
                type="date"
                @change="fetchSummary"
                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
        </div>

        <div v-if="loading" class="text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <p class="mt-2 text-sm text-gray-500">Loading summary...</p>
        </div>

        <div v-else-if="summary" class="space-y-6">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="text-sm text-blue-600 font-medium mb-1">Total Received</div>
                    <div class="text-2xl font-bold text-blue-900">K {{ summary.total_received.toFixed(2) }}</div>
                    <div class="text-xs text-blue-600 mt-1">{{ summary.payment_count }} payments</div>
                </div>

                <div class="bg-green-50 rounded-lg p-4">
                    <div class="text-sm text-green-600 font-medium mb-1">Allocated</div>
                    <div class="text-2xl font-bold text-green-900">K {{ summary.total_allocated.toFixed(2) }}</div>
                </div>

                <div class="bg-amber-50 rounded-lg p-4">
                    <div class="text-sm text-amber-600 font-medium mb-1">Unallocated</div>
                    <div class="text-2xl font-bold text-amber-900">K {{ summary.total_unallocated.toFixed(2) }}</div>
                </div>

                <div class="bg-purple-50 rounded-lg p-4">
                    <div class="text-sm text-purple-600 font-medium mb-1">Allocation Rate</div>
                    <div class="text-2xl font-bold text-purple-900">
                        {{ summary.total_received > 0 ? ((summary.total_allocated / summary.total_received) * 100).toFixed(1) : 0 }}%
                    </div>
                </div>
            </div>

            <!-- By Payment Method -->
            <div v-if="Object.keys(summary.by_method).length > 0">
                <h4 class="text-sm font-semibold text-gray-700 mb-3">By Payment Method</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div
                        v-for="(data, method) in summary.by_method"
                        :key="method"
                        class="flex items-center gap-3 p-3 rounded-lg border border-gray-200"
                    >
                        <div :class="['p-2 rounded-lg', getMethodColor(method)]">
                            <component :is="getMethodIcon(method)" class="h-5 w-5" />
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-900 capitalize">
                                {{ method.replace('_', ' ') }}
                            </div>
                            <div class="text-xs text-gray-500">{{ data.count }} payments</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-gray-900">K {{ data.total.toFixed(2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Payments Message -->
            <div v-else class="text-center py-8 text-gray-500">
                <BanknotesIcon class="h-12 w-12 mx-auto mb-2 text-gray-400" />
                <p>No payments recorded for this date</p>
            </div>
        </div>
    </div>
</template>
