<template>
    <GrowFinanceLayout>
        <div class="px-4 py-4 pb-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Quotations</h1>
                    <p class="text-gray-500 text-sm">Create and manage quotes for customers</p>
                </div>
                <button 
                    @click="router.visit(route('growfinance.quotations.create'))"
                    class="p-3 bg-emerald-500 text-white rounded-xl shadow-lg shadow-emerald-500/30 active:scale-95 transition-transform"
                    aria-label="Create quotation"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 gap-3 mb-6">
                <div class="bg-white rounded-xl p-4 shadow-sm">
                    <p class="text-xs text-gray-500 mb-1">Pending Value</p>
                    <p class="text-lg font-bold text-gray-900">{{ formatMoney(stats.pending_value) }}</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm">
                    <p class="text-xs text-gray-500 mb-1">Accepted</p>
                    <p class="text-lg font-bold text-emerald-600">{{ stats.accepted }}</p>
                </div>
            </div>

            <!-- Status Filter -->
            <div class="flex gap-2 overflow-x-auto pb-2 mb-4 -mx-4 px-4 scrollbar-hide">
                <button
                    @click="filterStatus(null)"
                    :class="[
                        'px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors',
                        !currentStatus ? 'bg-emerald-500 text-white' : 'bg-white text-gray-600'
                    ]"
                >
                    All ({{ stats.total }})
                </button>
                <button
                    v-for="status in statuses"
                    :key="status.value"
                    @click="filterStatus(status.value)"
                    :class="[
                        'px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors',
                        currentStatus === status.value ? 'bg-emerald-500 text-white' : 'bg-white text-gray-600'
                    ]"
                >
                    {{ status.label }}
                </button>
            </div>

            <!-- Quotations List -->
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
                <ul v-if="quotations.data.length > 0" class="divide-y divide-gray-100">
                    <li v-for="quotation in quotations.data" :key="quotation.id">
                        <button 
                            @click="router.visit(route('growfinance.quotations.show', quotation.id))"
                            class="w-full p-4 text-left active:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="font-medium text-gray-900">{{ quotation.quotation_number }}</p>
                                        <span v-if="quotation.is_expired && quotation.status !== 'expired'" class="text-xs px-1.5 py-0.5 rounded bg-red-100 text-red-600">
                                            Expired
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500 truncate">{{ quotation.customer?.name || 'Walk-in' }}</p>
                                    <p v-if="quotation.subject" class="text-xs text-gray-400 truncate">{{ quotation.subject }}</p>
                                    <p class="text-xs text-gray-400">{{ quotation.quotation_date }}</p>
                                </div>
                                <div class="text-right ml-4">
                                    <p class="font-semibold text-gray-900">{{ formatMoney(quotation.total_amount) }}</p>
                                    <span :class="['text-xs px-2 py-0.5 rounded-full', statusColors[quotation.status]]">
                                        {{ statusLabels[quotation.status] }}
                                    </span>
                                </div>
                            </div>
                        </button>
                    </li>
                </ul>
                <div v-else class="p-8 text-center">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                        <DocumentTextIcon class="h-6 w-6 text-gray-400" aria-hidden="true" />
                    </div>
                    <p class="text-gray-500 text-sm">No quotations found</p>
                    <button 
                        @click="router.visit(route('growfinance.quotations.create'))"
                        class="text-emerald-600 text-sm font-medium mt-2"
                    >
                        Create your first quotation
                    </button>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="quotations.data.length > 0 && quotations.last_page > 1" class="mt-4 flex justify-center">
                <nav class="flex gap-1">
                    <button
                        v-for="page in quotations.last_page"
                        :key="page"
                        @click="goToPage(page)"
                        :class="[
                            'w-8 h-8 rounded-lg text-sm font-medium transition-colors',
                            quotations.current_page === page 
                                ? 'bg-emerald-500 text-white' 
                                : 'bg-white text-gray-600 hover:bg-gray-50'
                        ]"
                    >
                        {{ page }}
                    </button>
                </nav>
            </div>
        </div>
    </GrowFinanceLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import { PlusIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';

interface Quotation {
    id: number;
    quotation_number: string;
    quotation_date: string;
    valid_until: string | null;
    total_amount: number;
    status: string;
    subject: string | null;
    is_expired: boolean;
    customer: { id: number; name: string } | null;
}

interface Props {
    quotations: { 
        data: Quotation[];
        current_page: number;
        last_page: number;
    };
    currentStatus: string | null;
    stats: {
        total: number;
        draft: number;
        sent: number;
        accepted: number;
        converted: number;
        pending_value: number;
    };
}

defineProps<Props>();

const statuses = [
    { value: 'draft', label: 'Draft' },
    { value: 'sent', label: 'Sent' },
    { value: 'accepted', label: 'Accepted' },
    { value: 'rejected', label: 'Rejected' },
    { value: 'converted', label: 'Converted' },
];

const statusLabels: Record<string, string> = {
    draft: 'Draft',
    sent: 'Sent',
    accepted: 'Accepted',
    rejected: 'Rejected',
    expired: 'Expired',
    converted: 'Converted',
};

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-600',
    sent: 'bg-blue-100 text-blue-600',
    accepted: 'bg-emerald-100 text-emerald-600',
    rejected: 'bg-red-100 text-red-600',
    expired: 'bg-amber-100 text-amber-600',
    converted: 'bg-indigo-100 text-indigo-600',
};

const formatMoney = (amount: number) => {
    return 'K' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const filterStatus = (status: string | null) => {
    router.visit(route('growfinance.quotations.index', status ? { status } : {}));
};

const goToPage = (page: number) => {
    router.visit(route('growfinance.quotations.index', { page }));
};
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
