<template>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Transaction History</h2>
        </div>

        <div v-if="loading" class="p-8 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-gray-200 border-t-blue-600"></div>
            <p class="mt-2 text-sm text-gray-600">Loading transactions...</p>
        </div>

        <div v-else-if="transactions.length === 0" class="p-8 text-center">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                />
            </svg>
            <p class="text-gray-600">No transactions yet</p>
        </div>

        <div v-else class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Reference
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Amount
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Customer
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="transaction in transactions" :key="transaction.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ transaction.transaction_reference }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ transaction.currency }} {{ formatAmount(transaction.amount) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            <div>{{ transaction.customer_name || 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ transaction.phone_number }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                :class="[
                                    'px-2 py-1 text-xs font-medium rounded-full',
                                    getStatusClass(transaction.status),
                                ]"
                            >
                                {{ transaction.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ formatDate(transaction.created_at) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <button
                                @click="viewDetails(transaction)"
                                class="text-blue-600 hover:text-blue-700 font-medium"
                            >
                                View
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total > pagination.per_page" class="px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} transactions
                </div>
                <div class="flex gap-2">
                    <button
                        v-for="page in paginationPages"
                        :key="page"
                        @click="loadPage(page)"
                        :class="[
                            'px-3 py-1 rounded',
                            page === pagination.current_page
                                ? 'bg-blue-600 text-white'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                        ]"
                    >
                        {{ page }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Transaction Details Modal -->
        <TransactionDetailsModal
            v-if="selectedTransaction"
            :transaction="selectedTransaction"
            @close="selectedTransaction = null"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import TransactionDetailsModal from './TransactionDetailsModal.vue';

interface Props {
    siteId: number;
}

const props = defineProps<Props>();

const transactions = ref<any[]>([]);
const loading = ref(true);
const selectedTransaction = ref<any>(null);
const pagination = ref({
    current_page: 1,
    per_page: 10,
    total: 0,
    from: 0,
    to: 0,
    last_page: 1,
});

const paginationPages = computed(() => {
    const pages = [];
    for (let i = 1; i <= pagination.value.last_page; i++) {
        pages.push(i);
    }
    return pages;
});

async function loadTransactions(page = 1) {
    loading.value = true;
    try {
        const response = await fetch(`/growbuilder/sites/${props.siteId}/payment/transactions?page=${page}`);
        const data = await response.json();
        transactions.value = data.data || [];
        pagination.value = {
            current_page: data.current_page,
            per_page: data.per_page,
            total: data.total,
            from: data.from,
            to: data.to,
            last_page: data.last_page,
        };
    } catch (error) {
        console.error('Failed to load transactions:', error);
    } finally {
        loading.value = false;
    }
}

function loadPage(page: number) {
    loadTransactions(page);
}

function formatAmount(amount: number): string {
    return new Intl.NumberFormat('en-ZM', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
}

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('en-ZM', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function getStatusClass(status: string): string {
    const classes: Record<string, string> = {
        completed: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        processing: 'bg-blue-100 text-blue-800',
        failed: 'bg-red-100 text-red-800',
        cancelled: 'bg-gray-100 text-gray-800',
        refunded: 'bg-purple-100 text-purple-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

function viewDetails(transaction: any) {
    selectedTransaction.value = transaction;
}

onMounted(() => {
    loadTransactions();
});
</script>
