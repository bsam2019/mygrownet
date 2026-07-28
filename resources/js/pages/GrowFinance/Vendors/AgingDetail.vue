<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <div class="flex items-center gap-3 mb-5">
                <Link :href="route('growfinance.aging.ap')" class="p-2 -ml-2 rounded-lg hover:bg-gray-100">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" />
                </Link>
                <div class="flex-1">
                    <h1 class="text-xl font-bold text-gray-900">{{ expenses[0]?.vendor_name || 'Vendor' }}</h1>
                    <p class="text-sm text-gray-500">AP Aging Detail</p>
                </div>
                <p class="text-lg font-bold text-gray-900">{{ formatMoney(total_outstanding) }}</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div v-for="exp in expenses" :key="exp.id"
                    class="border-b border-gray-50 last:border-0 p-4"
                >
                    <div class="flex items-center justify-between mb-1">
                        <span class="font-medium text-gray-900">{{ exp.description }}</span>
                        <span :class="['px-2 py-0.5 rounded-full text-xs font-medium',
                            exp.bucket === 'current' ? 'bg-emerald-100 text-emerald-700' :
                            exp.bucket === '1_30' ? 'bg-amber-100 text-amber-700' :
                            'bg-red-100 text-red-700'
                        ]">{{ exp.days_overdue > 0 ? exp.days_overdue + 'd overdue' : 'Current' }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">{{ exp.date }} &middot; {{ exp.category }}</span>
                        <span class="font-medium text-gray-900">{{ formatMoney(exp.amount) }}</span>
                    </div>
                </div>
                <div v-if="expenses.length === 0" class="p-8 text-center">
                    <p class="text-gray-500 text-sm">No outstanding expenses</p>
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
    vendor_id: Number,
    expenses: Array,
    total_outstanding: Number,
})

function formatMoney(amount: number): string {
    if (amount == null) return 'K0.00'
    return 'K' + Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
</script>
