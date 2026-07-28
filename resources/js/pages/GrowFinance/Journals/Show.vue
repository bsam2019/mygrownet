<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <div class="flex items-center gap-3 mb-5">
                <Link :href="route('growfinance.journals.index')" class="p-2 -ml-2 rounded-lg hover:bg-gray-100">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </Link>
                <div class="flex-1">
                    <h1 class="text-xl font-bold text-gray-900">{{ entry.journal_number }}</h1>
                    <p class="text-sm text-gray-500">{{ entry.date }}</p>
                </div>
                <span :class="['px-3 py-1 rounded-full text-sm font-medium',
                    entry.status === 'posted' ? 'bg-emerald-100 text-emerald-700' :
                    entry.status === 'reversed' ? 'bg-red-100 text-red-700' :
                    'bg-gray-100 text-gray-600'
                ]">{{ entry.status }}</span>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-4 mb-4">
                <p class="text-gray-900">{{ entry.description }}</p>
                <p v-if="entry.reference" class="text-sm text-gray-500 mt-1">Ref: {{ entry.reference }}</p>
                <p v-if="entry.reversal_reason" class="text-sm text-red-600 mt-1">Reversal: {{ entry.reversal_reason }}</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-4">
                <div class="grid grid-cols-4 gap-2 px-4 py-3 bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-600 uppercase">
                    <div class="col-span-2">Account</div>
                    <div class="text-right">Debit</div>
                    <div class="text-right">Credit</div>
                </div>
                <div v-for="line in lines" :key="line.id"
                    class="grid grid-cols-4 gap-2 px-4 py-3 border-b border-gray-50 last:border-0"
                >
                    <div class="col-span-2">
                        <p class="font-medium text-gray-900 text-sm">{{ line.account?.name || 'Unknown' }}</p>
                        <p class="text-xs text-gray-500">{{ line.account?.code }}</p>
                    </div>
                    <div class="text-right text-sm text-gray-900">
                        {{ line.debit_amount > 0 ? 'K ' + Number(line.debit_amount).toLocaleString() : '-' }}
                    </div>
                    <div class="text-right text-sm text-gray-900">
                        {{ line.credit_amount > 0 ? 'K ' + Number(line.credit_amount).toLocaleString() : '-' }}
                    </div>
                </div>
                <div class="grid grid-cols-4 gap-2 px-4 py-3 bg-gray-50 font-semibold text-sm">
                    <div class="col-span-2 text-gray-700">Total</div>
                    <div class="text-right text-gray-900">K {{ totalDebit.toLocaleString() }}</div>
                    <div class="text-right text-gray-900">K {{ totalCredit.toLocaleString() }}</div>
                </div>
            </div>

            <div class="flex gap-3">
                <button v-if="entry.status === 'draft'"
                    @click="postJournal"
                    class="flex-1 py-3 bg-amber-500 text-white rounded-xl font-medium active:scale-95 transition-transform"
                >
                    Post Entry
                </button>
                <button v-if="entry.status === 'posted'"
                    @click="showReverseModal = true"
                    class="flex-1 py-3 bg-red-500 text-white rounded-xl font-medium active:scale-95 transition-transform"
                >
                    Reverse Entry
                </button>
            </div>
        </div>

        <div v-if="showReverseModal" class="fixed inset-0 bg-black/50 z-50 flex items-end" @click.self="showReverseModal = false">
            <div class="bg-white rounded-t-2xl w-full p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Reverse Journal Entry</h3>
                <textarea v-model="reverseReason" placeholder="Reason for reversal"
                    class="w-full p-3 border border-gray-200 rounded-xl mb-4 resize-none h-24"
                ></textarea>
                <div class="flex gap-3">
                    <button @click="showReverseModal = false"
                        class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl font-medium"
                    >Cancel</button>
                    <button @click="reverseJournal"
                        class="flex-1 py-3 bg-red-500 text-white rounded-xl font-medium active:scale-95 transition-transform"
                    >Confirm Reversal</button>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup>
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue'
import { router, Link } from '@inertiajs/vue3'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import { ref, computed } from 'vue'

const props = defineProps({
    entry: Object,
    lines: Array,
})

const showReverseModal = ref(false)
const reverseReason = ref('')

const totalDebit = computed(() => props.lines.reduce((s, l) => s + (l.debit_amount || 0), 0))
const totalCredit = computed(() => props.lines.reduce((s, l) => s + (l.credit_amount || 0), 0))

function postJournal() {
    router.post(route('growfinance.journals.post', { journal: props.entry.id }))
}

function reverseJournal() {
    if (!reverseReason.value.trim()) return
    router.post(route('growfinance.journals.reverse', { journal: props.entry.id }), {
        reason: reverseReason.value,
    })
}
</script>
