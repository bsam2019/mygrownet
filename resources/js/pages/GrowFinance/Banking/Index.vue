<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Banking</h1>
                    <p class="text-sm text-gray-500">Manage cash & bank accounts</p>
                </div>
                <Link 
                    :href="route('growfinance.banking.reconcile')"
                    class="px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700"
                >
                    Reconcile
                </Link>
            </div>

            <!-- Total Cash Card -->
            <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-xl p-4 text-white shadow-md mb-6">
                <p class="text-sm text-white/80 mb-1">Total Cash Balance</p>
                <p class="text-3xl font-bold">{{ formatMoney(totalCash) }}</p>
            </div>

            <!-- Cash Accounts -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Cash & Bank Accounts</h3>
                </div>
                <ul v-if="cashAccounts.length > 0" class="divide-y divide-gray-50">
                    <li v-for="account in cashAccounts" :key="account.id" class="px-4 py-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ account.name }}</p>
                                <p class="text-xs text-gray-500">{{ account.code }} • {{ account.category }}</p>
                            </div>
                            <p :class="['font-semibold', account.current_balance >= 0 ? 'text-emerald-600' : 'text-red-600']">
                                {{ formatMoney(account.current_balance) }}
                            </p>
                        </div>
                    </li>
                </ul>
                <div v-else class="p-8 text-center">
                    <p class="text-gray-500 text-sm">No cash accounts found</p>
                    <Link :href="route('growfinance.setup.index')" class="text-blue-600 text-sm font-medium mt-2 inline-block">
                        Set up accounts
                    </Link>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-3 gap-3 mb-6">
                <button 
                    @click="showDepositModal = true"
                    class="bg-emerald-50 rounded-xl p-4 text-center hover:bg-emerald-100 transition-colors"
                >
                    <ArrowDownTrayIcon class="h-6 w-6 text-emerald-600 mx-auto mb-2" aria-hidden="true" />
                    <span class="text-xs font-medium text-emerald-700">Deposit</span>
                </button>
                <button 
                    @click="showWithdrawalModal = true"
                    class="bg-red-50 rounded-xl p-4 text-center hover:bg-red-100 transition-colors"
                >
                    <ArrowUpTrayIcon class="h-6 w-6 text-red-600 mx-auto mb-2" aria-hidden="true" />
                    <span class="text-xs font-medium text-red-700">Withdraw</span>
                </button>
                <button 
                    @click="showTransferModal = true"
                    class="bg-blue-50 rounded-xl p-4 text-center hover:bg-blue-100 transition-colors"
                >
                    <ArrowsRightLeftIcon class="h-6 w-6 text-blue-600 mx-auto mb-2" aria-hidden="true" />
                    <span class="text-xs font-medium text-blue-700">Transfer</span>
                </button>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900">Recent Transactions</h3>
                </div>
                <ul v-if="recentTransactions.length > 0" class="divide-y divide-gray-50">
                    <li v-for="tx in recentTransactions" :key="tx.id" class="px-4 py-3">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 truncate">{{ tx.description }}</p>
                                <p class="text-xs text-gray-500">{{ formatDate(tx.date) }} • {{ tx.account }}</p>
                            </div>
                            <p :class="['font-semibold text-sm whitespace-nowrap ml-3', tx.amount >= 0 ? 'text-emerald-600' : 'text-red-600']">
                                {{ tx.amount >= 0 ? '+' : '' }}{{ formatMoney(tx.amount) }}
                            </p>
                        </div>
                    </li>
                </ul>
                <div v-else class="p-8 text-center">
                    <BanknotesIcon class="h-10 w-10 text-gray-300 mx-auto mb-2" aria-hidden="true" />
                    <p class="text-gray-500 text-sm">No transactions yet</p>
                </div>
            </div>
        </div>

        <!-- Deposit Modal -->
        <TransactionModal
            v-if="showDepositModal"
            title="Record Deposit"
            type="deposit"
            :accounts="cashAccounts"
            @close="showDepositModal = false"
            @submit="handleDeposit"
        />

        <!-- Withdrawal Modal -->
        <TransactionModal
            v-if="showWithdrawalModal"
            title="Record Withdrawal"
            type="withdrawal"
            :accounts="cashAccounts"
            @close="showWithdrawalModal = false"
            @submit="handleWithdrawal"
        />

        <!-- Transfer Modal -->
        <TransferModal
            v-if="showTransferModal"
            :accounts="cashAccounts"
            @close="showTransferModal = false"
            @submit="handleTransfer"
        />
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import TransactionModal from './TransactionModal.vue';
import TransferModal from './TransferModal.vue';
import {
    ArrowDownTrayIcon,
    ArrowUpTrayIcon,
    ArrowsRightLeftIcon,
    BanknotesIcon,
} from '@heroicons/vue/24/outline';

interface Account {
    id: number;
    code: string;
    name: string;
    category: string;
    current_balance: number;
}

interface Transaction {
    id: number;
    date: string;
    description: string;
    reference: string | null;
    account: string;
    debit: number;
    credit: number;
    amount: number;
}

const props = defineProps<{
    cashAccounts: Account[];
    recentTransactions: Transaction[];
    totalCash: number;
}>();

const showDepositModal = ref(false);
const showWithdrawalModal = ref(false);
const showTransferModal = ref(false);

const formatMoney = (amount: number) => {
    return 'K' + Math.abs(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const handleDeposit = (data: any) => {
    router.post(route('growfinance.banking.deposit'), data, {
        onSuccess: () => { showDepositModal.value = false; },
    });
};

const handleWithdrawal = (data: any) => {
    router.post(route('growfinance.banking.withdrawal'), data, {
        onSuccess: () => { showWithdrawalModal.value = false; },
    });
};

const handleTransfer = (data: any) => {
    router.post(route('growfinance.banking.transfer'), data, {
        onSuccess: () => { showTransferModal.value = false; },
    });
};
</script>
