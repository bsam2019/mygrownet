<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { PlusIcon, BanknotesIcon, ArrowTrendingUpIcon, ArrowTrendingDownIcon } from '@heroicons/vue/24/outline';

interface Transaction {
    id: number;
    amount: number;
    type: 'deposit' | 'withdrawal' | 'payment';
    description: string | null;
    status: 'pending' | 'completed' | 'failed';
    created_at: string;
}

interface Props {
    wallet: {
        balance: number;
        locked_balance: number;
        available_balance: number;
    };
    transactions: {
        data: Transaction[];
        current_page: number;
        last_page: number;
        total: number;
    };
}

const props = defineProps<Props>();

const showDepositForm = ref(false);

const form = useForm({
    amount: '',
    description: '',
});

const deposit = () => {
    form.post(route('bizboost.wallet.deposit'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showDepositForm.value = false;
        },
    });
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
};
</script>

<template>
    <Head title="Wallet - BizBoost" />
    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Wallet</h1>
                    <button @click="showDepositForm = !showDepositForm" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <PlusIcon class="h-5 w-5" />
                        Deposit Funds
                    </button>
                </div>

                <div v-if="showDepositForm" class="mb-6 bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Deposit Funds</h2>
                    <form @submit.prevent="deposit" class="space-y-4 max-w-md">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Amount (USD)</label>
                            <input v-model="form.amount" type="number" step="0.01" min="0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="0.00" />
                            <p v-if="form.errors.amount" class="mt-1 text-sm text-red-600">{{ form.errors.amount }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description (optional)</label>
                            <input v-model="form.description" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="e.g. Ad campaign budget" />
                        </div>
                        <div class="flex gap-3">
                            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50">
                                {{ form.processing ? 'Processing...' : 'Deposit' }}
                            </button>
                            <button type="button" @click="showDepositForm = false" class="px-4 py-2 text-gray-700 hover:text-gray-900">Cancel</button>
                        </div>
                    </form>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-blue-100 rounded-lg">
                                <BanknotesIcon class="h-6 w-6 text-blue-600" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Total Balance</p>
                                <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(wallet.balance) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-yellow-100 rounded-lg">
                                <ArrowTrendingUpIcon class="h-6 w-6 text-yellow-600" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Locked in Campaigns</p>
                                <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(wallet.locked_balance) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-green-100 rounded-lg">
                                <ArrowTrendingDownIcon class="h-6 w-6 text-green-600" />
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Available Balance</p>
                                <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(wallet.available_balance) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b">
                        <h2 class="text-lg font-semibold text-gray-900">Transaction History</h2>
                    </div>
                    <div v-if="transactions.data.length === 0" class="p-6 text-gray-500 text-center">No transactions yet.</div>
                    <div v-else class="divide-y">
                        <div v-for="txn in transactions.data" :key="txn.id" class="px-6 py-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ txn.description || txn.type }}</p>
                                <p class="text-xs text-gray-500">{{ txn.created_at }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span :class="['text-sm font-semibold', txn.type === 'deposit' ? 'text-green-600' : 'text-red-600']">
                                    {{ txn.type === 'deposit' ? '+' : '-' }}{{ formatCurrency(txn.amount) }}
                                </span>
                                <span :class="['px-2 py-0.5 text-xs rounded-full', txn.status === 'completed' ? 'bg-green-100 text-green-700' : txn.status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700']">
                                    {{ txn.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div v-if="transactions.last_page > 1" class="px-6 py-4 border-t flex justify-center gap-2">
                        <Link v-for="page in transactions.last_page" :key="page" :href="route('bizboost.wallet.index', { page })" :class="['px-3 py-1 rounded', page === transactions.current_page ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']">
                            {{ page }}
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
