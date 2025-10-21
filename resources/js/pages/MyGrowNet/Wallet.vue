<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MemberLayout from '@/Layouts/MemberLayout.vue';
import { BanknoteIcon, ArrowUpIcon, ArrowDownIcon, ClockIcon } from 'lucide-vue-next';

interface Transaction {
    id: number;
    type: string;
    amount: number;
    status: string;
    date: string;
    description: string;
}

const props = withDefaults(defineProps<{
    balance?: number;
    totalEarnings?: number;
    totalWithdrawals?: number;
    recentTransactions?: Transaction[];
    pendingWithdrawals?: number;
    commissionEarnings?: number;
    profitEarnings?: number;
    walletTopups?: number;
    workshopExpenses?: number;
}>(), {
    balance: 0,
    totalEarnings: 0,
    totalWithdrawals: 0,
    recentTransactions: () => [],
    pendingWithdrawals: 0,
    commissionEarnings: 0,
    profitEarnings: 0,
    walletTopups: 0,
    workshopExpenses: 0,
});

const formatCurrency = (amount: number | undefined | null) => {
    const value = amount ?? 0;
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(value);
};
</script>

<template>
    <MemberLayout>
        <Head title="My Wallet" />

        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">My Wallet</h1>
                <p class="mt-2 text-sm text-gray-600">Manage your earnings, deposits, and withdrawals in one place</p>
            </div>

            <!-- Balance Card -->
            <div class="mb-6 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-blue-100 text-sm">Available Balance</p>
                        <h2 class="text-3xl sm:text-4xl font-bold mt-1">{{ formatCurrency(balance) }}</h2>
                    </div>
                    <BanknoteIcon class="h-12 w-12 text-blue-200" />
                </div>
                
                <div class="grid grid-cols-3 gap-3 mt-6">
                    <Link
                        :href="route('mygrownet.payments.create', { type: 'wallet_topup' })"
                        class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium text-center hover:bg-blue-50 transition-colors text-sm"
                    >
                        Top Up
                    </Link>
                    <Link
                        :href="route('withdrawals.create')"
                        class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium text-center hover:bg-blue-50 transition-colors text-sm"
                    >
                        Withdraw
                    </Link>
                    <Link
                        :href="route('transactions')"
                        class="bg-blue-700 text-white px-4 py-2 rounded-lg font-medium text-center hover:bg-blue-800 transition-colors text-sm"
                    >
                        History
                    </Link>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Income</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">{{ formatCurrency(totalEarnings) }}</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-lg">
                            <ArrowUpIcon class="h-6 w-6 text-green-600" />
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Earnings + Deposits</p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Withdrawn</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">{{ formatCurrency(totalWithdrawals) }}</p>
                        </div>
                        <div class="bg-red-100 p-3 rounded-lg">
                            <ArrowDownIcon class="h-6 w-6 text-red-600" />
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Approved withdrawals</p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Pending</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">{{ formatCurrency(pendingWithdrawals) }}</p>
                        </div>
                        <div class="bg-amber-100 p-3 rounded-lg">
                            <ClockIcon class="h-6 w-6 text-amber-600" />
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Awaiting approval</p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Expenses</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">{{ formatCurrency(workshopExpenses) }}</p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-lg">
                            <BanknoteIcon class="h-6 w-6 text-purple-600" />
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Workshop payments</p>
                </div>
            </div>

            <!-- Earnings Breakdown -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Income Breakdown</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-600">Referral Commissions</span>
                                <span class="text-sm font-semibold text-gray-900">{{ formatCurrency(commissionEarnings) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div 
                                    class="bg-blue-600 h-2 rounded-full" 
                                    :style="{ width: totalEarnings > 0 ? `${(commissionEarnings / totalEarnings) * 100}%` : '0%' }"
                                ></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-600">Profit Shares</span>
                                <span class="text-sm font-semibold text-gray-900">{{ formatCurrency(profitEarnings) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div 
                                    class="bg-green-600 h-2 rounded-full" 
                                    :style="{ width: totalEarnings > 0 ? `${(profitEarnings / totalEarnings) * 100}%` : '0%' }"
                                ></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-600">Wallet Top-ups</span>
                                <span class="text-sm font-semibold text-gray-900">{{ formatCurrency(walletTopups) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div 
                                    class="bg-purple-600 h-2 rounded-full" 
                                    :style="{ width: totalEarnings > 0 ? `${(walletTopups / totalEarnings) * 100}%` : '0%' }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Transactions</h3>
                </div>
                <div class="divide-y divide-gray-200">
                    <div
                        v-for="transaction in recentTransactions"
                        :key="transaction.id"
                        class="px-6 py-4 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ transaction.description }}</p>
                                <p class="text-sm text-gray-500 mt-1">{{ transaction.date }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-green-600">+{{ formatCurrency(transaction.amount) }}</p>
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium mt-1"
                                    :class="{
                                        'bg-green-100 text-green-800': transaction.status === 'paid' || transaction.status === 'verified',
                                        'bg-yellow-100 text-yellow-800': transaction.status === 'pending',
                                        'bg-gray-100 text-gray-800': transaction.status === 'processing',
                                    }"
                                >
                                    {{ transaction.status === 'verified' ? 'Completed' : transaction.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="recentTransactions.length === 0" class="px-6 py-8 text-center text-gray-500">
                        No transactions yet
                    </div>
                </div>
                
                <div v-if="recentTransactions.length > 0" class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <Link
                        :href="route('transactions')"
                        class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                    >
                        View all transactions →
                    </Link>
                </div>
            </div>
        </div>
    </div>
    </MemberLayout>
</template>
