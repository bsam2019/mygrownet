<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import {
    TicketIcon,
    PlusIcon,
    ClockIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    ChatBubbleLeftRightIcon,
} from '@heroicons/vue/24/outline';

interface Ticket {
    id: number;
    ticket_number: string;
    subject: string;
    category: string;
    priority: string;
    status: string;
    created_at: string;
    assignee?: { full_name: string };
}

interface Props {
    tickets: Ticket[];
    stats: {
        total: number;
        open: number;
        resolved: number;
        by_category: Record<string, number>;
    };
    categories: Record<string, string>;
    filters: { status?: string; category?: string; priority?: string };
}

const props = defineProps<Props>();

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        open: 'bg-blue-100 text-blue-700',
        in_progress: 'bg-amber-100 text-amber-700',
        waiting: 'bg-purple-100 text-purple-700',
        resolved: 'bg-green-100 text-green-700',
        closed: 'bg-gray-100 text-gray-700',
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
};

const getPriorityColor = (priority: string) => {
    const colors: Record<string, string> = {
        low: 'text-gray-500',
        medium: 'text-blue-600',
        high: 'text-amber-600',
        urgent: 'text-red-600',
    };
    return colors[priority] || 'text-gray-500';
};

const getPriorityBadge = (priority: string) => {
    const badges: Record<string, string> = {
        low: 'bg-gray-100 text-gray-700',
        medium: 'bg-blue-100 text-blue-700',
        high: 'bg-amber-100 text-amber-700',
        urgent: 'bg-red-100 text-red-700',
    };
    return badges[priority] || 'bg-gray-100 text-gray-700';
};
</script>

<template>
    <Head title="Help Desk" />

    <EmployeePortalLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Help Desk</h1>
                    <p class="text-gray-500 mt-1">Get support for IT, HR, and other issues</p>
                </div>
                <Link :href="route('employee.portal.support.create')"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                    New Ticket
                </Link>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Total Tickets</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <TicketIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Open Tickets</p>
                            <p class="text-2xl font-bold text-amber-600">{{ stats.open }}</p>
                        </div>
                        <div class="p-3 bg-amber-50 rounded-lg">
                            <ClockIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Resolved</p>
                            <p class="text-2xl font-bold text-green-600">{{ stats.resolved }}</p>
                        </div>
                        <div class="p-3 bg-green-50 rounded-lg">
                            <CheckCircleIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tickets List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">My Tickets</h2>
                    <div class="flex gap-3">
                        <select class="border-gray-300 rounded-lg text-sm">
                            <option value="">All Status</option>
                            <option value="open">Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                        </select>
                        <select class="border-gray-300 rounded-lg text-sm">
                            <option value="">All Categories</option>
                            <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
                        </select>
                    </div>
                </div>

                <div class="divide-y divide-gray-100">
                    <Link v-for="ticket in tickets" :key="ticket.id"
                        :href="route('employee.portal.support.show', ticket.id)"
                        class="block p-5 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-mono text-gray-500">{{ ticket.ticket_number }}</span>
                                    <span :class="getPriorityBadge(ticket.priority)" class="px-2 py-0.5 text-xs font-medium rounded capitalize">
                                        {{ ticket.priority }}
                                    </span>
                                </div>
                                <h3 class="font-medium text-gray-900 mt-1">{{ ticket.subject }}</h3>
                                <div class="flex items-center gap-3 mt-2 text-sm text-gray-500">
                                    <span>{{ categories[ticket.category] || ticket.category }}</span>
                                    <span>•</span>
                                    <span>{{ new Date(ticket.created_at).toLocaleDateString() }}</span>
                                    <span v-if="ticket.assignee">•</span>
                                    <span v-if="ticket.assignee">Assigned to: {{ ticket.assignee.full_name }}</span>
                                </div>
                            </div>

                            <span :class="getStatusColor(ticket.status)" class="px-3 py-1 text-xs font-medium rounded-full capitalize">
                                {{ ticket.status.replace('_', ' ') }}
                            </span>
                        </div>
                    </Link>

                    <div v-if="tickets.length === 0" class="p-8 text-center text-gray-500">
                        <ChatBubbleLeftRightIcon class="h-12 w-12 mx-auto text-gray-300 mb-3" aria-hidden="true" />
                        <p>No support tickets yet</p>
                        <Link :href="route('employee.portal.support.create')"
                            class="inline-flex items-center mt-4 text-blue-600 hover:text-blue-700">
                            <PlusIcon class="h-5 w-5 mr-1" aria-hidden="true" />
                            Create your first ticket
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Quick Help -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-6 text-white">
                <h3 class="text-lg font-semibold mb-2">Need immediate help?</h3>
                <p class="text-blue-100 mb-4">For urgent issues, you can also reach out directly:</p>
                <div class="flex flex-wrap gap-4">
                    <div class="bg-white/10 rounded-lg px-4 py-2">
                        <p class="text-sm text-blue-200">IT Support</p>
                        <p class="font-medium">it@mygrownet.com</p>
                    </div>
                    <div class="bg-white/10 rounded-lg px-4 py-2">
                        <p class="text-sm text-blue-200">HR Support</p>
                        <p class="font-medium">hr@mygrownet.com</p>
                    </div>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
