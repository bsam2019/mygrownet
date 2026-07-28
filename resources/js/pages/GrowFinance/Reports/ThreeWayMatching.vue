<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/GrowFinanceLayout.vue';

const page = usePage();
const results = computed(() => (page.props as any).results || {});
const dateRange = computed(() => (page.props as any).dateRange || { from: '', to: '' });

const summary = computed(() => results.value.summary || {});
const matches = computed(() => results.value.matches || []);
const partialMatches = computed(() => results.value.partial_matches || []);
const unmatchedInvoices = computed(() => results.value.unmatched_invoices || []);
const unmatchedExpenses = computed(() => results.value.unmatched_expenses || []);

const dateFrom = ref(dateRange.value.from);
const dateTo = ref(dateRange.value.to);

const runMatching = () => {
    router.get(route('growfinance.matching.index'), {
        from: dateFrom.value,
        to: dateTo.value,
    }, { preserveState: true });
};

const confirmMatch = (invoiceId: number, expenseId: number) => {
    router.post(route('growfinance.matching.confirm'), {
        invoice_id: invoiceId,
        expense_id: expenseId,
    }, { preserveState: true });
};

const getBadgeClass = (type: string) => {
    switch (type) {
        case 'exact': return 'bg-green-50 text-green-700';
        case 'amount_vendor': return 'bg-blue-50 text-blue-700';
        case 'partial': return 'bg-yellow-50 text-yellow-700';
        default: return 'bg-gray-50 text-gray-600';
    }
};

const getBadgeText = (type: string) => {
    switch (type) {
        case 'exact': return 'Exact';
        case 'amount_vendor': return 'Amount';
        case 'partial': return 'Partial';
        default: return type;
    }
};
</script>

