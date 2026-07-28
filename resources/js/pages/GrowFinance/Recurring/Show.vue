<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <div class="flex items-center gap-3 mb-5">
                <Link :href="route('growfinance.recurring.index')" class="p-2 -ml-2 rounded-lg hover:bg-gray-100">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" />
                </Link>
                <div class="flex-1">
                    <h1 class="text-xl font-bold text-gray-900">{{ recurring.description }}</h1>
                    <p class="text-sm text-gray-500 capitalize">{{ recurring.type }} &middot; {{ recurring.frequency }}</p>
                </div>
                <div class="flex gap-2">
                    <button v-if="recurring.is_active" @click="pause"
                        class="p-2 bg-amber-50 text-amber-600 rounded-xl hover:bg-amber-100 transition-colors"
                        title="Pause"
                    >
                        <PauseIcon class="h-5 w-5" />
                    </button>
                    <button v-else @click="resume"
                        class="p-2 bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-100 transition-colors"
                        title="Resume"
                    >
                        <PlayIcon class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-4">
                <div class="p-4 border-b border-gray-50">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Amount</span>
                        <span class="text-xl font-bold text-gray-900">{{ formatMoney(recurring.amount) }}</span>
                    </div>
                </div>
                <div class="divide-y divide-gray-50">
                    <div v-if="recurring.category" class="flex items-center justify-between p-4">
                        <span class="text-sm text-gray-500">Category</span>
                        <span class="text-sm font-medium text-gray-900">{{ recurring.category }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4">
                        <span class="text-sm text-gray-500">Status</span>
                        <span :class="['text-sm font-medium', recurring.is_active ? 'text-emerald-600' : 'text-gray-500']">{{ recurring.is_active ? 'Active' : 'Paused' }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4">
                        <span class="text-sm text-gray-500">Next Due</span>
                        <span class="text-sm font-medium text-gray-900">{{ recurring.next_due_date || '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4">
                        <span class="text-sm text-gray-500">Occurrences</span>
                        <span class="text-sm font-medium text-gray-900">{{ recurring.occurrences_count }}{{ recurring.max_occurrences ? ' / ' + recurring.max_occurrences : '' }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4">
                        <span class="text-sm text-gray-500">Started</span>
                        <span class="text-sm font-medium text-gray-900">{{ recurring.start_date || '—' }}</span>
                    </div>
                    <div v-if="recurring.account" class="flex items-center justify-between p-4">
                        <span class="text-sm text-gray-500">Account</span>
                        <span class="text-sm font-medium text-gray-900">{{ recurring.account.code }} - {{ recurring.account.name }}</span>
                    </div>
                    <div v-if="recurring.vendor" class="flex items-center justify-between p-4">
                        <span class="text-sm text-gray-500">Vendor</span>
                        <span class="text-sm font-medium text-gray-900">{{ recurring.vendor.name }}</span>
                    </div>
                    <div v-if="recurring.customer" class="flex items-center justify-between p-4">
                        <span class="text-sm text-gray-500">Customer</span>
                        <span class="text-sm font-medium text-gray-900">{{ recurring.customer.name }}</span>
                    </div>
                    <div v-if="recurring.notes" class="p-4">
                        <span class="text-sm text-gray-500 block mb-1">Notes</span>
                        <p class="text-sm text-gray-900">{{ recurring.notes }}</p>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <Link :href="route('growfinance.recurring.edit', recurring.id)"
                    class="flex-1 py-3 bg-cyan-500 text-white text-center rounded-xl font-medium active:scale-95 transition-transform"
                >Edit</Link>
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
import { ArrowLeftIcon, PauseIcon } from '@heroicons/vue/24/outline'
import { PlayIcon } from '@heroicons/vue/24/solid'

const props = defineProps({
    recurring: Object,
})

function formatMoney(amount: number): string {
    if (amount == null) return 'K0.00'
    return 'K' + Number(amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function pause() {
    router.post(route('growfinance.recurring.pause', props.recurring?.id))
}

function resume() {
    router.post(route('growfinance.recurring.resume', props.recurring?.id))
}

function destroy() {
    if (confirm('Delete this recurring transaction?')) {
        router.delete(route('growfinance.recurring.destroy', props.recurring?.id))
    }
}
</script>
