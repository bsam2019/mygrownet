<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <!-- Header -->
            <div class="flex items-center gap-3 mb-5">
                <Link :href="route('growfinance.banking.index')" class="p-2 -ml-2 rounded-lg hover:bg-gray-100">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Bank Reconciliation</h1>
                    <p class="text-sm text-gray-500">Match transactions with bank statement</p>
                </div>
            </div>

            <!-- Account Selector -->
            <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Account</label>
                <select 
                    v-model="selectedAccountId"
                    @change="handleAccountChange"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option v-for="account in cashAccounts" :key="account.id" :value="account.id">
                        {{ account.name }} - {{ formatMoney(account.current_balance) }}
                    </option>
                </select>
            </div>

            <!-- Balance Summary -->
            <div v-if="selectedAccount" class="grid grid-cols-2 gap-3 mb-6">
                <div class="bg-blue-50 rounded-xl p-4">
                    <p class="text-xs text-blue-600 font-medium mb-1">Book Balance</p>
                    <p class="text-lg font-bold text-blue-700">{{ formatMoney(selectedAccount.current_balance) }}</p>
                </div>
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-xs text-gray-600 font-medium mb-1">Statement Balance</p>
                    <input 
                        v-model.number="statementBalance"
                        type="number"
                        step="0.01"
                        class="w-full text-lg font-bold text-gray-700 bg-transparent border-none p-0 focus:ring-0"
                        placeholder="Enter balance"
                    />
                </div>
            </div>

            <!-- Difference -->
            <div v-if="statementBalance !== null" class="bg-white rounded-xl shadow-sm p-4 mb-6">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Difference</span>
                    <span :class="['font-bold', difference === 0 ? 'text-emerald-600' : 'text-red-600']">
                        {{ formatMoney(difference) }}
                    </span>
                </div>
                <p v-if="difference === 0" class="text-xs text-emerald-600 mt-1">
                    ✓ Account is reconciled
                </p>
                <p v-else class="text-xs text-gray-500 mt-1">
                    Review transactions below to find the difference
                </p>
            </div>

            <!-- Transactions -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Transactions</h3>
                </div>
                <ul v-if="transactions.length > 0" class="divide-y divide-gray-50">
                    <li 
                        v-for="tx in transactions" 
                        :key="tx.id" 
                        class="px-4 py-3 flex items-center gap-3"
                    >
                        <input 
                            type="checkbox"
                            v-model="reconciledIds"
                            :value="tx.id"
                            class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 truncate">{{ tx.description }}</p>
                            <p class="text-xs text-gray-500">{{ formatDate(tx.date) }} • {{ tx.reference || 'No ref' }}</p>
                        </div>
                        <p :class="['font-semibold text-sm whitespace-nowrap', tx.amount >= 0 ? 'text-emerald-600' : 'text-red-600']">
                            {{ tx.amount >= 0 ? '+' : '' }}{{ formatMoney(tx.amount) }}
                        </p>
                    </li>
                </ul>
                <div v-else class="p-8 text-center">
                    <p class="text-gray-500 text-sm">No transactions to reconcile</p>
                </div>
            </div>

            <!-- Save Button -->
            <div class="mt-6">
                <button 
                    @click="saveReconciliation"
                    :disabled="!selectedAccount || statementBalance === null"
                    class="w-full py-3.5 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed"
                >
                    Complete Reconciliation
                </button>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Account {
    id: number;
    code: string;
    name: string;
    current_balance: number;
}

interface Transaction {
    id: number;
    date: string;
    description: string;
    reference: string | null;
    debit: number;
    credit: number;
    amount: number;
    reconciled: boolean;
}

const props = defineProps<{
    cashAccounts: Account[];
    selectedAccount: Account | null;
    transactions: Transaction[];
}>();

const selectedAccountId = ref(props.selectedAccount?.id || props.cashAccounts[0]?.id);
const statementBalance = ref<number | null>(props.selectedAccount?.current_balance || null);
const reconciledIds = ref<number[]>([]);

const difference = computed(() => {
    if (statementBalance.value === null || !props.selectedAccount) return 0;
    return props.selectedAccount.current_balance - statementBalance.value;
});

const formatMoney = (amount: number) => {
    return 'K' + Math.abs(amount).toLocaleString('en-US', { minimumFractionDigits: 2 });
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const handleAccountChange = () => {
    router.get(route('growfinance.banking.reconcile'), { account_id: selectedAccountId.value }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const saveReconciliation = () => {
    router.post(route('growfinance.banking.reconcile.store'), {
        account_id: selectedAccountId.value,
        statement_balance: statementBalance.value,
        reconciled_transactions: reconciledIds.value,
    });
};
</script>
