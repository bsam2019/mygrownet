<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import {
    ChatBubbleLeftRightIcon,
    TicketIcon,
    ClockIcon,
    CheckCircleIcon,
    UserGroupIcon,
    ArrowRightIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

interface Ticket {
    id: number;
    ticket_number: string;
    subject: string;
    status: string;
    priority: string;
    source: 'member' | 'investor';
    user_name: string;
    user_email: string;
    created_at: string;
    updated_at: string;
    assigned_to: number | null;
}

interface Props {
    employee: any;
    tickets: Ticket[];
    stats: {
        total_open: number;
        assigned_to_me: number;
        resolved_today: number;
        avg_response_time: string;
    };
}

const props = defineProps<Props>();

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        open: 'bg-blue-100 text-blue-700',
        in_progress: 'bg-amber-100 text-amber-700',
        pending: 'bg-purple-100 text-purple-700',
        resolved: 'bg-green-100 text-green-700',
        closed: 'bg-gray-100 text-gray-700',
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
};

const getPriorityColor = (priority: string) => {
    const colors: Record<string, string> = {
        low: 'bg-gray-100 text-gray-600',
        medium: 'bg-blue-100 text-blue-600',
        high: 'bg-amber-100 text-amber-600',
        urgent: 'bg-red-100 text-red-600',
    };
    return colors[priority] || 'bg-gray-100 text-gray-600';
};

const getSourceBadge = (source: string) => {
    return source === 'investor' 
        ? 'bg-indigo-100 text-indigo-700' 
        : 'bg-emerald-100 text-emerald-700';
};

const formatTime = (date: string) => {
    const d = new Date(date);
    const now = new Date();
    const diff = now.getTime() - d.getTime();
    const mins = Math.floor(diff / 60000);
    
    if (mins < 1) return 'Just now';
    if (mins < 60) return `${mins}m ago`;
    if (mins < 1440) return `${Math.floor(mins / 60)}h ago`;
    return d.toLocaleDateString();
};
</script>

<template>
    <Head title="Support Agent Dashboard" />

    <EmployeePortalLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Support Agent Dashboard</h1>
                    <p class="text-gray-500 mt-1">Manage member and investor support tickets</p>
                </div>
                <Link 
                    :href="route('employee.portal.support-agent.tickets')"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                    <TicketIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                    View All Tickets
                </Link>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Open Tickets</p>
                            <p class="text-2xl font-bold text-blue-600">{{ stats.total_open }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <TicketIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Assigned to Me</p>
                            <p class="text-2xl font-bold text-amber-600">{{ stats.assigned_to_me }}</p>
                        </div>
                        <div class="p-3 bg-amber-50 rounded-lg">
                            <UserGroupIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Resolved Today</p>
                            <p class="text-2xl font-bold text-green-600">{{ stats.resolved_today }}</p>
                        </div>
                        <div class="p-3 bg-green-50 rounded-lg">
                            <CheckCircleIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Avg Response</p>
                            <p class="text-2xl font-bold text-purple-600">{{ stats.avg_response_time }}</p>
                        </div>
                        <div class="p-3 bg-purple-50 rounded-lg">
                            <ClockIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Tickets -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                        <ChatBubbleLeftRightIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        Recent Tickets
                    </h2>
                    <Link 
                        :href="route('employee.portal.support-agent.tickets')"
                        class="text-sm text-blue-600 hover:text-blue-700 flex items-center gap-1"
                    >
                        View all
                        <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
                    </Link>
                </div>

                <div class="divide-y divide-gray-100">
                    <Link 
                        v-for="ticket in tickets" 
                        :key="`${ticket.source}-${ticket.id}`"
                        :href="route('employee.portal.support-agent.show', { ticket: ticket.id }) + `?source=${ticket.source}`"
                        class="block p-5 hover:bg-gray-50 transition-colors"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm font-mono text-gray-500">{{ ticket.ticket_number }}</span>
                                    <span :class="[getSourceBadge(ticket.source), 'px-2 py-0.5 text-xs font-medium rounded capitalize']">
                                        {{ ticket.source }}
                                    </span>
                                    <span :class="[getPriorityColor(ticket.priority), 'px-2 py-0.5 text-xs font-medium rounded capitalize']">
                                        {{ ticket.priority }}
                                    </span>
                                </div>
                                <h3 class="font-medium text-gray-900 mt-1 truncate">{{ ticket.subject }}</h3>
                                <div class="flex items-center gap-3 mt-2 text-sm text-gray-500">
                                    <span>{{ ticket.user_name }}</span>
                                    <span>•</span>
                                    <span>{{ formatTime(ticket.created_at) }}</span>
                                </div>
                            </div>

                            <span :class="[getStatusColor(ticket.status), 'px-3 py-1 text-xs font-medium rounded-full capitalize whitespace-nowrap']">
                                {{ ticket.status.replace('_', ' ') }}
                            </span>
                        </div>
                    </Link>

                    <div v-if="tickets.length === 0" class="p-8 text-center text-gray-500">
                        <CheckCircleIcon class="h-12 w-12 mx-auto text-green-300 mb-3" aria-hidden="true" />
                        <p class="font-medium">All caught up!</p>
                        <p class="text-sm">No open tickets at the moment.</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-6 text-white">
                <h3 class="text-lg font-semibold mb-2">Quick Actions</h3>
                <div class="flex flex-wrap gap-3 mt-4">
                    <Link 
                        :href="route('employee.portal.support-agent.tickets') + '?status=open'"
                        class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-lg text-sm transition-colors"
                    >
                        View Open Tickets
                    </Link>
                    <Link 
                        :href="route('employee.portal.support-agent.tickets') + '?assigned_to_me=1'"
                        class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-lg text-sm transition-colors"
                    >
                        My Assigned Tickets
                    </Link>
                    <Link 
                        :href="route('employee.portal.support-agent.tickets') + '?status=pending'"
                        class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-lg text-sm transition-colors"
                    >
                        Pending Response
                    </Link>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
