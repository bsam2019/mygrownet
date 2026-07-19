<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { BookOpenIcon, ArrowLeftIcon, CalendarIcon } from '@heroicons/vue/24/outline';
import BMSLayout from '@/Layouts/BMSLayout.vue';

defineOptions({
  layout: BMSLayout
})

interface Props {
    generalLedger: any;
    growFinanceEnabled: boolean;
    startDate: string;
    endDate: string;
}

const props = defineProps<Props>();

const selectedAccountId = ref(props.generalLedger?.selected_account?.id || null);
const startDate = ref(props.startDate);
const endDate = ref(props.endDate);

const formatCurrency = (amount: number) => {
    return `K${amount.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const applyFilter = () => {
    const params: any = {
        start_date: startDate.value,
        end_date: endDate.value
    };
    
    if (selectedAccountId.value) {
        params.account_id = selectedAccountId.value;
    }
    
    window.location.href = route('bms.reports.general-ledger', params);
};

const totalDebits = computed(() => {
    if (!props.generalLedger?.entries) return 0;
    return props.generalLedger.entries.reduce((sum: number, entry: any) => sum + entry.debit, 0);
});

const totalCredits = computed(() => {
    if (!props.generalLedger?.entries) return 0;
    return props.generalLedger.entries.reduce((sum: number, entry: any) => sum + entry.credit, 0);
});
</script>

<template>
    <Head title="General Ledger - CMS Reports" />

    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <Link :href="route('bms.reports.index')" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4">
                <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                Back to Reports
            </Link>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-emerald-100 rounded-lg">
                        <BookOpenIcon class="h-6 w-6 text-emerald-600" aria-hidden="true" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">General Ledger</h1>
                        <p class="mt-1 text-sm text-gray-600">Detailed Account Transactions</p>
                    </div>
                </div>
                <div v-if="growFinanceEnabled" class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 border border-emerald-200 rounded-lg">
                    <div class="h-2 w-2 bg-emerald-500 rounded-full animate-pulse"></div>
                    <span class="text-sm font-medium text-emerald-700">GrowFinance Powered</span>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6 rounded-lg bg-white p-4 shadow">
            <div class="flex flex-col gap-4">
                <!-- Account Selector -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Account</label>
                    <select
                        v-model="selectedAccountId"
                        class="block w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                    >
                        <option :value="null">-- Select an account --</option>
                        <option v-for="account in generalLedger?.accounts" :key="account.id" :value="account.id">
                            {{ account.code }} - {{ account.name }} ({{ account.type }})
                        </option>
                    </select>
                </div>

                <!-- Date Range -->
                <div class="flex items-end gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <CalendarIcon class="h-4 w-4 inline mr-1" aria-hidden="true" />
                            Start Date
                        </label>
                        <input
                            v-model="startDate"
                            type="date"
                            class="block w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                        />
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <CalendarIcon class="h-4 w-4 inline mr-1" aria-hidden="true" />
                            End Date
                        </label>
                        <input
                            v-model="endDate"
                            type="date"
                            class="block w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                        />
                    </div>
                    <button
                        @click="applyFilter"
                        class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 transition"
                    >
                        Apply
                    </button>
                </div>
            </div>
        </div>

        <!-- GrowFinance Disabled Warning -->
        <div v-if="!growFinanceEnabled" class="mb-6 rounded-lg bg-amber-50 border border-amber-200 p-4">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-amber-800">GrowFinance Module Not Enabled</h3>
                    <p class="mt-1 text-sm text-amber-700">
                        Enable the GrowFinance (Full Accounting) module in Settings → Modules to view general ledger.
                    </p>
                    <Link :href="route('bms.settings.index')" class="mt-2 inline-flex items-center text-sm font-medium text-amber-800 hover:text-amber-900">
                        Go to Settings →
                    </Link>
                </div>
            </div>
        </div>

        <!-- General Ledger Content -->
        <div v-if="growFinanceEnabled && generalLedger" class="space-y-6">
            <!-- Selected Account Info -->
            <div v-if="generalLedger.selected_account" class="rounded-lg bg-white p-6 shadow">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                    <div>
                        <div class="text-sm font-medium text-gray-600">Account Code</div>
                        <div class="mt-1 text-lg font-semibold text-gray-900">{{ generalLedger.selected_account.code }}</div>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-600">Account Name</div>
                        <div class="mt-1 text-lg font-semibold text-gray-900">{{ generalLedger.selected_account.name }}</div>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-600">Opening Balance</div>
                        <div class="mt-1 text-lg font-semibold text-blue-600">
                            {{ formatCurrency(generalLedger.selected_account.opening_balance) }}
                        </div>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-600">Current Balance</div>
                        <div class="mt-1 text-lg font-semibold text-emerald-600">
                            {{ formatCurrency(generalLedger.selected_account.current_balance) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions Table -->
            <div v-if="generalLedger.selected_account" class="rounded-lg bg-white shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-emerald-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-emerald-900">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-emerald-900">Entry #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-emerald-900">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-emerald-900">Reference</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-emerald-900">Debit</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-emerald-900">Credit</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-emerald-900">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="entry in generalLedger.entries" :key="entry.id" class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ formatDate(entry.date) }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ entry.entry_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ entry.description }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ entry.reference || '-' }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium text-blue-600">
                                    {{ entry.debit > 0 ? formatCurrency(entry.debit) : '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium text-red-600">
                                    {{ entry.credit > 0 ? formatCurrency(entry.credit) : '-' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-gray-900">
                                    {{ formatCurrency(entry.balance) }}
                                </td>
                            </tr>
                            <tr v-if="generalLedger.entries.length === 0">
                                <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-600">
                                    No transactions found for this account in the selected period
                                </td>
                            </tr>
                            <!-- Totals Row -->
                            <tr v-if="generalLedger.entries.length > 0" class="bg-gray-50 font-semibold">
                                <td colspan="4" class="px-6 py-4 text-sm text-gray-900">Total</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-blue-600">
                                    {{ formatCurrency(totalDebits) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-red-600">
                                    {{ formatCurrency(totalCredits) }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                                    {{ formatCurrency(generalLedger.selected_account.current_balance) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- No Account Selected -->
            <div v-if="!generalLedger.selected_account" class="rounded-lg bg-white p-12 shadow text-center">
                <BookOpenIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                <h3 class="mt-4 text-lg font-medium text-gray-900">Select an Account</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Choose an account from the dropdown above to view its general ledger entries.
                </p>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!growFinanceEnabled" class="rounded-lg bg-white p-12 shadow text-center">
            <BookOpenIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
            <h3 class="mt-4 text-lg font-medium text-gray-900">No General Ledger Data</h3>
            <p class="mt-2 text-sm text-gray-600">
                Enable GrowFinance module to view general ledger.
            </p>
        </div>
    </div>
</template>
