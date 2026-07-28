<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Accounting Periods</h1>
                    <p class="text-gray-500 text-sm">Manage fiscal years and periods</p>
                </div>
                <button
                    @click="router.visit(route('growfinance.periods.create'))"
                    class="p-3 bg-amber-500 text-white rounded-xl shadow-lg shadow-amber-500/30 active:scale-95 transition-transform"
                    aria-label="Create fiscal year"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>

            <div v-if="currentPeriod" class="bg-amber-50 rounded-2xl p-4 mb-6">
                <p class="text-sm text-amber-700 font-medium">Current Period</p>
                <p class="text-lg font-bold text-amber-900">{{ currentPeriod.label }}</p>
                <p class="text-sm text-amber-600">{{ currentPeriod.start_date }} - {{ currentPeriod.end_date }}</p>
            </div>

            <div v-for="group in periodsByYear" :key="group.fiscal_year.id" class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-bold text-gray-900">{{ group.fiscal_year.label }}</h2>
                    <span class="text-sm text-gray-500">{{ group.fiscal_year.start_date }} - {{ group.fiscal_year.end_date }}</span>
                </div>

                <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                    <div v-for="period in group.periods" :key="period.id"
                        class="flex items-center justify-between p-4 border-b border-gray-50 last:border-0"
                    >
                        <div>
                            <p class="font-medium text-gray-900">{{ period.label }}</p>
                            <p class="text-sm text-gray-500">{{ period.start_date }} - {{ period.end_date }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span :class="['px-2 py-0.5 rounded-full text-xs font-medium',
                                period.status === 'open' ? 'bg-emerald-100 text-emerald-700' :
                                period.status === 'closed' ? 'bg-gray-100 text-gray-600' :
                                'bg-red-100 text-red-700'
                            ]">{{ period.status }}</span>
                            <button v-if="period.status === 'open'"
                                @click="closePeriod(period.id)"
                                class="text-sm text-red-600 font-medium"
                            >Close</button>
                            <button v-if="period.status === 'closed'"
                                @click="reopenPeriod(period.id)"
                                class="text-sm text-amber-600 font-medium"
                            >Reopen</button>
                        </div>
                    </div>
                    <div v-if="group.periods.length === 0" class="p-4 text-center text-gray-400 text-sm">
                        No periods generated yet
                    </div>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup>
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue'
import { router } from '@inertiajs/vue3'
import { PlusIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    periodsByYear: Array,
    currentPeriod: Object,
})

function closePeriod(id) {
    if (confirm('Close this period? Unposted entries will prevent closing.')) {
        router.post(route('growfinance.periods.close', { period: id }))
    }
}

function reopenPeriod(id) {
    if (confirm('Reopen this period? New entries can be added.')) {
        router.post(route('growfinance.periods.reopen', { period: id }))
    }
}
</script>
