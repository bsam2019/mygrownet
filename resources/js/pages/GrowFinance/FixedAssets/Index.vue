<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Fixed Assets</h1>
                    <p class="text-gray-500 text-sm">Asset register with depreciation</p>
                </div>
                <button @click="router.visit(route('growfinance.fixed-assets.create'))"
                    class="p-3 bg-indigo-500 text-white rounded-xl shadow-lg shadow-indigo-500/30 active:scale-95 transition-transform"
                    aria-label="Acquire asset"
                >
                    <PlusIcon class="h-5 w-5" />
                </button>
            </div>

            <div class="grid grid-cols-4 gap-2 mb-6">
                <div class="bg-white rounded-xl p-3 shadow-sm text-center">
                    <p class="text-xs text-gray-500">Total Cost</p>
                    <p class="text-sm font-bold text-gray-900">{{ formatMoney(summary.total_cost) }}</p>
                </div>
                <div class="bg-white rounded-xl p-3 shadow-sm text-center">
                    <p class="text-xs text-gray-500">Accum. Depr.</p>
                    <p class="text-sm font-bold text-amber-600">{{ formatMoney(summary.total_accumulated) }}</p>
                </div>
                <div class="bg-white rounded-xl p-3 shadow-sm text-center">
                    <p class="text-xs text-gray-500">Net Book Value</p>
                    <p class="text-sm font-bold text-emerald-600">{{ formatMoney(summary.total_nbv) }}</p>
                </div>
                <div class="bg-white rounded-xl p-3 shadow-sm text-center">
                    <p class="text-xs text-gray-500">Active Assets</p>
                    <p class="text-sm font-bold text-gray-900">{{ summary.active_count }}</p>
                </div>
            </div>

            <button @click="runAllDepreciation"
                class="w-full py-2 bg-amber-50 text-amber-700 rounded-xl text-sm font-medium mb-4 active:bg-amber-100 transition-colors"
            >Run Depreciation for All Assets</button>

            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div v-for="asset in assets" :key="asset.id"
                    class="border-b border-gray-50 last:border-0"
                >
                    <button @click="router.visit(route('growfinance.fixed-assets.show', asset.id))"
                        class="w-full p-4 text-left active:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-2">
                                <p class="font-medium text-gray-900">{{ asset.name }}</p>
                                <span :class="['px-2 py-0.5 rounded-full text-xs font-medium',
                                    asset.status === 'active' ? 'bg-emerald-100 text-emerald-700' :
                                    asset.status === 'disposed' ? 'bg-gray-100 text-gray-600' :
                                    'bg-blue-100 text-blue-700'
                                ]">{{ asset.status }}</span>
                            </div>
                            <p class="font-semibold text-gray-900">{{ formatMoney(asset.net_book_value) }}</p>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>{{ asset.category || 'Uncategorized' }} &middot; {{ asset.purchase_date }}</span>
                            <span>Cost: {{ formatMoney(asset.cost) }}</span>
                        </div>
                    </button>
                </div>
                <div v-if="assets.length === 0" class="p-8 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                        <CubeIcon class="h-6 w-6 text-gray-400" />
                    </div>
                    <p class="text-gray-500 text-sm">No fixed assets</p>
                    <button @click="router.visit(route('growfinance.fixed-assets.create'))" class="text-indigo-600 text-sm font-medium mt-2">Acquire your first asset</button>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue'
import { PlusIcon, CubeIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    assets: Array,
    summary: Object,
})

function formatMoney(amount: number): string {
    if (amount == null) return 'K0.00'
    return 'K' + Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function runAllDepreciation() {
    if (confirm('Run depreciation for all active assets for the current period?')) {
        router.post(route('growfinance.fixed-assets.depreciate-all'))
    }
}
</script>
