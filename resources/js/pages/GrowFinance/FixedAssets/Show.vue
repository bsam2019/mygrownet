<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <div class="flex items-center gap-3 mb-5">
                <Link :href="route('growfinance.fixed-assets.index')" class="p-2 -ml-2 rounded-lg hover:bg-gray-100">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" />
                </Link>
                <div class="flex-1">
                    <h1 class="text-xl font-bold text-gray-900">{{ asset.name }}</h1>
                    <span :class="['px-2 py-0.5 rounded-full text-xs font-medium',
                        asset.status === 'active' ? 'bg-emerald-100 text-emerald-700' :
                        asset.status === 'disposed' ? 'bg-gray-100 text-gray-600' :
                        'bg-blue-100 text-blue-700'
                    ]">{{ asset.status }}</span>
                </div>
                <div v-if="asset.status === 'active'" class="flex gap-2">
                    <button @click="runDepreciation"
                        class="p-2 bg-amber-50 text-amber-600 rounded-xl hover:bg-amber-100 transition-colors"
                        title="Run depreciation"
                    >
                        <ArrowPathIcon class="h-5 w-5" />
                    </button>
                    <button @click="showDispose = true"
                        class="p-2 bg-red-50 text-red-600 rounded-xl hover:bg-red-100 transition-colors"
                        title="Dispose asset"
                    >
                        <TrashIcon class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-2 mb-4">
                <div class="bg-white rounded-xl p-3 shadow-sm text-center">
                    <p class="text-xs text-gray-500">Cost</p>
                    <p class="text-sm font-bold text-gray-900">{{ formatMoney(asset.cost) }}</p>
                </div>
                <div class="bg-white rounded-xl p-3 shadow-sm text-center">
                    <p class="text-xs text-gray-500">Accum. Depr.</p>
                    <p class="text-sm font-bold text-amber-600">{{ formatMoney(asset.accumulated_depreciation) }}</p>
                </div>
                <div class="bg-white rounded-xl p-3 shadow-sm text-center">
                    <p class="text-xs text-gray-500">Net Book Value</p>
                    <p class="text-sm font-bold text-emerald-600">{{ formatMoney(asset.net_book_value) }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-4">
                <div class="divide-y divide-gray-50">
                    <div class="flex items-center justify-between p-4">
                        <span class="text-sm text-gray-500">Category</span>
                        <span class="text-sm font-medium text-gray-900">{{ asset.category || '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4">
                        <span class="text-sm text-gray-500">Purchase Date</span>
                        <span class="text-sm font-medium text-gray-900">{{ asset.purchase_date }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4">
                        <span class="text-sm text-gray-500">Residual Value</span>
                        <span class="text-sm font-medium text-gray-900">{{ formatMoney(asset.residual_value) }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4">
                        <span class="text-sm text-gray-500">Useful Life</span>
                        <span class="text-sm font-medium text-gray-900">{{ asset.useful_life_months }} months</span>
                    </div>
                    <div class="flex items-center justify-between p-4">
                        <span class="text-sm text-gray-500">Method</span>
                        <span class="text-sm font-medium text-gray-900 capitalize">{{ asset.depreciation_method.replace('_', ' ') }}</span>
                    </div>
                    <div v-if="asset.depreciation_rate" class="flex items-center justify-between p-4">
                        <span class="text-sm text-gray-500">Rate</span>
                        <span class="text-sm font-medium text-gray-900">{{ asset.depreciation_rate }}%</span>
                    </div>
                    <div v-if="asset.location" class="flex items-center justify-between p-4">
                        <span class="text-sm text-gray-500">Location</span>
                        <span class="text-sm font-medium text-gray-900">{{ asset.location }}</span>
                    </div>
                    <div v-if="asset.serial_number" class="flex items-center justify-between p-4">
                        <span class="text-sm text-gray-500">Serial</span>
                        <span class="text-sm font-medium text-gray-900">{{ asset.serial_number }}</span>
                    </div>
                    <div v-if="asset.notes" class="p-4">
                        <span class="text-sm text-gray-500 block mb-1">Notes</span>
                        <p class="text-sm text-gray-900">{{ asset.notes }}</p>
                    </div>
                </div>
            </div>

            <h3 class="text-lg font-bold text-gray-900 mb-3">Depreciation Schedule</h3>
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <div v-for="entry in schedule" :key="entry.id"
                    class="flex items-center justify-between p-3 border-b border-gray-50 last:border-0 text-sm"
                >
                    <span class="text-gray-600">{{ entry.period_date }}</span>
                    <span class="font-medium text-gray-900">{{ formatMoney(entry.depreciation_amount) }}</span>
                    <span class="text-gray-500">NBV: {{ formatMoney(entry.net_book_value) }}</span>
                    <span v-if="entry.journal_entry_id" class="text-emerald-600 text-xs">Posted</span>
                    <span v-else class="text-amber-600 text-xs">Pending</span>
                </div>
                <div v-if="schedule.length === 0" class="p-4 text-center text-gray-500 text-sm">
                    No depreciation schedule generated
                </div>
            </div>

            <!-- Dispose Modal -->
            <Teleport to="body">
                <Transition
                    enter-active-class="transition-opacity duration-200"
                    enter-from-class="opacity-0" enter-to-class="opacity-100"
                    leave-active-class="transition-opacity duration-150"
                    leave-from-class="opacity-100" leave-to-class="opacity-0"
                >
                    <div v-if="showDispose" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm" @click="showDispose = false" />
                </Transition>
                <Transition
                    enter-active-class="transition-all duration-200 ease-out"
                    enter-from-class="opacity-0 translate-y-4" enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-150 ease-in"
                    leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-4"
                >
                    <div v-if="showDispose" class="fixed bottom-0 left-0 right-0 z-50 bg-white rounded-t-2xl shadow-xl p-5 max-w-md mx-auto">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Dispose Asset</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Disposal Date</label>
                                <input v-model="disposeForm.disposal_date" type="date" required
                                    class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Proceeds (K)</label>
                                <input v-model="disposeForm.disposal_proceeds" type="number" step="0.01" min="0"
                                    class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                    placeholder="Amount received"
                                />
                            </div>
                            <div class="flex gap-3">
                                <button @click="showDispose = false"
                                    class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium"
                                >Cancel</button>
                                <button @click="confirmDispose"
                                    class="flex-1 py-3 bg-red-500 text-white rounded-xl font-medium active:scale-95 transition-transform"
                                >Dispose</button>
                            </div>
                        </div>
                    </div>
                </Transition>
            </Teleport>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue'
import { ArrowLeftIcon, ArrowPathIcon, TrashIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    asset: Object,
    schedule: Array,
})

const showDispose = ref(false)
const disposeForm = reactive({
    disposal_date: new Date().toISOString().split('T')[0],
    disposal_proceeds: '',
})

function formatMoney(amount: number): string {
    if (amount == null) return 'K0.00'
    return 'K' + Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function runDepreciation() {
    router.post(route('growfinance.fixed-assets.depreciate', props.asset?.id))
}

function confirmDispose() {
    router.post(route('growfinance.fixed-assets.dispose', props.asset?.id), { ...disposeForm })
}
</script>
