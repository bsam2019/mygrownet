<script setup>
import { ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue'

defineOptions({ layout: GrowFinanceLayout })

const props = defineProps({
    data: Object,
    saved_returns: Array,
})

const periodStart = ref(props.data.period_start)
const periodEnd = ref(props.data.period_end)

function refresh() {
    router.get(route('growfinance.tax.vat-return'), {
        period_start: periodStart.value,
        period_end: periodEnd.value,
    }, { preserveState: true })
}

function saveReturn() {
    router.post(route('growfinance.tax.save-return'), {
        return_type: 'vat',
        period_start: periodStart.value,
        period_end: periodEnd.value,
    }, { preserveState: true })
}
</script>

<template>
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">VAT Return</h1>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Period Start</label>
                <input v-model="periodStart" type="date"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Period End</label>
                <input v-model="periodEnd" type="date"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
            </div>
            <div class="flex items-end space-x-2">
                <button @click="refresh"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Refresh</button>
                <button @click="saveReturn"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Save Return</button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Sales</h3>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">K {{ (data.total_sales || 0).toLocaleString() }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Purchases</h3>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">K {{ (data.total_purchases || 0).toLocaleString() }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Net VAT Payable</h3>
                <p class="mt-2 text-3xl font-bold" :class="data.net_vat_payable > 0 ? 'text-red-600' : 'text-green-600'">
                    K {{ (data.net_vat_payable || 0).toLocaleString() }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">Output VAT (Sales)</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Total Sales (Incl VAT)</span>
                        <span class="font-semibold">K {{ (data.total_sales || 0).toLocaleString() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">VAT Collected</span>
                        <span class="font-semibold text-blue-600">K {{ (data.output_vat || 0).toLocaleString() }}</span>
                    </div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-4">Input VAT (Purchases)</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Total Purchases (Incl VAT)</span>
                        <span class="font-semibold">K {{ (data.total_purchases || 0).toLocaleString() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">VAT Claimed</span>
                        <span class="font-semibold text-green-600">K {{ (data.input_vat || 0).toLocaleString() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Saved Returns</h3>
            <div v-if="saved_returns.length" class="space-y-2">
                <div v-for="r in saved_returns" :key="r.id"
                    class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded">
                    <div>
                        <span class="font-medium">{{ r.period_label }}</span>
                        <span class="ml-3 text-sm text-gray-500">{{ r.period_start }} → {{ r.period_end }}</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="font-semibold">K {{ (r.net_vat_payable || 0).toLocaleString() }}</span>
                        <span :class="r.status === 'filed' ? 'text-green-600 bg-green-100' : 'text-yellow-600 bg-yellow-100'"
                            class="px-2 py-1 text-xs rounded-full capitalize">{{ r.status }}</span>
                    </div>
                </div>
            </div>
            <p v-else class="text-gray-500 dark:text-gray-400 italic">No saved VAT returns yet.</p>
        </div>
    </div>
</template>
