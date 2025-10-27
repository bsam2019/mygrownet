<template>
    <MemberLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h1 class="text-2xl font-bold text-gray-900">My Receipts</h1>
                <p class="text-gray-600 mt-1">View and download all your payment receipts</p>
            </div>

            <!-- Receipts List -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div v-if="receipts.data.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Receipt #
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Description
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="receipt in receipts.data" :key="receipt.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ receipt.receipt_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ formatDate(receipt.created_at) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getTypeBadgeClass(receipt.type)" 
                                          class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        {{ formatType(receipt.type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ receipt.description }}</div>
                                    <div v-if="receipt.payment_method" class="text-xs text-gray-500">
                                        via {{ receipt.payment_method }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">K {{ formatAmount(receipt.amount) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    <a :href="`/receipts/${receipt.id}/view`" 
                                       target="_blank"
                                       class="text-blue-600 hover:text-blue-900">
                                        View
                                    </a>
                                    <a :href="`/receipts/${receipt.id}/download`" 
                                       class="text-green-600 hover:text-green-900">
                                        Download
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No receipts yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Receipts will appear here after payments are processed</p>
                </div>

                <!-- Pagination -->
                <div v-if="receipts.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Showing {{ receipts.from }} to {{ receipts.to }} of {{ receipts.total }} receipts
                        </div>
                        <div class="flex space-x-2">
                            <Link v-if="receipts.prev_page_url" 
                                  :href="receipts.prev_page_url"
                                  class="px-3 py-1 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                                Previous
                            </Link>
                            <Link v-if="receipts.next_page_url" 
                                  :href="receipts.next_page_url"
                                  class="px-3 py-1 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                                Next
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';

interface Receipt {
    id: number;
    receipt_number: string;
    type: string;
    amount: number;
    payment_method: string;
    description: string;
    created_at: string;
}

interface Props {
    receipts: {
        data: Receipt[];
        from: number;
        to: number;
        total: number;
        prev_page_url: string | null;
        next_page_url: string | null;
    };
}

defineProps<Props>();

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const formatAmount = (amount: number) => {
    return Number(amount).toFixed(2);
};

const formatType = (type: string) => {
    const types: Record<string, string> = {
        'payment': 'Registration',
        'starter_kit': 'Starter Kit',
        'wallet': 'Wallet',
        'purchase': 'Purchase'
    };
    return types[type] || type;
};

const getTypeBadgeClass = (type: string) => {
    const classes: Record<string, string> = {
        'payment': 'bg-blue-100 text-blue-800',
        'starter_kit': 'bg-green-100 text-green-800',
        'wallet': 'bg-purple-100 text-purple-800',
        'purchase': 'bg-yellow-100 text-yellow-800'
    };
    return classes[type] || 'bg-gray-100 text-gray-800';
};
</script>
