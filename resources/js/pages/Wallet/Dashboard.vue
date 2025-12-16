<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import ClientLayout from '@/layouts/ClientLayout.vue';
import { 
    WalletIcon, 
    ArrowUpIcon, 
    ArrowDownIcon, 
    ClockIcon, 
    GiftIcon,
    ShieldCheckIcon, 
    AlertCircleIcon,
    PlusIcon,
    MinusIcon,
    HistoryIcon,
    CheckCircleIcon,
    XCircleIcon
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface Transaction {
    id: string;
    type: string;
    amount: number;
    status: string;
    date: string;
    description: string;
}

interface VerificationLimits {
    daily_withdrawal: number;
    monthly_withdrawal: number;
    single_transaction: number;
}

const props = withDefaults(defineProps<{
    balance?: number;
    bonusBalance?: number;
    totalDeposits?: number;
    totalWithdrawals?: number;
    totalExpenses?: number;
    ventureDividends?: number;
    businessRevenue?: number;
    commissions?: number;
    profitShares?: number;
    recentTransactions?: Transaction[];
    pendingWithdrawals?: number;
    verificationLevel?: string;
    verificationLimits?: VerificationLimits;
    remainingDailyLimit?: number;
    policyAccepted?: boolean;
    accountType?: string;
}>(), {
    balance: 0,
    bonusBalance: 0,
    totalDeposits: 0,
    totalWithdrawals: 0,
    totalExpenses: 0,
    ventureDividends: 0,
    businessRevenue: 0,
    commissions: 0,
    profitShares: 0,
    recentTransactions: () => [],
    pendingWithdrawals: 0,
    verificationLevel: 'basic',
    verificationLimits: () => ({ daily_withdrawal: 1000, monthly_withdrawal: 10000, single_transaction: 500 }),
    remainingDailyLimit: 1000,
    policyAccepted: false,
    accountType: 'client',
});

const showPolicyModal = ref(!props.policyAccepted);

const acceptPolicy = () => {
    router.post(route('wallet.accept-policy'), {}, {
        onSuccess: () => {
            showPolicyModal.value = false;
        }
    });
};

const getVerificationBadgeColor = (level: string) => {
    switch(level) {
        case 'premium': return 'bg-purple-100 text-purple-800 border-purple-200';
        case 'enhanced': return 'bg-blue-100 text-blue-800 border-blue-200';
        default: return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

const getVerificationLabel = (level: string) => {
    switch(level) {
        case 'premium': return 'Premium';
        case 'enhanced': return 'Enhanced';
        default: return 'Basic';
    }
};

const getTransactionIcon = (type: string) => {
    switch(type) {
        case 'deposit':
        case 'credit':
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

const formatCurrency = (amount: number | undefined | null) => {
    const value = amount ?? 0;
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(value);
};

const accountTypeLabel = computed(() => {
    switch(props.accountType) {
        case 'business': return 'Business Account';
        case 'member': return 'Member Account';
        default: return 'Personal Account';
    }
});
</script>

<template>
    <ClientLayout title="My Wallet">
        <Head title="My Wallet" />

        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <div class="flex items-center gap-3 mb-2">
                        <WalletIcon class="h-8 w-8 text-blue-600" aria-hidden="true" />
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">My Wallet</h1>
                    </div>
                    <p class="text-sm text-gray-600">{{ accountTypeLabel }} - Manage your funds securely</p>
                </div>

                <!-- Policy Acceptance Modal -->
                <div v-if="showPolicyModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] flex flex-col">
                        <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-purple-50">
                            <div class="flex items-center gap-3">
                                <div class="bg-blue-600 p-3 rounded-lg">
                                    <ShieldCheckIcon class="h-6 w-6 text-white" aria-hidden="true" />
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900">Wallet Terms & Conditions</h2>
                                    <p class="text-sm text-gray-600">Please review before using your wallet</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex-1 overflow-y-auto p-6 space-y-4">
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                                <h3 class="font-semibold text-gray-900 mb-2 flex items-center gap-2">
                                    <AlertCircleIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                                    Important Notice
                                </h3>
                                <p class="text-sm text-gray-700">
                                    This wallet is a digital account for platform transactions only. 
                                    It is NOT a bank account and does NOT earn interest.
                                </p>
                            </div>

                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <h3 class="font-semibold text-gray-900 mb-2">✅ What You CAN Do:</h3>
                                <ul class="text-sm text-gray-700 space-y-1">
                                    <li>• Store funds for platform purchases</li>
                                    <li>• Top up via mobile money or bank transfer</li>
                                    <li>• Withdraw to your mobile money account</li>
                                    <li>• View transaction history</li>
                                </ul>
                            </div>

                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <h3 class="font-semibold text-gray-900 mb-2">❌ What You CANNOT Do:</h3>
                                <ul class="text-sm text-gray-700 space-y-1">
                                    <li>• Earn interest on balance</li>
                                    <li>• Transfer to other users</li>
                                    <li>• Use as a bank account</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="p-6 border-t border-gray-200 bg-gray-50">
                            <div class="flex flex-col sm:flex-row gap-3">
                                <a
                                    :href="route('wallet.policy')"
                                    target="_blank"
                                    class="sm:flex-none bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition-colors text-center"
                                >
                                    Full Policy
                                </a>
                                <button
                                    @click="acceptPolicy"
                                    class="flex-1 bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-700 transition-colors"
                                >
                                    I Accept - Continue
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Balance Card -->
                <div class="mb-6 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <p class="text-blue-100 text-sm">Available Balance</p>
                                <span 
                                    :class="['text-xs px-2 py-1 rounded-full border', getVerificationBadgeColor(verificationLevel)]"
                                >
                                    {{ getVerificationLabel(verificationLevel) }}
                                </span>
                            </div>
                            <h2 class="text-3xl sm:text-4xl font-bold mt-1">{{ formatCurrency(balance) }}</h2>
                        </div>
                        <WalletIcon class="h-12 w-12 text-blue-200" aria-hidden="true" />
                    </div>
                    
                    <!-- Bonus Balance -->
                    <div v-if="bonusBalance > 0" class="flex items-center gap-2 mb-4 pt-4 border-t border-blue-400">
                        <GiftIcon class="h-5 w-5 text-blue-200" aria-hidden="true" />
                        <div>
                            <p class="text-xs text-blue-100">Bonus Balance</p>
                            <p class="text-sm font-semibold">{{ formatCurrency(bonusBalance) }}</p>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="grid grid-cols-3 gap-3 mt-4">
                        <Link
                            :href="route('wallet.topup')"
                            class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium text-center hover:bg-blue-50 transition-colors text-sm flex items-center justify-center gap-1"
                        >
                            <PlusIcon class="h-4 w-4" aria-hidden="true" />
                            Top Up
                        </Link>
                        <Link
                            :href="route('wallet.withdraw')"
                            class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium text-center hover:bg-blue-50 transition-colors text-sm flex items-center justify-center gap-1"
                        >
                            <MinusIcon class="h-4 w-4" aria-hidden="true" />
                            Withdraw
                        </Link>
                        <Link
                            :href="route('wallet.history')"
                            class="bg-blue-700 text-white px-4 py-2 rounded-lg font-medium text-center hover:bg-blue-800 transition-colors text-sm flex items-center justify-center gap-1"
                        >
                            <HistoryIcon class="h-4 w-4" aria-hidden="true" />
                            History
                        </Link>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <ArrowDownIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Total Deposits</p>
                                <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(totalDeposits) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <ArrowUpIcon class="h-5 w-5 text-red-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Total Withdrawals</p>
                                <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(totalWithdrawals) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-orange-100 rounded-lg">
                                <ClockIcon class="h-5 w-5 text-orange-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Pending</p>
                                <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(pendingWithdrawals) }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm p-4 border border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <GiftIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Bonus Balance</p>
                                <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(bonusBalance) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Income Sources (for members, investors and business accounts) -->
                <div v-if="commissions > 0 || profitShares > 0 || ventureDividends > 0 || businessRevenue > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
                    <!-- MLM Commissions (Members only) -->
                    <div v-if="commissions > 0" class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg shadow-sm p-4 border border-blue-100">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-blue-600 font-medium">Referral Commissions</p>
                                <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(commissions) }}</p>
                                <p class="text-xs text-gray-500">From your network</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Profit Shares (Members only) -->
                    <div v-if="profitShares > 0" class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg shadow-sm p-4 border border-green-100">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-green-600 font-medium">Profit Shares</p>
                                <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(profitShares) }}</p>
                                <p class="text-xs text-gray-500">Quarterly distributions</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Venture Dividends -->
                    <div v-if="ventureDividends > 0" class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg shadow-sm p-4 border border-indigo-100">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-indigo-100 rounded-lg">
                                <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-indigo-600 font-medium">Venture Dividends</p>
                                <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(ventureDividends) }}</p>
                                <p class="text-xs text-gray-500">From your investments</p>
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="businessRevenue > 0" class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-lg shadow-sm p-4 border border-emerald-100">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-emerald-100 rounded-lg">
                                <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-emerald-600 font-medium">Business Revenue</p>
                                <p class="text-lg font-semibold text-gray-900">{{ formatCurrency(businessRevenue) }}</p>
                                <p class="text-xs text-gray-500">From your business</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Withdrawal Limits Info -->
                <div class="bg-white rounded-lg shadow-sm p-4 mb-6 border border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                        <ShieldCheckIcon class="h-4 w-4 text-blue-600" aria-hidden="true" />
                        Withdrawal Limits ({{ getVerificationLabel(verificationLevel) }} Level)
                    </h3>
                    <div class="grid grid-cols-3 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Daily Limit</p>
                            <p class="font-medium text-gray-900">{{ formatCurrency(verificationLimits.daily_withdrawal) }}</p>
                            <p class="text-xs text-green-600">{{ formatCurrency(remainingDailyLimit) }} remaining</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Monthly Limit</p>
                            <p class="font-medium text-gray-900">{{ formatCurrency(verificationLimits.monthly_withdrawal) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Per Transaction</p>
                            <p class="font-medium text-gray-900">{{ formatCurrency(verificationLimits.single_transaction) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">Recent Transactions</h3>
                        <Link 
                            :href="route('wallet.history')"
                            class="text-sm text-blue-600 hover:text-blue-700"
                        >
                            View All
                        </Link>
                    </div>
                    
                    <div v-if="recentTransactions.length === 0" class="p-8 text-center">
                        <WalletIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" aria-hidden="true" />
                        <p class="text-gray-500">No transactions yet</p>
                        <p class="text-sm text-gray-400">Your transaction history will appear here</p>
                    </div>
                    
                    <div v-else class="divide-y divide-gray-100">
                        <div 
                            v-for="transaction in recentTransactions" 
                            :key="transaction.id"
                            class="px-4 py-3 flex items-center justify-between hover:bg-gray-50"
                        >
                            <div class="flex items-center gap-3">
                                <div :class="['p-2 rounded-lg', getTransactionColor(transaction.type)]">
                                    <component 
                                        :is="getTransactionIcon(transaction.type)" 
                                        class="h-4 w-4" 
                                        aria-hidden="true" 
                                    />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ transaction.description }}</p>
                                    <p class="text-xs text-gray-500">{{ transaction.date }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p :class="[
                                    'text-sm font-semibold',
                                    transaction.type === 'deposit' || transaction.type === 'credit' 
                                        ? 'text-green-600' 
                                        : 'text-red-600'
                                ]">
                                    {{ transaction.type === 'deposit' || transaction.type === 'credit' ? '+' : '-' }}{{ formatCurrency(transaction.amount) }}
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
