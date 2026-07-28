<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <div class="flex items-center gap-3 mb-5">
                <Link :href="route('growfinance.journals.index')" class="p-2 -ml-2 rounded-lg hover:bg-gray-100">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">New Journal Entry</h1>
                    <p class="text-sm text-gray-500">{{ currentPeriod?.label || 'No active period' }}</p>
                </div>
            </div>

            <form @submit.prevent="submit">
                <div class="bg-white rounded-2xl shadow-sm p-4 mb-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <input v-model="form.description" required
                            class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                            placeholder="What is this entry for?"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                            <input v-model="form.date" type="date" required
                                class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reference</label>
                            <input v-model="form.reference"
                                class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                                placeholder="Optional"
                            />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-4">
                    <div class="grid grid-cols-12 gap-2 px-4 py-3 bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-600 uppercase">
                        <div class="col-span-5">Account</div>
                        <div class="col-span-3 text-right">Debit</div>
                        <div class="col-span-3 text-right">Credit</div>
                        <div class="col-span-1"></div>
                    </div>

                    <div v-for="(line, i) in form.lines" :key="i"
                        class="grid grid-cols-12 gap-2 px-4 py-2 border-b border-gray-50 items-center"
                    >
                        <div class="col-span-5">
                            <select v-model="line.account_id" required
                                class="w-full p-2 border border-gray-200 rounded-lg text-sm"
                            >
                                <option value="">Select account</option>
                                <option v-for="a in accounts" :key="a.id" :value="a.id">
                                    {{ a.code }} - {{ a.name }}
                                </option>
                            </select>
                        </div>
                        <div class="col-span-3">
                            <input v-model="line.debit_amount" type="number" step="0.01" min="0"
                                class="w-full p-2 border border-gray-200 rounded-lg text-sm text-right"
                                placeholder="0.00"
                            />
                        </div>
                        <div class="col-span-3">
                            <input v-model="line.credit_amount" type="number" step="0.01" min="0"
                                class="w-full p-2 border border-gray-200 rounded-lg text-sm text-right"
                                placeholder="0.00"
                            />
                        </div>
                        <div class="col-span-1">
                            <button v-if="form.lines.length > 2" @click="removeLine(i)" type="button"
                                class="p-1 text-red-400 hover:text-red-600"
                            >&times;</button>
                        </div>
                    </div>

                    <div class="px-4 py-3 border-t border-gray-100">
                        <button @click="addLine" type="button"
                            class="text-sm text-amber-600 font-medium"
                        >+ Add Line</button>
                    </div>

                    <div class="grid grid-cols-12 gap-2 px-4 py-3 bg-gray-50 font-semibold text-sm border-t border-gray-100">
                        <div class="col-span-5 text-gray-700">Total</div>
                        <div class="col-span-3 text-right text-gray-900">K {{ totalDebit.toLocaleString() }}</div>
                        <div class="col-span-3 text-right text-gray-900">K {{ totalCredit.toLocaleString() }}</div>
                        <div class="col-span-1"></div>
                    </div>

                    <div v-if="!isBalanced && form.lines.length >= 2"
                        class="px-4 py-3 bg-red-50 text-red-600 text-sm font-medium"
                    >
                        Entry is not balanced (Difference: K {{ difference.toLocaleString() }})
                    </div>
                </div>

                <button type="submit" :disabled="!isBalanced || submitting"
                    class="w-full py-3 bg-amber-500 text-white rounded-xl font-medium active:scale-95 transition-transform disabled:opacity-50"
                >
                    {{ submitting ? 'Creating...' : 'Create Journal Entry' }}
                </button>
            </form>
        </div>
    </GrowFinanceLayout>
</template>

<script setup>
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue'
import { router, Link } from '@inertiajs/vue3'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import { ref, reactive, computed } from 'vue'

const props = defineProps({
    accounts: Array,
    currentPeriod: Object,
})

const submitting = ref(false)

const form = reactive({
    description: '',
    date: new Date().toISOString().split('T')[0],
    reference: '',
    lines: [
        { account_id: '', debit_amount: 0, credit_amount: 0 },
        { account_id: '', debit_amount: 0, credit_amount: 0 },
    ],
})

const totalDebit = computed(() => form.lines.reduce((s, l) => s + (parseFloat(l.debit_amount) || 0), 0))
const totalCredit = computed(() => form.lines.reduce((s, l) => s + (parseFloat(l.credit_amount) || 0), 0))
const isBalanced = computed(() => Math.abs(totalDebit.value - totalCredit.value) < 0.01)
const difference = computed(() => Math.abs(totalDebit.value - totalCredit.value))

function addLine() {
    form.lines.push({ account_id: '', debit_amount: 0, credit_amount: 0 })
}

function removeLine(i) {
    form.lines.splice(i, 1)
}

function submit() {
    if (!isBalanced.value) return
    submitting.value = true
    router.post(route('growfinance.journals.store'), {
        description: form.description,
        date: form.date,
        reference: form.reference || null,
        lines: form.lines.map(l => ({
            account_id: l.account_id,
            debit_amount: parseFloat(l.debit_amount) || 0,
            credit_amount: parseFloat(l.credit_amount) || 0,
        })),
    })
}
</script>
