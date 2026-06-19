<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue'
import {
  ArrowLeftIcon,
  PlusIcon,
  DocumentArrowUpIcon,
  CheckCircleIcon,
  XCircleIcon,
  LinkIcon,
} from '@heroicons/vue/24/outline'

defineOptions({
  layout: GrowFinanceLayout
})

interface BankAccount {
  id: number
  account_name: string
  bank_name: string | null
  account_number: string | null
  current_balance: number
}

interface Transaction {
  id: number
  journal_entry_id: number
  account_id: number
  debit_amount: number
  credit_amount: number
  reconciled: boolean
  reconciled_at: string | null
  journalEntry: {
    id: number
    entry_date: string
    description: string
    reference: string | null
    entry_number: string
  }
  account: {
    id: number
    name: string
    code: string
  }
}

interface StatementLine {
  id: number
  transaction_date: string
  description: string
  reference: string | null
  debit_amount: number
  credit_amount: number
  status: string
}

interface Statement {
  id: number
  statement_period: string | null
  start_date: string
  end_date: string
  opening_balance: number
  closing_balance: number
  status: string
  lines: StatementLine[]
}

interface ReconciliationPeriod {
  id: number
  start_date: string
  end_date: string
  opening_balance: number
  closing_balance: number
  book_balance: number
  difference: number
  status: string
  created_at: string
}

interface Props {
  bankAccounts: BankAccount[]
  cashAccounts: any[]
  selectedBankAccount: BankAccount | null
  periods: ReconciliationPeriod[]
  selectedPeriod: ReconciliationPeriod | null
  transactions: Transaction[]
  statements: Statement[]
}

const props = defineProps<Props>()

const selectedAccountId = ref(props.selectedBankAccount?.id || props.bankAccounts[0]?.id)
const selectedPeriodId = ref(props.selectedPeriod?.id || null)
const showImportModal = ref(false)
const showMatchModal = ref(false)
const selectedStatementLineId = ref<number | null>(null)
const selectedJournalLineId = ref<number | null>(null)

const importForm = useForm({
  bank_account_id: selectedAccountId.value,
  start_date: '',
  end_date: '',
  opening_balance: 0,
  closing_balance: 0,
  statement_period: '',
  lines: [] as {
    transaction_date: string
    description: string
    reference: string
    debit_amount: number
    credit_amount: number
  }[],
})

const addLine = () => {
  importForm.lines.push({
    transaction_date: '',
    description: '',
    reference: '',
    debit_amount: 0,
    credit_amount: 0,
  })
}

const removeLine = (index: number) => {
  importForm.lines.splice(index, 1)
}

const submitImport = () => {
  importForm.post(route('growfinance.banking.import-statement'), {
    preserveScroll: true,
    onSuccess: () => {
      showImportModal.value = false
      importForm.reset()
    },
  })
}

const handleAccountChange = () => {
  router.get(route('growfinance.banking.reconcile'), {
    account_id: selectedAccountId.value,
  }, { preserveState: true })
}

const handlePeriodChange = () => {
  router.get(route('growfinance.banking.reconcile'), {
    account_id: selectedAccountId.value,
    period_id: selectedPeriodId.value,
  }, { preserveState: true })
}

const matchTransaction = (statementLineId: number, journalLineId: number) => {
  router.post(route('growfinance.banking.match'), {
    period_id: selectedPeriodId.value,
    statement_line_id: statementLineId,
    journal_line_id: journalLineId,
  }, { preserveScroll: true })
}

const unmatchTransaction = (matchId: number) => {
  router.post(route('growfinance.banking.unmatch', matchId), {}, { preserveScroll: true })
}

const completeReconciliation = () => {
  if (!selectedPeriodId.value || !selectedAccountId.value) return
  router.post(route('growfinance.banking.reconcile.store'), {
    account_id: selectedAccountId.value,
    period_id: selectedPeriodId.value,
    statement_balance: props.selectedPeriod?.closing_balance || 0,
  }, { preserveScroll: true })
}

