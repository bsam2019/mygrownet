<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import InvestorLayout from '@/Layouts/InvestorLayout.vue';
import { BanknoteIcon, ArrowUpIcon, ArrowDownIcon, ClockIcon } from 'lucide-vue-next';

interface Transaction {
    id: number;
    type: string;
    amount: number;
    status: string;
    date: string;
    description: string;
}

const props = defineProps<{
    balance: number;
    totalEarnings: number;
    totalWithdrawals: number;
    recentTransactions: Transaction[];
    pendingWithdrawals: number;
}>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(amount);
};
</script>

<template>
    <InvestorLayout>
        <Head title="MyGrow Save - Digital Wallet" />

        <div class="py-6 sm:py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">MyGrow Save</h1>
                <p class="mt-2 text-sm text-gray-600">Your digital wallet for managing earnings and funds</p>
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
                
                <div class="flex gap-4 mt-6">
                    <Link
                        :href="route('withdrawals.create')"
                        class="flex-1 bg-white text-blue-600 px-4 py-2 rounded-lg font-medium text-center hover:bg-blue-50 transition-colors"
                    >
                        Withdraw Funds
                    </Link>
                    <Link
                        :href="route('transactions')"
                        class="flex-1 bg-blue-700 text-white px-4 py-2 rounded-lg font-medium text-center hover:bg-blue-800 transition-colors"
                    >
                        View History
                    </Link>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Earnings</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">{{ formatCurrency(totalEarnings) }}</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-lg">
                            <ArrowUpIcon class="h-6 w-6 text-green-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Withdrawals</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">{{ formatCurrency(totalWithdrawals) }}</p>
                        </div>
                        <div class="bg-red-100 p-3 rounded-lg">
                            <ArrowDownIcon class="h-6 w-6 text-red-600" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Pending Withdrawals</p>
                            <p class="text-xl font-bold text-gray-900 mt-1">{{ formatCurrency(pendingWithdrawals) }}</p>
                        </div>
                        <div class="bg-amber-100 p-3 rounded-lg">
                            <ClockIcon class="h-6 w-6 text-amber-600" />
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
                                        'bg-green-100 text-green-800': transaction.status === 'paid',
                                        'bg-yellow-100 text-yellow-800': transaction.status === 'pending',
                                        'bg-gray-100 text-gray-800': transaction.status === 'processing',
                                    }"
                                >
                                    {{ transaction.status }}
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
    </InvestorLayout>
</template>
