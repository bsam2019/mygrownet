<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import { ref, watch } from 'vue';
import {
    TicketIcon,
    FunnelIcon,
    MagnifyingGlassIcon,
    ArrowLeftIcon,
} from '@heroicons/vue/24/outline';

interface Ticket {
    id: number;
    ticket_number: string;
    subject: string;
    description: string;
    status: string;
    priority: string;
    category: string;
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
    filters: {
        status: string;
        source: string;
        assigned_to_me: boolean;
    };
}

const props = defineProps<Props>();

const statusFilter = ref(props.filters.status);
const sourceFilter = ref(props.filters.source);
const assignedToMe = ref(props.filters.assigned_to_me);
const searchQuery = ref('');

const applyFilters = () => {
    router.get(route('employee.portal.support-agent.tickets'), {
        status: statusFilter.value,
        source: sourceFilter.value,
        assigned_to_me: assignedToMe.value ? '1' : '0',
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

watch([statusFilter, sourceFilter, assignedToMe], () => {
    applyFilters();
});

const filteredTickets = () => {
    if (!searchQuery.value) return props.tickets;
    const q = searchQuery.value.toLowerCase();
    return props.tickets.filter(t => 
        t.subject.toLowerCase().includes(q) ||
        t.ticket_number.toLowerCase().includes(q) ||
        t.user_name.toLowerCase().includes(q)
    );
};

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
    return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <Head title="Support Tickets" />

    <EmployeePortalLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link 
                    :href="route('employee.portal.support-agent.dashboard')"
                    class="p-2 hover:bg-gray-100 rounded-lg"
                >
                    <ArrowLeftIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                </Link>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Support Tickets</h1>
                    <p class="text-gray-500 mt-1">{{ filteredTickets().length }} tickets</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                <div class="flex flex-wrap items-center gap-4">
                    <!-- Search -->
                    <div class="relative flex-1 min-w-[200px]">
                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search tickets..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>

                    <!-- Status Filter -->
                    <select
                        v-model="statusFilter"
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="all">All Status</option>
                        <option value="open">Open</option>
                        <option value="in_progress">In Progress</option>
                        <option value="pending">Pending</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>

                    <!-- Source Filter -->
                    <select
                        v-model="sourceFilter"
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="all">All Sources</option>
                        <option value="member">Members</option>
                        <option value="investor">Investors</option>
                    </select>

                    <!-- Assigned to Me -->
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            v-model="assignedToMe"
                            type="checkbox"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                        <span class="text-sm text-gray-700">Assigned to me</span>
                    </label>
                </div>
            </div>

            <!-- Tickets List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="divide-y divide-gray-100">
                    <Link 
                        v-for="ticket in filteredTickets()" 
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
                                    <span class="text-xs text-gray-400">{{ ticket.category }}</span>
                                </div>
                                <h3 class="font-medium text-gray-900 mt-1">{{ ticket.subject }}</h3>
                                <p class="text-sm text-gray-500 mt-1 line-clamp-1">{{ ticket.description }}</p>
                                <div class="flex items-center gap-3 mt-2 text-sm text-gray-500">
                                    <span class="font-medium">{{ ticket.user_name }}</span>
                                    <span>•</span>
                                    <span>{{ ticket.user_email }}</span>
                                    <span>•</span>
                                    <span>{{ formatTime(ticket.created_at) }}</span>
                                </div>
                            </div>

                            <span :class="[getStatusColor(ticket.status), 'px-3 py-1 text-xs font-medium rounded-full capitalize whitespace-nowrap']">
                                {{ ticket.status.replace('_', ' ') }}
                            </span>
                        </div>
                    </Link>

                    <div v-if="filteredTickets().length === 0" class="p-8 text-center text-gray-500">
                        <TicketIcon class="h-12 w-12 mx-auto text-gray-300 mb-3" aria-hidden="true" />
                        <p>No tickets found</p>
                        <p class="text-sm mt-1">Try adjusting your filters</p>
                    </div>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