const formatMoney = (amount: number) => {
  return 'K' + Math.abs(amount).toLocaleString('en-US', { minimumFractionDigits: 2 })
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const diff = computed(() => {
  if (!props.selectedPeriod) return 0
  return props.selectedPeriod.book_balance - props.selectedPeriod.closing_balance
})
</script>

<template>
  <GrowFinanceLayout>
    <div class="px-4 py-4 pb-6">
      <!-- Header -->
      <div class="flex items-center gap-3 mb-5">
        <Link :href="route('growfinance.banking.index')" class="p-2 -ml-2 rounded-lg hover:bg-gray-100">
          <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
        </Link>
        <div class="flex-1">
          <h1 class="text-xl font-bold text-gray-900">Bank Reconciliation</h1>
          <p class="text-sm text-gray-500">Match statement transactions against your books</p>
        </div>
        <button
          @click="showImportModal = true"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
        >
          <DocumentArrowUpIcon class="h-5 w-5" aria-hidden="true" />
          Import Statement
        </button>
      </div>

      <!-- Account Selector -->
      <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1">
            <label class="block text-sm font-medium text-gray-700 mb-1">Bank Account</label>
            <select
              v-model="selectedAccountId"
              @change="handleAccountChange"
              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
              <option v-for="acc in bankAccounts" :key="acc.id" :value="acc.id">
                {{ acc.account_name }} {{ acc.bank_name ? '(' + acc.bank_name + ')' : '' }}
              </option>
            </select>
          </div>
          <div class="w-full sm:w-64">
            <label class="block text-sm font-medium text-gray-700 mb-1">Reconciliation Period</label>
            <select
              v-model="selectedPeriodId"
              @change="handlePeriodChange"
              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
              <option :value="null">Select period...</option>
              <option v-for="p in periods" :key="p.id" :value="p.id">
                {{ formatDate(p.start_date) }} - {{ formatDate(p.end_date) }} ({{ p.status }})
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Balance Summary -->
      <div v-if="selectedPeriod" class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
        <div class="bg-blue-50 rounded-xl p-4">
          <p class="text-xs text-blue-600 font-medium mb-1">Opening Balance</p>
          <p class="text-lg font-bold text-blue-700">{{ formatMoney(selectedPeriod.opening_balance) }}</p>
        </div>
        <div class="bg-green-50 rounded-xl p-4">
          <p class="text-xs text-green-600 font-medium mb-1">Closing Balance</p>
          <p class="text-lg font-bold text-green-700">{{ formatMoney(selectedPeriod.closing_balance) }}</p>
        </div>
        <div class="bg-purple-50 rounded-xl p-4">
          <p class="text-xs text-purple-600 font-medium mb-1">Book Balance</p>
          <p class="text-lg font-bold text-purple-700">{{ formatMoney(selectedPeriod.book_balance) }}</p>
        </div>
        <div :class="['rounded-xl p-4', diff === 0 ? 'bg-emerald-50' : 'bg-red-50']">
          <p class="text-xs font-medium mb-1" :class="diff === 0 ? 'text-emerald-600' : 'text-red-600'">Difference</p>
          <p class="text-lg font-bold" :class="diff === 0 ? 'text-emerald-700' : 'text-red-700'">
            {{ formatMoney(diff) }}
          </p>
          <p v-if="diff === 0" class="text-xs text-emerald-600 mt-1">✓ Reconciled</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Statement Lines -->
        <div>
          <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
              <h3 class="font-semibold text-gray-900">Bank Statement</h3>
              <span v-if="statements.length > 0" class="text-xs text-gray-500">
                {{ statements.length }} statement(s)
              </span>
            </div>

            <div v-if="statements.length === 0" class="p-8 text-center">
              <p class="text-gray-500 text-sm">No statements imported. Click "Import Statement" to begin.</p>
            </div>

            <div v-for="stmt in statements" :key="stmt.id" class="border-b border-gray-50">
              <div class="px-4 py-2 bg-gray-50 text-xs text-gray-600 font-medium">
                {{ stmt.statement_period || formatDate(stmt.start_date) + ' - ' + formatDate(stmt.end_date) }}
                ({{ stmt.lines.length }} transactions)
              </div>
              <div v-for="line in stmt.lines" :key="line.id" class="px-4 py-3 flex items-center gap-3 hover:bg-gray-50">
                <button
                  @click="selectedStatementLineId = line.id; showMatchModal = true"
                  :disabled="line.status === 'matched'"
                  class="flex-1 flex items-center gap-3 min-w-0 text-left"
                >
                  <div :class="['w-2 h-2 rounded-full flex-shrink-0', line.status === 'matched' ? 'bg-green-500' : line.status === 'ignored' ? 'bg-gray-300' : 'bg-yellow-500']" />
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ line.description }}</p>
                    <p class="text-xs text-gray-500">{{ formatDate(line.transaction_date) }} {{ line.reference ? '• ' + line.reference : '' }}</p>
                  </div>
                  <p :class="['text-sm font-semibold whitespace-nowrap', line.credit_amount > 0 ? 'text-emerald-600' : 'text-red-600']">
                    {{ line.credit_amount > 0 ? '+' : '' }}{{ formatMoney(line.credit_amount || -line.debit_amount) }}
                  </p>
                </button>
                <span v-if="line.status === 'matched'" class="text-xs text-green-600 font-medium flex-shrink-0">
                  <CheckCircleIcon class="h-5 w-5" aria-hidden="true" />
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Journal Entries -->
        <div>
          <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100">
              <h3 class="font-semibold text-gray-900">Book Transactions</h3>
            </div>
            <div v-if="transactions.length === 0" class="p-8 text-center">
              <p class="text-gray-500 text-sm">No unreconciled transactions found</p>
            </div>
            <div v-else class="divide-y divide-gray-50">
              <div
                v-for="tx in transactions"
                :key="tx.id"
                class="px-4 py-3 flex items-center gap-3 hover:bg-gray-50 cursor-pointer"
                @click="selectedJournalLineId = tx.id; showMatchModal = true"
              >
                <div :class="['w-2 h-2 rounded-full flex-shrink-0', tx.reconciled ? 'bg-green-500' : 'bg-yellow-500']" />
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate">{{ tx.journalEntry.description }}</p>
                  <p class="text-xs text-gray-500">
                    {{ formatDate(tx.journalEntry.entry_date) }}
                    {{ tx.journalEntry.reference ? '• ' + tx.journalEntry.reference : '' }}
                    • {{ tx.account.name }}
                  </p>
                </div>
                <p :class="['text-sm font-semibold whitespace-nowrap', tx.debit_amount > 0 ? 'text-emerald-600' : 'text-red-600']">
                  {{ tx.debit_amount > 0 ? '+' : '' }}{{ formatMoney(tx.debit_amount || -tx.credit_amount) }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Complete Reconciliation -->
      <div v-if="selectedPeriod && selectedPeriod.status === 'in_progress'" class="mt-6">
        <button
          @click="completeReconciliation"
          class="w-full py-3.5 rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700 flex items-center justify-center gap-2"
        >
          <CheckCircleIcon class="h-5 w-5" aria-hidden="true" />
          Complete Reconciliation
        </button>
      </div>

      <!-- Import Statement Modal -->
      <div v-if="showImportModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showImportModal = false" />
          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
            <form @submit.prevent="submitImport">
              <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Import Bank Statement</h3>
              </div>
              <div class="px-6 py-4 space-y-4 max-h-96 overflow-y-auto">
                <input type="hidden" v-model="importForm.bank_account_id" />
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Period Label</label>
                    <input v-model="importForm.statement_period" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="e.g. June 2026" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statement Period *</label>
                    <input v-model="importForm.statement_period" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="e.g. June 2026" />
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                    <input v-model="importForm.start_date" type="date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date *</label>
                    <input v-model="importForm.end_date" type="date" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Opening Balance *</label>
                    <input v-model.number="importForm.opening_balance" type="number" step="0.01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Closing Balance *</label>
                    <input v-model.number="importForm.closing_balance" type="number" step="0.01" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" />
                  </div>
                </div>

                <div class="border-t border-gray-200 pt-4">
                  <div class="flex items-center justify-between mb-3">
                    <label class="text-sm font-medium text-gray-700">Transactions</label>
                    <button type="button" @click="addLine" class="text-sm text-blue-600 hover:text-blue-700 font-medium">+ Add Line</button>
                  </div>
                  <div v-for="(line, idx) in importForm.lines" :key="idx" class="p-3 bg-gray-50 rounded-lg mb-3">
                    <div class="grid grid-cols-2 gap-2 mb-2">
                      <input v-model="line.transaction_date" type="date" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="Date" />
                      <input v-model="line.description" type="text" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="Description" />
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                      <input v-model="line.reference" type="text" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="Ref" />
                      <input v-model.number="line.debit_amount" type="number" step="0.01" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="Debit" />
                      <div class="flex items-center gap-2">
                        <input v-model.number="line.credit_amount" type="number" step="0.01" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="Credit" />
                        <button type="button" @click="removeLine(idx)" class="text-red-500 hover:text-red-700 flex-shrink-0">
                          <XCircleIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="px-6 py-4 bg-gray-50 flex justify-end gap-3">
                <button type="button" @click="showImportModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg">Cancel</button>
                <button type="submit" :disabled="importForm.processing" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                  {{ importForm.processing ? 'Importing...' : 'Import Statement' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Match Modal -->
      <div v-if="showMatchModal" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showMatchModal = false" />
          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl sm:my-8 sm:align-middle sm:max-w-md sm:w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Match Transaction</h3>
            <p class="text-sm text-gray-600 mb-4">Select a statement line and a book transaction to match them.</p>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Statement Line</label>
                <select v-model="selectedStatementLineId" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                  <option :value="null">Select...</option>
                  <template v-for="stmt in statements" :key="stmt.id">
                    <option v-for="line in stmt.lines" :key="line.id" :value="line.id" :disabled="line.status === 'matched'">
                      {{ formatDate(line.transaction_date) }} - {{ line.description }} ({{ formatMoney(line.credit_amount || -line.debit_amount) }})
                    </option>
                  </template>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Book Transaction</label>
                <select v-model="selectedJournalLineId" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                  <option :value="null">Select...</option>
                  <option v-for="tx in transactions" :key="tx.id" :value="tx.id" :disabled="tx.reconciled">
                    {{ formatDate(tx.journalEntry.entry_date) }} - {{ tx.journalEntry.description }} ({{ formatMoney(tx.debit_amount || -tx.credit_amount) }})
                  </option>
                </select>
              </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
              <button @click="showMatchModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg">Cancel</button>
              <button
                @click="matchTransaction(selectedStatementLineId!, selectedJournalLineId!); showMatchModal = false"
                :disabled="!selectedStatementLineId || !selectedJournalLineId"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
              >
                <LinkIcon class="h-4 w-4 inline mr-1" aria-hidden="true" />
                Match
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </GrowFinanceLayout>
</template>