<template>
    <AppLayout>
        <div class="p-4 sm:p-6 max-w-6xl mx-auto">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Three-Way Matching</h1>
                <p class="text-sm text-gray-500 mt-1">Match invoices to expenses (PO → Receipt → Invoice)</p>
            </div>

            <!-- Date Range -->
            <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6 flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">From</label>
                    <input
                        v-model="dateFrom"
                        type="date"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500"
                    />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">To</label>
                    <input
                        v-model="dateTo"
                        type="date"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500"
                    />
                </div>
                <button
                    @click="runMatching"
                    class="px-5 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors"
                >
                    Run Matching
                </button>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl border border-green-200 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Matched</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ summary.matched || 0 }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ results.exact_matches || 0 }} exact / {{ results.amount_matches || 0 }} amount</p>
                </div>
                <div class="bg-white rounded-xl border border-yellow-200 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Partial</p>
                    <p class="text-2xl font-bold text-yellow-600 mt-1">{{ summary.partial || 0 }}</p>
                    <p class="text-xs text-gray-400 mt-1">Requires review</p>
                </div>
                <div class="bg-white rounded-xl border border-red-200 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Unmatched Invoices</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ summary.unmatched_invoices || 0 }}</p>
                    <p class="text-xs text-gray-400 mt-1">of {{ summary.total_invoices || 0 }} total</p>
                </div>
                <div class="bg-white rounded-xl border border-red-200 p-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Unmatched Expenses</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ summary.unmatched_expenses || 0 }}</p>
                    <p class="text-xs text-gray-400 mt-1">of {{ summary.total_expenses || 0 }} total</p>
                </div>
            </div>

            <!-- Matched Table -->
            <div v-if="matches.length > 0" class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Matched ({{ matches.length }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="text-left px-4 py-2.5 font-medium text-gray-600">Invoice</th>
                                <th class="text-left px-4 py-2.5 font-medium text-gray-600">Expense Ref</th>
                                <th class="text-right px-4 py-2.5 font-medium text-gray-600">Amount</th>
                                <th class="text-center px-4 py-2.5 font-medium text-gray-600">Type</th>
                                <th class="text-center px-4 py-2.5 font-medium text-gray-600">Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="m in matches" :key="m.invoice_id + '-' + m.expense_id" class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-4 py-2.5 text-gray-900">{{ m.invoice_number || '#' + m.invoice_id }}</td>
                                <td class="px-4 py-2.5 text-gray-600">{{ m.expense_reference || '#' + m.expense_id }}</td>
                                <td class="px-4 py-2.5 text-right font-medium text-gray-900">K{{ (m.amount || 0).toLocaleString() }}</td>
                                <td class="px-4 py-2.5 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium" :class="getBadgeClass(m.type)">
                                        {{ getBadgeText(m.type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <span class="text-xs font-mono">{{ m.score }}%</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Partial Matches -->
            <div v-if="partialMatches.length > 0" class="bg-white rounded-xl border border-yellow-200 overflow-hidden mb-6">
                <div class="px-4 py-3 border-b border-yellow-100 bg-yellow-50/50">
                    <h3 class="text-sm font-semibold text-yellow-800">Partial Matches ({{ partialMatches.length }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="text-left px-4 py-2.5 font-medium text-gray-600">Invoice</th>
                                <th class="text-left px-4 py-2.5 font-medium text-gray-600">Expense Ref</th>
                                <th class="text-right px-4 py-2.5 font-medium text-gray-600">Inv Amt</th>
                                <th class="text-right px-4 py-2.5 font-medium text-gray-600">Exp Amt</th>
                                <th class="text-right px-4 py-2.5 font-medium text-gray-600">Diff</th>
                                <th class="text-center px-4 py-2.5 font-medium text-gray-600">Score</th>
                                <th class="text-center px-4 py-2.5 font-medium text-gray-600">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="m in partialMatches" :key="'p-' + m.invoice_id + '-' + m.expense_id" class="border-b border-gray-100 hover:bg-yellow-50">
                                <td class="px-4 py-2.5 text-gray-900">{{ m.invoice_number || '#' + m.invoice_id }}</td>
                                <td class="px-4 py-2.5 text-gray-600">{{ m.expense_reference || '#' + m.expense_id }}</td>
                                <td class="px-4 py-2.5 text-right text-gray-900">K{{ (m.amount_invoice || 0).toLocaleString() }}</td>
                                <td class="px-4 py-2.5 text-right text-gray-600">K{{ (m.amount_expense || 0).toLocaleString() }}</td>
                                <td class="px-4 py-2.5 text-right font-medium" :class="m.difference > 0 ? 'text-red-600' : 'text-green-600'">
                                    {{ m.difference > 0 ? '+' : '' }}K{{ (m.difference || 0).toLocaleString() }}
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-50 text-yellow-700">{{ m.score }}%</span>
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <button
                                        @click="confirmMatch(m.invoice_id, m.expense_id)"
                                        class="px-3 py-1.5 text-xs font-medium text-emerald-600 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition-colors"
                                    >
                                        Confirm
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Unmatched Tables -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Unmatched Invoices -->
                <div v-if="unmatchedInvoices.length > 0" class="bg-white rounded-xl border border-red-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-red-100 bg-red-50/50">
                        <h3 class="text-sm font-semibold text-red-800">Unmatched Invoices ({{ unmatchedInvoices.length }})</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="text-left px-4 py-2.5 font-medium text-gray-600">Number</th>
                                    <th class="text-right px-4 py-2.5 font-medium text-gray-600">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="inv in unmatchedInvoices" :key="'ui-' + inv.id" class="border-b border-gray-100 hover:bg-red-50">
                                    <td class="px-4 py-2.5 text-gray-900">{{ inv.number || '#' + inv.id }}</td>
                                    <td class="px-4 py-2.5 text-right text-gray-900">K{{ (inv.amount || 0).toLocaleString() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Unmatched Expenses -->
                <div v-if="unmatchedExpenses.length > 0" class="bg-white rounded-xl border border-red-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-red-100 bg-red-50/50">
                        <h3 class="text-sm font-semibold text-red-800">Unmatched Expenses ({{ unmatchedExpenses.length }})</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="text-left px-4 py-2.5 font-medium text-gray-600">Reference</th>
                                    <th class="text-right px-4 py-2.5 font-medium text-gray-600">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="exp in unmatchedExpenses" :key="'ue-' + exp.id" class="border-b border-gray-100 hover:bg-red-50">
                                    <td class="px-4 py-2.5 text-gray-900">{{ exp.reference || '#' + exp.id }}</td>
                                    <td class="px-4 py-2.5 text-right text-gray-900">K{{ (exp.amount || 0).toLocaleString() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="matches.length === 0 && partialMatches.length === 0 && unmatchedInvoices.length === 0 && unmatchedExpenses.length === 0" class="text-center py-12 text-gray-400">
                <p class="text-sm">No data for the selected date range. Click "Run Matching" to analyze.</p>
            </div>
        </div>
    </AppLayout>
</template>
