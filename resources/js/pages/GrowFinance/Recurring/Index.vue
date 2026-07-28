<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Recurring Transactions</h1>
                    <p class="text-gray-500 text-sm">Automated income and expenses</p>
                </div>
                <div class="flex gap-2">
                    <button @click="processDue"
                        class="px-4 py-2 bg-amber-50 text-amber-700 rounded-xl text-sm font-medium hover:bg-amber-100 active:scale-95 transition-transform"
                    >Process Due</button>
                    <button @click="router.visit(route('growfinance.recurring.create'))"
                        class="p-3 bg-cyan-500 text-white rounded-xl shadow-lg shadow-cyan-500/30 active:scale-95 transition-transform"
                        aria-label="Create recurring"
                    >
                        <PlusIcon class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <div v-if="upcoming && upcoming.length > 0" class="mb-6">
                <p class="text-sm font-medium text-gray-700 mb-2">Upcoming (next 14 days)</p>
                <div class="bg-amber-50 rounded-2xl p-3 space-y-2">
                    <div v-for="item in upcoming" :key="item.id"
                        class="flex items-center justify-between text-sm"
                    >
                        <span class="text-amber-900 font-medium">{{ item.description }}</span>
                        <span class="text-amber-700">{{ formatMoney(item.amount) }} - {{ item.next_due_date }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div v-for="item in recurring" :key="item.id"
                    class="border-b border-gray-50 last:border-0"
                >
                    <button @click="router.visit(route('growfinance.recurring.show', item.id))"
                        class="w-full p-4 text-left active:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div :class="['w-10 h-10 rounded-full flex items-center justify-center', item.type === 'income' ? 'bg-emerald-100' : 'bg-red-100']">
                                    <BanknotesIcon :class="['h-5 w-5', item.type === 'income' ? 'text-emerald-600' : 'text-red-600']" />
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ item.description }}</p>
                                    <p class="text-xs text-gray-500">{{ item.frequency }} &middot; {{ item.category || 'Uncategorized' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900">{{ formatMoney(item.amount) }}</p>
                                <div class="flex items-center gap-1 justify-end mt-0.5">
                                    <span :class="['w-1.5 h-1.5 rounded-full', item.is_active ? 'bg-emerald-500' : 'bg-gray-300']"></span>
                                    <span class="text-xs" :class="item.is_active ? 'text-emerald-600' : 'text-gray-400'">{{ item.is_active ? 'Active' : 'Paused' }}</span>
                                </div>
                            </div>
                        </div>
                    </button>
                </div>
                <div v-if="recurring.length === 0" class="p-8 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                        <ArrowPathIcon class="h-6 w-6 text-gray-400" />
                    </div>
                    <p class="text-gray-500 text-sm">No recurring transactions</p>
                    <button @click="router.visit(route('growfinance.recurring.create'))" class="text-cyan-600 text-sm font-medium mt-2">Create one</button>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue'
import { PlusIcon, ArrowPathIcon, BanknotesIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    recurring: Array,
    upcoming: Array,
})

function formatMoney(amount: number): string {
    if (amount == null) return 'K0.00'
    return 'K' + Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function processDue() {
    router.post(route('growfinance.recurring.process'))
}
</script>
