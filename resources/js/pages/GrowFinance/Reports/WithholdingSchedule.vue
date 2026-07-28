<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue'

defineOptions({ layout: GrowFinanceLayout })

const props = defineProps({
    data: Object,
    saved_returns: Array,
})

const periodStart = ref(props.data.period_start)
const periodEnd = ref(props.data.period_end)

function refresh() {
    router.get(route('growfinance.tax.withholding-schedule'), {
        period_start: periodStart.value,
        period_end: periodEnd.value,
    }, { preserveState: true })
}

function saveReturn() {
    router.post(route('growfinance.tax.save-return'), {
        return_type: 'withholding',
        period_start: periodStart.value,
        period_end: periodEnd.value,
    }, { preserveState: true })
}
</script>

<template>
    <div class="space-y-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Withholding Tax Schedule</h1>

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
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Gross Payments</h3>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">K {{ data.items.reduce((a,i) => a + (i.gross_amount || 0), 0).toLocaleString() }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Withholding</h3>
                <p class="mt-2 text-3xl font-bold text-red-600">K {{ (data.total_withholding || 0).toLocaleString() }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Transactions</h3>
                <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ data.items.length }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Withholding Transactions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Gross</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Withholding</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Net</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="(item, i) in data.items" :key="i" class="hover:bg-gray-50 dark:hover:bg-gray-750">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ item.date }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ item.vendor_name || '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ item.description || '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900 dark:text-white">K {{ item.gross_amount.toLocaleString() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-600 dark:text-gray-400">{{ item.rate }}%</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-red-600">K {{ item.withholding_amount.toLocaleString() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-green-600">K {{ item.net_amount.toLocaleString() }}</td>
                        </tr>
                        <tr v-if="!data.items.length">
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400 italic">No withholding transactions in this period.</td>
                        </tr>
                    </tbody>
                    <tfoot v-if="data.items.length" class="bg-gray-50 dark:bg-gray-700 font-semibold">
                        <tr>
                            <td colspan="3" class="px-6 py-3 text-sm text-gray-900 dark:text-white">Total</td>
                            <td class="px-6 py-3 text-sm text-right text-gray-900 dark:text-white">K {{ data.items.reduce((a,i) => a + i.gross_amount, 0).toLocaleString() }}</td>
                            <td></td>
                            <td class="px-6 py-3 text-sm text-right text-red-600">K {{ (data.total_withholding || 0).toLocaleString() }}</td>
                            <td class="px-6 py-3 text-sm text-right text-green-600">K {{ data.items.reduce((a,i) => a + i.net_amount, 0).toLocaleString() }}</td>
                        </tr>
                    </tfoot>
                </table>
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
                        <span class="font-semibold">K {{ (r.withholding_collected || 0).toLocaleString() }}</span>
                        <span :class="r.status === 'filed' ? 'text-green-600 bg-green-100' : 'text-yellow-600 bg-yellow-100'"
                            class="px-2 py-1 text-xs rounded-full capitalize">{{ r.status }}</span>
                    </div>
                </div>
            </div>
            <p v-else class="text-gray-500 dark:text-gray-400 italic">No saved withholding returns yet.</p>
        </div>
    </div>
</template>
