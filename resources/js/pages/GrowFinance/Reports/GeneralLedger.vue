<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <!-- Header -->
            <div class="flex items-center gap-3 mb-5">
                <Link :href="route('growfinance.reports.profit-loss')" class="p-2 -ml-2 rounded-lg hover:bg-gray-100">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">General Ledger</h1>
                    <p class="text-sm text-gray-500">Account transaction history</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm p-4 mb-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Account</label>
                    <select 
                        v-model="selectedAccountId"
                        @change="handleFilterChange"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Choose an account</option>
                        <option v-for="account in accounts" :key="account.id" :value="account.id">
                            {{ account.code }} - {{ account.name }}
                        </option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
                        <input 
                            v-model="filterStartDate"
                            type="date"
                            @change="handleFilterChange"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                        <input 
                            v-model="filterEndDate"
                            type="date"
                            @change="handleFilterChange"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                        />
                    </div>
                </div>
            </div>

            <!-- Account Summary -->
            <div v-if="selectedAccount" class="bg-blue-50 rounded-xl p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-blue-900">{{ selectedAccount.name }}</p>
                        <p class="text-xs text-blue-600">{{ selectedAccount.code }} • {{ selectedAccount.type }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-blue-600">Current Balance</p>
                        <p class="font-bold text-blue-900">{{ formatMoney(selectedAccount.current_balance) }}</p>
                    </div>
                </div>
            </div>

            <!-- Ledger Entries -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Transactions</h3>
                </div>

                <div v-if="!selectedAccount" class="p-8 text-center">
                    <DocumentTextIcon class="h-10 w-10 text-gray-300 mx-auto mb-2" aria-hidden="true" />
                    <p class="text-gray-500 text-sm">Select an account to view transactions</p>
                </div>

                <div v-else-if="ledgerEntries.length === 0" class="p-8 text-center">
                    <DocumentTextIcon class="h-10 w-10 text-gray-300 mx-auto mb-2" aria-hidden="true" />
                    <p class="text-gray-500 text-sm">No transactions in this period</p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Date</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Description</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600">Debit</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600">Credit</th>
                                <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="entry in ledgerEntries" :key="entry.id + '-' + entry.date">
                                <td class="px-3 py-2 whitespace-nowrap text-gray-600">
                                    {{ formatShortDate(entry.date) }}
                                </td>
                                <td class="px-3 py-2">
                                    <p class="font-medium text-gray-900 truncate max-w-[150px]">{{ entry.description }}</p>
                                    <p class="text-xs text-gray-500">{{ entry.entry_number }}</p>
                                </td>
                                <td class="px-3 py-2 text-right whitespace-nowrap">
                                    <span v-if="entry.debit > 0" class="text-gray-900">{{ formatMoney(entry.debit) }}</span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="px-3 py-2 text-right whitespace-nowrap">
                                    <span v-if="entry.credit > 0" class="text-gray-900">{{ formatMoney(entry.credit) }}</span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="px-3 py-2 text-right whitespace-nowrap font-medium">
                                    <span :class="entry.balance >= 0 ? 'text-gray-900' : 'text-red-600'">
                                        {{ formatMoney(entry.balance) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Report Navigation -->
            <div class="mt-6 grid grid-cols-2 gap-3">
                <Link 
                    :href="route('growfinance.reports.trial-balance')"
                    class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow"
                >
                    <p class="font-medium text-gray-900 text-sm">Trial Balance</p>
                    <p class="text-xs text-gray-500">Account summary</p>
                </Link>
                <Link 
                    :href="route('growfinance.reports.balance-sheet')"
                    class="bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow"
                >
                    <p class="font-medium text-gray-900 text-sm">Balance Sheet</p>
                    <p class="text-xs text-gray-500">Financial position</p>
                </Link>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { ArrowLeftIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';

interface Account {
    id: number;
    code: string;
    name: string;
    type: string;
    current_balance: number;
}

interface LedgerEntry {
    id: number;
    date: string;
    entry_number: string;
    description: string;
    reference: string | null;
    debit: number;
    credit: number;
    balance: number;
}

const props = defineProps<{
    accounts: Account[];
    selectedAccount: Account | null;
    ledgerEntries: LedgerEntry[];
    startDate: string;
    endDate: string;
}>();

const selectedAccountId = ref(props.selectedAccount?.id || '');
const filterStartDate = ref(props.startDate);
const filterEndDate = ref(props.endDate);

const formatMoney = (amount: number) => {
    return 'K' + Math.abs(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatShortDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const handleFilterChange = () => {
    if (!selectedAccountId.value) return;
    
    router.get(route('growfinance.reports.general-ledger'), {
        account_id: selectedAccountId.value,
        start_date: filterStartDate.value,
        end_date: filterEndDate.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>
