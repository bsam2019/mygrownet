<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Journal Entries</h1>
                    <p class="text-gray-500 text-sm">Manual journal entries</p>
                </div>
                <button
                    @click="router.visit(route('growfinance.journals.create'))"
                    class="p-3 bg-amber-500 text-white rounded-xl shadow-lg shadow-amber-500/30 active:scale-95 transition-transform"
                    aria-label="Create journal entry"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>

            <div class="flex gap-2 overflow-x-auto pb-2 mb-4 -mx-4 px-4 scrollbar-hide">
                <button
                    v-for="s in statuses"
                    :key="s.value"
                    @click="filterByStatus(s.value)"
                    :class="[
                        'px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors',
                        filters.status === s.value ? 'bg-amber-500 text-white' : 'bg-white text-gray-600'
                    ]"
                >
                    {{ s.label }}
                </button>
            </div>

            <div class="space-y-3">
                <div v-for="entry in entries" :key="entry.id"
                    @click="router.visit(route('growfinance.journals.show', { journal: entry.id }))"
                    class="bg-white rounded-2xl shadow-sm p-4 active:scale-[0.98] transition-transform cursor-pointer"
                >
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-mono text-sm text-gray-500">{{ entry.journal_number }}</span>
                        <span :class="['px-2 py-0.5 rounded-full text-xs font-medium',
                            entry.status === 'posted' ? 'bg-emerald-100 text-emerald-700' :
                            entry.status === 'reversed' ? 'bg-red-100 text-red-700' :
                            'bg-gray-100 text-gray-600'
                        ]">
                            {{ entry.status }}
                        </span>
                    </div>
                    <p class="font-medium text-gray-900 truncate">{{ entry.description }}</p>
                    <div class="flex items-center justify-between mt-2 text-sm text-gray-500">
                        <span>{{ entry.date }}</span>
                    </div>
                </div>
                <div v-if="entries.length === 0" class="text-center py-12 text-gray-400">
                    No journal entries found
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
    entries: Array,
    periods: Array,
    filters: Object,
})

const statuses = [
    { value: '', label: 'All' },
    { value: 'draft', label: 'Draft' },
    { value: 'posted', label: 'Posted' },
    { value: 'reversed', label: 'Reversed' },
]

function filterByStatus(status) {
    router.visit(route('growfinance.journals.index', { status: status || null }), {
        preserveState: true,
        preserveScroll: true,
    })
}
</script>
