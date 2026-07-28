<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Budgets</h1>
                    <p class="text-gray-500 text-sm">Track spending against targets</p>
                </div>
                <button
                    @click="router.visit(route('growfinance.budgets.create'))"
                    class="p-3 bg-emerald-500 text-white rounded-xl shadow-lg shadow-emerald-500/30 active:scale-95 transition-transform"
                    aria-label="Create budget"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>

            <div v-if="summary" class="grid grid-cols-2 gap-3 mb-6">
                <div class="bg-white rounded-2xl p-4 shadow-sm">
                    <p class="text-xs text-gray-500 font-medium">Total Budget</p>
                    <p class="text-xl font-bold text-gray-900">{{ formatMoney(summary.total_budgeted) }}</p>
                </div>
                <div class="bg-white rounded-2xl p-4 shadow-sm">
                    <p class="text-xs text-gray-500 font-medium">Total Spent</p>
                    <p class="text-xl font-bold text-amber-600">{{ formatMoney(summary.total_spent) }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div v-for="budget in budgets" :key="budget.id"
                    class="border-b border-gray-50 last:border-0"
                >
                    <button @click="router.visit(route('growfinance.budgets.show', budget.id))"
                        class="w-full p-4 text-left active:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-center justify-between mb-2">
                            <p class="font-medium text-gray-900">{{ budget.name }}</p>
                            <span :class="['px-2 py-0.5 rounded-full text-xs font-medium',
                                budget.spent_amount > budget.budgeted_amount ? 'bg-red-100 text-red-700' :
                                budget.spent_amount >= budget.budgeted_amount * 0.8 ? 'bg-amber-100 text-amber-700' :
                                'bg-emerald-100 text-emerald-700'
                            ]">{{ budget.spent_amount > budget.budgeted_amount ? 'Over' : budget.spent_amount >= budget.budgeted_amount * 0.8 ? 'Almost' : 'On Track' }}</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2 mb-2">
                            <div class="h-2 rounded-full transition-all" :style="{ width: Math.min(100, (budget.spent_amount / budget.budgeted_amount) * 100) + '%', background: budget.spent_amount > budget.budgeted_amount ? '#ef4444' : budget.spent_amount >= budget.budgeted_amount * 0.8 ? '#f59e0b' : '#10b981' }"></div>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">{{ formatMoney(budget.spent_amount) }} spent</span>
                            <span class="text-gray-900 font-medium">{{ formatMoney(budget.budgeted_amount) }}</span>
                        </div>
                        <div v-if="budget.category" class="mt-1 text-xs text-gray-400">{{ budget.category }}</div>
                    </button>
                </div>
                <div v-if="budgets.length === 0" class="p-8 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                        <CurrencyDollarIcon class="h-6 w-6 text-gray-400" />
                    </div>
                    <p class="text-gray-500 text-sm">No budgets yet</p>
                    <button @click="router.visit(route('growfinance.budgets.create'))" class="text-emerald-600 text-sm font-medium mt-2">Create your first budget</button>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue'
import { PlusIcon, CurrencyDollarIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    budgets: Array,
    summary: Object,
})

function formatMoney(amount: number): string {
    if (amount == null) return 'K0.00'
    return 'K' + Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
</script>
