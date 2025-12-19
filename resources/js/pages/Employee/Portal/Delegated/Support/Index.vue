<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/layouts/EmployeePortalLayout.vue';
import {
    ChatBubbleLeftRightIcon,
    TicketIcon,
    ClockIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    EyeIcon,
    FunnelIcon,
} from '@heroicons/vue/24/outline';

interface Ticket {
    id: number;
    ticket_number: string;
    subject: string;
    status: string;
    priority: string;
    created_at: string;
    updated_at: string;
    source: 'member' | 'investor';
    user?: { id: number; name: string; email: string };
    investor?: { id: number; name: string; email: string };
}

interface Props {
    employee: { id: number; full_name: string };
    tickets: Ticket[];
    stats: {
        total_open: number;
        in_progress: number;
        resolved_today: number;
    };
}

const props = defineProps<Props>();

const statusFilter = ref('all');
const priorityFilter = ref('all');

const filteredTickets = computed(() => {
    return props.tickets.filter(ticket => {
        if (statusFilter.value !== 'all' && ticket.status !== statusFilter.value) return false;
        if (priorityFilter.value !== 'all' && ticket.priority !== priorityFilter.value) return false;
        return true;
    });
});

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        open: 'bg-blue-100 text-blue-800',
        in_progress: 'bg-amber-100 text-amber-800',
        resolved: 'bg-green-100 text-green-800',
        closed: 'bg-gray-100 text-gray-800',
    };
    return colors[status] || colors.open;
};

const getPriorityColor = (priority: string) => {
    const colors: Record<string, string> = {
        low: 'text-gray-500',
        medium: 'text-blue-500',
        high: 'text-amber-500',
        urgent: 'text-red-500',
    };
    return colors[priority] || colors.medium;
};

const getTicketUser = (ticket: Ticket) => {
    return ticket.user || ticket.investor || { name: 'Unknown', email: '' };
};
</script>

<template>
    <EmployeePortalLayout>
        <Head title="Support Center - Delegated" />

        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <ChatBubbleLeftRightIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Support Center</h1>
                        <p class="text-sm text-gray-500">Handle member and investor support tickets</p>
                    </div>
                </div>
                <span class="px-3 py-1 text-xs font-medium bg-purple-100 text-purple-700 rounded-full">
                    Delegated Access
                </span>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <TicketIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.total_open }}</p>
                            <p class="text-sm text-gray-500">Open Tickets</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-amber-100 rounded-lg">
                            <ClockIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.in_progress }}</p>
                            <p class="text-sm text-gray-500">In Progress</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <CheckCircleIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.resolved_today }}</p>
                            <p class="text-sm text-gray-500">Resolved Today</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2">
                        <FunnelIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        <span class="text-sm text-gray-500">Filter:</span>
                    </div>
                    <select
                        v-model="statusFilter"
                        class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                    >
                        <option value="all">All Status</option>
                        <option value="open">Open</option>
                        <option value="in_progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                    <select
                        v-model="priorityFilter"
                        class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                    >
                        <option value="all">All Priority</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                    <span class="text-sm text-gray-500 ml-auto">
                        {{ filteredTickets.length }} ticket{{ filteredTickets.length !== 1 ? 's' : '' }}
                    </span>
                </div>
            </div>

            <!-- Tickets List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="divide-y divide-gray-200">
                    <div
                        v-for="ticket in filteredTickets"
                        :key="`${ticket.source}-${ticket.id}`"
                        class="p-4 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-mono text-gray-500">{{ ticket.ticket_number }}</span>
                                    <span :class="[
                                        'px-2 py-0.5 text-xs font-medium rounded-full',
                                        getStatusColor(ticket.status)
                                    ]">
                                        {{ ticket.status.replace('_', ' ') }}
                                    </span>
                                    <span :class="[
                                        'text-xs font-medium',
                                        getPriorityColor(ticket.priority)
                                    ]">
                                        {{ ticket.priority }}
                                    </span>
                                    <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">
                                        {{ ticket.source }}
                                    </span>
                                </div>
                                <h3 class="font-medium text-gray-900 truncate">{{ ticket.subject }}</h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    From: {{ getTicketUser(ticket).name }} ({{ getTicketUser(ticket).email }})
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    Updated {{ formatDate(ticket.updated_at) }}
                                </p>
                            </div>
                            <Link
                                :href="route('employee.portal.delegated.support.show', { source: ticket.source, ticketId: ticket.id })"
                                class="flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-purple-600 hover:text-purple-900 hover:bg-purple-50 rounded-lg transition-colors"
                            >
                                <EyeIcon class="h-4 w-4" aria-hidden="true" />
                                View
                            </Link>
                        </div>
                    </div>

                    <div v-if="filteredTickets.length === 0" class="p-12 text-center text-gray-500">
                        <TicketIcon class="h-12 w-12 mx-auto mb-3 text-gray-300" aria-hidden="true" />
                        <p>No tickets found</p>
                        <p class="text-sm">Try adjusting your filters</p>
                    </div>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
