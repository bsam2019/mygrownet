<script setup lang="ts">
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeftIcon,
    PencilIcon,
    BanknotesIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
} from '@heroicons/vue/24/outline';

interface JournalLine {
    id: number;
    debit_amount: number;
    credit_amount: number;
    description: string;
    journal_entry: {
        id: number;
        entry_number: string;
        entry_date: string;
        description: string;
    };
}

interface Account {
    id: number;
    code: string;
    name: string;
    type: string;
    subtype: string | null;
    description: string | null;
    balance: number;
    is_active: boolean;
    is_system: boolean;
    journal_lines?: JournalLine[];
}

defineProps<{
    account: Account;
    recentTransactions?: JournalLine[];
}>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 2,
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-ZM', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const getTypeColor = (type: string) => {
    const colors: Record<string, string> = {
        asset: 'bg-blue-100 text-blue-800',
        liability: 'bg-red-100 text-red-800',
        equity: 'bg-purple-100 text-purple-800',
        revenue: 'bg-green-100 text-green-800',
        expense: 'bg-orange-100 text-orange-800',
    };
    return colors[type] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <GrowFinanceLayout>
        <Head :title="`Account: ${account.name}`" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('growfinance.accounts.index')"
                        class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold text-gray-900">{{ account.code }}</h1>
                            <span :class="[getTypeColor(account.type), 'px-3 py-1 rounded-full text-sm font-medium capitalize']">
                                {{ account.type }}
                            </span>
                        </div>
                        <p class="text-gray-500">{{ account.name }}</p>
                    </div>
                </div>
                <Link
                    v-if="!account.is_system"
                    :href="route('growfinance.accounts.edit', account.id)"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    <PencilIcon class="h-4 w-4" aria-hidden="true" />
                    Edit
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Account Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Balance Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Current Balance</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1">{{ formatCurrency(account.balance) }}</p>
                            </div>
                            <div class="p-4 bg-blue-50 rounded-xl">
                                <BanknotesIcon class="h-8 w-8 text-blue-600" aria-hidden="true" />
                            </div>
                        </div>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Recent Transactions</h2>
                        </div>
                        <div class="divide-y divide-gray-200">
                            <div
                                v-for="line in recentTransactions"
                                :key="line.id"
                                class="p-4 hover:bg-gray-50"
                            >
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div :class="[
                                            'p-2 rounded-lg',
                                            line.debit_amount > 0 ? 'bg-red-50' : 'bg-green-50'
                                        ]">
                                            <ArrowTrendingDownIcon
                                                v-if="line.debit_amount > 0"
                                                class="h-5 w-5 text-red-600"
                                                aria-hidden="true"
                                            />
                                            <ArrowTrendingUpIcon
                                                v-else
                                                class="h-5 w-5 text-green-600"
                                                aria-hidden="true"
                                            />
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ line.description || line.journal_entry.description }}</p>
                                            <p class="text-sm text-gray-500">{{ line.journal_entry.entry_number }} • {{ formatDate(line.journal_entry.entry_date) }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p v-if="line.debit_amount > 0" class="font-medium text-red-600">
                                            -{{ formatCurrency(line.debit_amount) }}
                                        </p>
                                        <p v-else class="font-medium text-green-600">
                                            +{{ formatCurrency(line.credit_amount) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div v-if="!recentTransactions?.length" class="p-8 text-center text-gray-500">
                                No transactions yet
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Account Details -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Account Details</h2>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Account Code</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-mono">{{ account.code }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Type</dt>
                                <dd class="mt-1 text-sm text-gray-900 capitalize">{{ account.type }}</dd>
                            </div>
                            <div v-if="account.subtype">
                                <dt class="text-sm font-medium text-gray-500">Subtype</dt>
                                <dd class="mt-1 text-sm text-gray-900 capitalize">{{ account.subtype.replace('_', ' ') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span :class="[
                                        'px-2 py-1 rounded-full text-xs font-medium',
                                        account.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                                    ]">
                                        {{ account.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </dd>
                            </div>
                            <div v-if="account.is_system">
                                <dd class="mt-1">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        System Account
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Description -->
                    <div v-if="account.description" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">Description</h2>
                        <p class="text-sm text-gray-600">{{ account.description }}</p>
                    </div>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>
