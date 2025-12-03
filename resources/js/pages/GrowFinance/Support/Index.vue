<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import {
    TicketIcon,
    PlusIcon,
    FunnelIcon,
    ClockIcon,
    CheckCircleIcon,
    ExclamationCircleIcon,
} from '@heroicons/vue/24/outline';

interface Ticket {
    id: number;
    ticket_number: string;
    subject: string;
    category: string;
    status: string;
    priority: string;
    created_at: string;
    updated_at: string;
}

interface Category {
    value: string;
    label: string;
}

const props = defineProps<{
    tickets: Ticket[];
    categories: Category[];
}>();

const selectedFilter = ref<string>('all');

const filters = [
    { value: 'all', label: 'All' },
    { value: 'open', label: 'Open' },
    { value: 'in_progress', label: 'In Progress' },
    { value: 'resolved', label: 'Resolved' },
    { value: 'closed', label: 'Closed' },
];

const filteredTickets = computed(() => {
    if (selectedFilter.value === 'all') return props.tickets;
    return props.tickets.filter(t => t.status === selectedFilter.value);
});

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        open: 'bg-blue-100 text-blue-700',
        in_progress: 'bg-amber-100 text-amber-700',
        waiting: 'bg-purple-100 text-purple-700',
        resolved: 'bg-emerald-100 text-emerald-700',
        closed: 'bg-gray-100 text-gray-700',
    };
    return colors[status] || colors.open;
};

const getStatusIcon = (status: string) => {
    if (status === 'resolved' || status === 'closed') return CheckCircleIcon;
    if (status === 'in_progress' || status === 'waiting') return ClockIcon;
    return ExclamationCircleIcon;
};

const getPriorityColor = (priority: string) => {
    const colors: Record<string, string> = {
        low: 'text-gray-500',
        medium: 'text-amber-500',
        high: 'text-red-500',
    };
    return colors[priority] || colors.medium;
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const viewTicket = (id: number) => {
    router.get(route('growfinance.support.show', id));
};

const createTicket = () => {
    router.get(route('growfinance.support.create'));
};
</script>

<template>
    <GrowFinanceLayout>
        <Head title="Support - GrowFinance" />

        <div class="p-4 lg:p-6 max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl lg:text-2xl font-bold text-gray-900">Support Tickets</h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Get help with GrowFinance features
                    </p>
                </div>
                <button
                    @click="createTicket"
                    class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors"
                >
                    <PlusIcon class="h-4 w-4" aria-hidden="true" />
                    New Ticket
                </button>
            </div>

            <!-- Filters -->
            <div class="flex items-center gap-2 mb-4 overflow-x-auto pb-2 hide-scrollbar">
                <FunnelIcon class="h-4 w-4 text-gray-400 flex-shrink-0" aria-hidden="true" />
                <button
                    v-for="filter in filters"
                    :key="filter.value"
                    @click="selectedFilter = filter.value"
                    :class="[
                        'px-3 py-1.5 text-sm font-medium rounded-full whitespace-nowrap transition-colors',
                        selectedFilter === filter.value
                            ? 'bg-emerald-100 text-emerald-700'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                    ]"
                >
                    {{ filter.label }}
                </button>
            </div>

            <!-- Ticket List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div v-if="filteredTickets.length === 0" class="p-8 text-center">
                    <TicketIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" aria-hidden="true" />
                    <p class="text-gray-500">No tickets found</p>
                    <button
                        @click="createTicket"
                        class="mt-4 text-sm text-emerald-600 hover:text-emerald-700 font-medium"
                    >
                        Create your first ticket
                    </button>
                </div>

                <div v-else class="divide-y divide-gray-100">
                    <div
                        v-for="ticket in filteredTickets"
                        :key="ticket.id"
                        class="flex items-start gap-3 p-4 hover:bg-gray-50 transition-colors cursor-pointer"
                        @click="viewTicket(ticket.id)"
                    >
                        <!-- Status Icon -->
                        <div :class="['p-2 rounded-full', getStatusColor(ticket.status)]">
                            <component
                                :is="getStatusIcon(ticket.status)"
                                class="h-5 w-5"
                                aria-hidden="true"
                            />
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ ticket.subject }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ ticket.ticket_number }}
                                    </p>
                                </div>
                                <span class="text-xs text-gray-400 whitespace-nowrap">
                                    {{ formatDate(ticket.created_at) }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 mt-2">
                                <span :class="['inline-flex items-center px-2 py-0.5 rounded text-xs font-medium', getStatusColor(ticket.status)]">
                                    {{ ticket.status.replace('_', ' ') }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                    {{ ticket.category }}
                                </span>
                                <span :class="['text-xs font-medium', getPriorityColor(ticket.priority)]">
                                    {{ ticket.priority }} priority
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GrowFinanceLayout>
</template>
