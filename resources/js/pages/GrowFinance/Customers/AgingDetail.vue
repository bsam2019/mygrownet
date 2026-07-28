<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <div class="flex items-center gap-3 mb-5">
                <Link :href="route('growfinance.aging.ar')" class="p-2 -ml-2 rounded-lg hover:bg-gray-100">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" />
                </Link>
                <div class="flex-1">
                    <h1 class="text-xl font-bold text-gray-900">{{ invoices[0]?.customer_name || 'Customer' }}</h1>
                    <p class="text-sm text-gray-500">AR Aging Detail</p>
                </div>
                <p class="text-lg font-bold text-gray-900">{{ formatMoney(total_outstanding) }}</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div v-for="inv in invoices" :key="inv.id"
                    class="border-b border-gray-50 last:border-0 p-4"
                >
                    <div class="flex items-center justify-between mb-1">
                        <Link :href="route('growfinance.invoices.show', inv.id)" class="font-medium text-blue-600">
                            {{ inv.number }}
                        </Link>
                        <span :class="['px-2 py-0.5 rounded-full text-xs font-medium',
                            inv.bucket === 'current' ? 'bg-emerald-100 text-emerald-700' :
                            inv.bucket === '1_30' ? 'bg-amber-100 text-amber-700' :
                            'bg-red-100 text-red-700'
                        ]">{{ inv.days_overdue > 0 ? inv.days_overdue + 'd overdue' : 'Current' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">{{ inv.date }} &middot; Due {{ inv.due_date }}</span>
                        <span class="font-medium text-gray-900">{{ formatMoney(inv.balance_due) }}</span>
                    </div>
                </div>
                <div v-if="invoices.length === 0" class="p-8 text-center">
                    <p class="text-gray-500 text-sm">No outstanding invoices</p>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue'
import { Link } from '@inertiajs/vue3'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    customer_id: Number,
    invoices: Array,
    total_outstanding: Number,
})

function formatMoney(amount: number): string {
    if (amount == null) return 'K0.00'
    return 'K' + Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
</script>
