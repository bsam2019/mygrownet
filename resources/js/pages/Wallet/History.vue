<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { 
    WalletIcon, 
    ArrowLeftIcon,
    ArrowUpIcon,
    ArrowDownIcon,
    ClockIcon,
    FunnelIcon,
    MagnifyingGlassIcon,
    CalendarIcon
} from '@heroicons/vue/24/outline';
import { ref, computed } from 'vue';

interface Transaction {
    id: string;
    type: string;
    amount: number;
    status: string;
    date: string;
    description: string;
    reference?: string;
}

interface Props {
    transactions?: Transaction[];
    balance?: number;
    totalDeposits?: number;
    totalWithdrawals?: number;
    filters?: {
        type?: string;
        status?: string;
        dateFrom?: string;
        dateTo?: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    transactions: () => [],
    balance: 0,
    totalDeposits: 0,
    totalWithdrawals: 0,
    filters: () => ({}),
});

const searchQuery = ref('');
const selectedType = ref(props.filters?.type || 'all');
const selectedStatus = ref(props.filters?.status || 'all');
const showFilters = ref(false);

const filteredTransactions = computed(() => {
    let result = props.transactions;
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(t => 
            t.description.toLowerCase().includes(query) ||
            t.reference?.toLowerCase().includes(query)
        );
    }
    
    if (selectedType.value !== 'all') {
        result = result.filter(t => t.type === selectedType.value);
    }
    
    if (selectedStatus.value !== 'all') {
        result = result.filter(t => t.status === selectedStatus.value);
    }
    
    return result;
});

const getTransactionIcon = (type: string) => {
    switch(type) {
        case 'deposit':
        case 'credit':
        case 'topup':
            return ArrowDownIcon;
        case 'withdrawal':
        case 'debit':
            return ArrowUpIcon;
        default:
            return ClockIcon;
    }
};

const getTransactionColor = (type: string) => {
    switch(type) {
        case 'deposit':
        case 'credit':
        case 'topup':
            return 'text-green-600 bg-green-100';
        case 'withdrawal':
        case 'debit':
            return 'text-red-600 bg-red-100';
        default:
            return 'text-gray-600 bg-gray-100';
    }
};

const getStatusBadge = (status: string) => {
    switch(status) {
        case 'completed':
        case 'approved':
        case 'verified':
            return { class: 'bg-green-100 text-green-800', label: 'Completed' };
        case 'pending':
            return { class: 'bg-yellow-100 text-yellow-800', label: 'Pending' };
        case 'rejected':
        case 'failed':
            return { class: 'bg-red-100 text-red-800', label: 'Failed' };
        default:
            return { class: 'bg-gray-100 text-gray-800', label: status };
    }
};

const isCredit = (type: string) => {
    return ['deposit', 'credit', 'topup', 'refund'].includes(type);
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(amount);
};
</script>

<template>
    <ClientLayout title="Transaction History">
        <Head title="Transaction History" />

        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link 
                        :href="route('wallet.index')"
                        class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-4"
                    >
                        <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                        Back to Wallet
                    </Link>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <WalletIcon class="h-8 w-8 text-blue-600" aria-hidden="true" />
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">Transaction History</h1>
                                <p class="text-sm text-gray-600">View all your wallet transactions</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
                        <p class="text-xs text-gray-500 mb-1">Current Balance</p>
                        <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(balance) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
                        <p class="text-xs text-gray-500 mb-1">Total Deposits</p>
                        <p class="text-lg font-semibold text-green-600">{{ formatCurrency(totalDeposits) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
                        <p class="text-xs text-gray-500 mb-1">Total Withdrawals</p>
                        <p class="text-lg font-semibold text-red-600">{{ formatCurrency(totalWithdrawals) }}</p>
                    </div>
                </div>

                <!-- Search & Filters -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-6">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <!-- Search -->
                        <div class="flex-1 relative">
                            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search transactions..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>
                        
                        <!-- Filter Toggle -->
                        <button
                            @click="showFilters = !showFilters"
                            :class="[
                                'inline-flex items-center gap-2 px-4 py-2 rounded-lg border transition-colors',
                                showFilters ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'
                            ]"
                        >
                            <FunnelIcon class="h-5 w-5" aria-hidden="true" />
                            Filters
                        </button>
                    </div>
                    
                    <!-- Filter Options -->
                    <div v-if="showFilters" class="mt-4 pt-4 border-t border-gray-200 grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Type</label>
                            <select
                                v-model="selectedType"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="all">All Types</option>
                                <option value="deposit">Deposits</option>
                                <option value="withdrawal">Withdrawals</option>
                                <option value="credit">Credits</option>
                                <option value="debit">Debits</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                            <select
                                v-model="selectedStatus"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="all">All Status</option>
                                <option value="completed">Completed</option>
                                <option value="pending">Pending</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Transactions List -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">
                            {{ filteredTransactions.length }} Transaction{{ filteredTransactions.length !== 1 ? 's' : '' }}
                        </h3>
                    </div>
                    
                    <div v-if="filteredTransactions.length === 0" class="p-8 text-center">
                        <WalletIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" aria-hidden="true" />
                        <p class="text-gray-500">No transactions found</p>
                        <p class="text-sm text-gray-400">Try adjusting your filters</p>
                    </div>
                    
                    <div v-else class="divide-y divide-gray-100">
                        <div 
                            v-for="transaction in filteredTransactions" 
                            :key="transaction.id"
                            class="px-4 py-4 flex items-center justify-between hover:bg-gray-50"
                        >
                            <div class="flex items-center gap-4">
                                <div :class="['p-2 rounded-lg', getTransactionColor(transaction.type)]">
                                    <component 
                                        :is="getTransactionIcon(transaction.type)" 
                                        class="h-5 w-5" 
                                        aria-hidden="true" 
                                    />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ transaction.description }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <p class="text-xs text-gray-500">{{ transaction.date }}</p>
                                        <span v-if="transaction.reference" class="text-xs text-gray-400">
                                            • {{ transaction.reference }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <p :class="[
                                    'text-sm font-semibold',
                                    isCredit(transaction.type) ? 'text-green-600' : 'text-red-600'
                                ]">
                                    {{ isCredit(transaction.type) ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
                                </p>
                                <span :class="['text-xs px-2 py-0.5 rounded-full', getStatusBadge(transaction.status).class]">
                                    {{ getStatusBadge(transaction.status).label }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>
