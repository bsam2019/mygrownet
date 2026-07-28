<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <div class="flex items-center gap-3 mb-5">
                <Link :href="route('growfinance.budgets.index')" class="p-2 -ml-2 rounded-lg hover:bg-gray-100">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" />
                </Link>
                <div class="flex-1">
                    <h1 class="text-xl font-bold text-gray-900">{{ budget.name }}</h1>
                    <p class="text-sm text-gray-500">{{ budget.category || 'Uncategorized' }}</p>
                </div>
                <div class="flex gap-2">
                    <button @click="recalculate"
                        class="p-2 bg-amber-50 text-amber-600 rounded-xl hover:bg-amber-100 transition-colors"
                        title="Recalculate spent"
                    >
                        <ArrowPathIcon class="h-5 w-5" />
                    </button>
                    <button @click="rollover"
                        class="p-2 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-100 transition-colors"
                        title="Rollover to new period"
                    >
                        <ForwardIcon class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-5 mb-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm text-gray-500">Progress</span>
                    <span :class="['text-sm font-medium', budget.spent_amount > budget.budgeted_amount ? 'text-red-600' : 'text-gray-900']">
                        {{ formatMoney(budget.spent_amount) }} / {{ formatMoney(budget.budgeted_amount) }}
                    </span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-4 mb-1">
                    <div class="h-4 rounded-full transition-all" :style="{ width: Math.min(100, pct) + '%', background: barColor }"></div>
                </div>
                <p class="text-xs text-gray-400 text-right">{{ pct.toFixed(1) }}% spent</p>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-6">
                <div class="bg-white rounded-2xl p-4 shadow-sm">
                    <p class="text-xs text-gray-500 font-medium">Remaining</p>
                    <p class="text-lg font-bold" :class="remaining >= 0 ? 'text-emerald-600' : 'text-red-600'">{{ formatMoney(remaining) }}</p>
                </div>
                <div class="bg-white rounded-2xl p-4 shadow-sm">
                    <p class="text-xs text-gray-500 font-medium">Period</p>
                    <p class="text-lg font-bold text-gray-900 capitalize">{{ budget.period || 'custom' }}</p>
                </div>
            </div>

            <div class="flex gap-3">
                <Link :href="route('growfinance.budgets.edit', budget.id)"
                    class="flex-1 py-3 bg-emerald-500 text-white text-center rounded-xl font-medium active:scale-95 transition-transform"
                >Edit Budget</Link>
                <button @click="destroy"
                    class="py-3 px-6 bg-red-50 text-red-600 rounded-xl font-medium active:scale-95 transition-transform"
                >Delete</button>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue'
import { router, Link } from '@inertiajs/vue3'
import { ArrowLeftIcon, ArrowPathIcon, ForwardIcon } from '@heroicons/vue/24/outline'
import { computed } from 'vue'

const props = defineProps({
    budget: Object,
})

const pct = computed(() => {
    if (!props.budget?.budgeted_amount) return 0
    return Math.min(100, (props.budget.spent_amount / props.budget.budgeted_amount) * 100)
})

const remaining = computed(() => {
    return (props.budget?.budgeted_amount || 0) - (props.budget?.spent_amount || 0)
})

const barColor = computed(() => {
    if (props.budget?.spent_amount > props.budget?.budgeted_amount) return '#ef4444'
    if (pct.value >= 80) return '#f59e0b'
    return '#10b981'
})

function formatMoney(amount: number): string {
    if (amount == null) return 'K0.00'
    return 'K' + Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function recalculate() {
    router.post(route('growfinance.budgets.recalculate', props.budget?.id))
}

function rollover() {
    if (confirm('Roll over unused budget to new period?')) {
        router.post(route('growfinance.budgets.rollover', props.budget?.id))
    }
}

function destroy() {
    if (confirm('Delete this budget?')) {
        router.delete(route('growfinance.budgets.destroy', props.budget?.id))
    }
}
</script>
