<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">AR Aging</h1>
                    <p class="text-gray-500 text-sm">Accounts Receivable by overdue period</p>
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold text-gray-900">{{ formatMoney(total_outstanding) }}</p>
                    <p class="text-xs text-gray-500">Total Outstanding</p>
                </div>
            </div>

            <div class="grid grid-cols-5 gap-2 mb-6">
                <div v-for="(bucket, key) in buckets" :key="key"
                    class="bg-white rounded-xl p-3 shadow-sm text-center"
                >
                    <p class="text-xs text-gray-500 font-medium">{{ bucket.label }}</p>
                    <p :class="['text-sm font-bold', key === '90_plus' || key === '61_90' ? 'text-red-600' : key === '1_30' ? 'text-amber-600' : 'text-gray-900']">
                        {{ formatMoney(bucket.total) }}
                    </p>
                    <p class="text-xs text-gray-400">{{ bucket.count }} invoices</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div v-for="customer in by_customer" :key="customer.customer_id"
                    class="border-b border-gray-50 last:border-0"
                >
                    <button @click="router.visit(route('growfinance.aging.customer', customer.customer_id))"
                        class="w-full p-4 text-left active:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <p class="font-medium text-gray-900">{{ customer.customer_name }}</p>
                                <p class="text-xs text-gray-500">{{ customer.email || customer.phone || '' }}</p>
                            </div>
                            <p class="font-bold text-gray-900">{{ formatMoney(customer.total_due) }}</p>
                        </div>
                        <div class="grid grid-cols-5 gap-1">
                            <div v-for="(val, key) in customer.buckets" :key="key"
                                class="text-center text-xs py-1 rounded"
                                :class="val > 0 ? (key === '90_plus' || key === '61_90' ? 'bg-red-50 text-red-700' : key === '1_30' ? 'bg-amber-50 text-amber-700' : 'bg-gray-50 text-gray-700') : 'text-gray-300'"
                            >{{ val > 0 ? formatMoney(val) : '-' }}</div>
                        </div>
                    </button>
                </div>
                <div v-if="by_customer.length === 0" class="p-8 text-center">
                    <p class="text-gray-500 text-sm">No outstanding receivables</p>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue'

const props = defineProps({
    buckets: Object,
    by_customer: Array,
    total_outstanding: Number,
})

function formatMoney(amount: number): string {
    if (amount == null) return 'K0.00'
    return 'K' + Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
</script>
