<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch, onMounted, onUnmounted } from 'vue';
import {
    TicketIcon,
    MagnifyingGlassIcon,
    FunnelIcon,
    ChatBubbleLeftRightIcon,
    ClockIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    UserIcon,
    BellAlertIcon,
} from '@heroicons/vue/24/outline';

interface Ticket {
    id: number;
    ticket_number: string;
    subject: string;
    category: string;
    priority: string;
    status: string;
    created_at: string;
    updated_at: string;
    comments_count: number;
    unread_count: number;
    last_message_at?: string;
    employee: {
        id: number;
        full_name: string;
        department?: { name: string };
    };
    assigned_to?: {
        id: number;
        full_name: string;
    };
}

interface Props {
    tickets: {
        data: Ticket[];
        links: any;
        meta: any;
    };
    stats: {
        total: number;
        open: number;
        in_progress: number;
        pending: number;
        resolved: number;
        urgent: number;
    };
    categories: Record<string, string>;
    filters: {
        status?: string;
        priority?: string;
        category?: string;
        search?: string;
    };
}

const props = defineProps<Props>();

// Make tickets and stats reactive so we can update them in real-time
const ticketsList = ref<Ticket[]>([...props.tickets.data]);
const statsData = ref({ ...props.stats });

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const priority = ref(props.filters.priority || '');
const category = ref(props.filters.category || '');

// Sync with props when they change (e.g., after filter/pagination)
watch(() => props.tickets.data, (newData) => {
    ticketsList.value = [...newData];
});
watch(() => props.stats, (newStats) => {
    statsData.value = { ...newStats };
});

const applyFilters = () => {
    router.get(route('admin.support.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
        priority: priority.value || undefined,
        category: category.value || undefined,
    }, { preserveState: true });
};

watch([status, priority, category], applyFilters);

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});

const getStatusColor = (s: string) => {
    const colors: Record<string, string> = {
        open: 'bg-blue-100 text-blue-700',
        in_progress: 'bg-amber-100 text-amber-700',
        pending: 'bg-purple-100 text-purple-700',
        resolved: 'bg-green-100 text-green-700',
        closed: 'bg-gray-100 text-gray-700',
    };
    return colors[s] || 'bg-gray-100 text-gray-700';
};

const getPriorityColor = (p: string) => {
    const colors: Record<string, string> = {
        low: 'bg-gray-100 text-gray-600',
        medium: 'bg-blue-100 text-blue-600',
        high: 'bg-amber-100 text-amber-600',
        urgent: 'bg-red-100 text-red-600',
    };
    return colors[p] || 'bg-gray-100 text-gray-600';
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Real-time updates
let echo: any = null;
const newTicketAlert = ref<{ id: number; ticket_number: string; subject: string; employee_name: string; priority: string } | null>(null);

const initEcho = () => {
    if ((window as any).Echo) {
        echo = (window as any).Echo;
        subscribeToAdminChannel();
    }
};

const subscribeToAdminChannel = () => {
    if (!echo) return;

    try {
        console.log('[AdminSupport] Subscribing to support.admin channel');
        const channel = echo.private('support.admin');

        channel.listen('.ticket.created', (data: any) => {
            console.log('[AdminSupport] New ticket created:', data);
            
            // Show alert notification
            newTicketAlert.value = {
                id: data.ticket_id,
                ticket_number: data.ticket_number,
                subject: data.subject,
                employee_name: data.employee_name,
                priority: data.priority,
            };

            // Add the new ticket to the top of the list (if not filtered out)
            const newTicket: Ticket = {
                id: data.ticket_id,
                ticket_number: data.ticket_number,
                subject: data.subject,
                category: data.category || 'general',
                priority: data.priority,
                status: data.status || 'open',
                created_at: data.created_at,
                updated_at: data.created_at,
                comments_count: 0,
                employee: {
                    id: data.employee_id || 0,
                    full_name: data.employee_name,
                    department: data.department_name ? { name: data.department_name } : undefined,
                },
                assigned_to: undefined,
            };

            // Only add if no filters are active or if it matches current filters
            const shouldAdd = !status.value || status.value === 'open';
            if (shouldAdd) {
                ticketsList.value.unshift(newTicket);
            }

            // Update stats
            statsData.value.total++;
            statsData.value.open++;
            if (data.priority === 'urgent') {
                statsData.value.urgent++;
            }

            // Play notification sound (optional)
            try {
                const audio = new Audio('/sounds/notification.mp3');
                audio.volume = 0.5;
                audio.play().catch(() => {}); // Ignore if sound fails
            } catch (e) {}

            // Auto-hide alert after 10 seconds
            setTimeout(() => {
                if (newTicketAlert.value?.id === data.ticket_id) {
                    newTicketAlert.value = null;
                }
            }, 10000);
        });

    } catch (e) {
        console.warn('[AdminSupport] Failed to subscribe to admin channel:', e);
    }
};

const dismissAlert = () => {
    newTicketAlert.value = null;
};

const viewNewTicket = () => {
    if (newTicketAlert.value) {
        router.visit(route('admin.support.show', newTicketAlert.value.id));
    }
};

const refreshTickets = () => {
    router.reload({ only: ['tickets', 'stats'] });
    newTicketAlert.value = null;
};

onMounted(() => {
    initEcho();
});

onUnmounted(() => {
    if (echo) {
        try {
            echo.leave('support.admin');
        } catch (e) {}
    }
});
</script>

<template>
    <Head title="Support Tickets" />

    <AdminLayout>
        <div class="space-y-6">
            <!-- New Ticket Alert -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 -translate-y-4"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-4"
            >
                <div v-if="newTicketAlert" class="bg-blue-600 text-white rounded-xl p-4 shadow-lg flex items-center gap-4">
                    <div class="p-2 bg-white/20 rounded-lg">
                        <BellAlertIcon class="h-6 w-6 animate-bounce" aria-hidden="true" />
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold">New Support Ticket!</p>
                        <p class="text-sm text-blue-100">
                            #{{ newTicketAlert.ticket_number }} - {{ newTicketAlert.subject }}
                            <span class="opacity-75">from {{ newTicketAlert.employee_name }}</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            @click="viewNewTicket"
                            class="px-4 py-2 bg-white text-blue-600 rounded-lg font-medium hover:bg-blue-50 transition-colors"
                        >
                            View Ticket
                        </button>
                        <button
                            @click="refreshTickets"
                            class="px-4 py-2 bg-blue-500 text-white rounded-lg font-medium hover:bg-blue-400 transition-colors"
                        >
                            Refresh List
                        </button>
                        <button
                            @click="dismissAlert"
                            class="p-2 hover:bg-white/20 rounded-lg transition-colors"
                            aria-label="Dismiss notification"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </Transition>

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Support Tickets</h1>
                    <p class="text-gray-500 mt-1">Manage employee support requests</p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-gray-100 rounded-lg">
                            <TicketIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ statsData.total }}</p>
                            <p class="text-xs text-gray-500">Total</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <ClockIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-blue-600">{{ statsData.open }}</p>
                            <p class="text-xs text-gray-500">Open</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-amber-100 rounded-lg">
                            <ChatBubbleLeftRightIcon class="h-5 w-5 text-amber-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-amber-600">{{ statsData.in_progress }}</p>
                            <p class="text-xs text-gray-500">In Progress</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <ClockIcon class="h-5 w-5 text-purple-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-purple-600">{{ statsData.pending }}</p>
                            <p class="text-xs text-gray-500">Pending</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <CheckCircleIcon class="h-5 w-5 text-green-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-green-600">{{ statsData.resolved }}</p>
                            <p class="text-xs text-gray-500">Resolved</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <ExclamationTriangleIcon class="h-5 w-5 text-red-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-red-600">{{ statsData.urgent }}</p>
                            <p class="text-xs text-gray-500">Urgent</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <div class="relative">
                            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search tickets..."
                                class="w-full pl-10 pr-4 py-2 border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>
                    </div>
                    <select v-model="status" class="border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Status</option>
                        <option value="open">Open</option>
                        <option value="in_progress">In Progress</option>
                        <option value="pending">Pending</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                    <select v-model="priority" class="border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Priority</option>
                        <option value="urgent">Urgent</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                    <select v-model="category" class="border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Categories</option>
                        <option v-for="(label, key) in categories" :key="key" :value="key">{{ label }}</option>
                    </select>
                </div>
            </div>

            <!-- Tickets Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ticket</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="ticket in ticketsList" :key="ticket.id" class="hover:bg-gray-50 transition-colors" :class="{ 'bg-blue-50': ticket.id === newTicketAlert?.id }">
                            <td class="px-6 py-4">
                                <Link :href="route('admin.support.show', ticket.id)" class="block">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-medium text-gray-900 hover:text-blue-600">
                                            #{{ ticket.ticket_number }}
                                        </p>
                                        <!-- Unread indicator -->
                                        <span 
                                            v-if="ticket.unread_count > 0" 
                                            class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-bold text-white bg-red-500 rounded-full animate-pulse"
                                            :title="`${ticket.unread_count} unread message(s)`"
                                        >
                                            {{ ticket.unread_count }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-500 truncate max-w-xs">{{ ticket.subject }}</p>
                                </Link>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                        <UserIcon class="h-4 w-4 text-gray-500" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ ticket.employee.full_name }}</p>
                                        <p class="text-xs text-gray-500">{{ ticket.employee.department?.name }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-600">{{ categories[ticket.category] || ticket.category }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="[getPriorityColor(ticket.priority), 'px-2 py-1 text-xs font-medium rounded-full']">
                                    {{ ticket.priority }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="[getStatusColor(ticket.status), 'px-2 py-1 text-xs font-medium rounded-full']">
                                    {{ ticket.status.replace('_', ' ') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="ticket.assigned_to" class="text-sm text-gray-600">
                                    {{ ticket.assigned_to.full_name }}
                                </span>
                                <span v-else class="text-sm text-gray-400">Unassigned</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ formatDate(ticket.created_at) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="route('admin.support.show', ticket.id)"
                                        class="text-blue-600 hover:text-blue-700 text-sm font-medium"
                                    >
                                        View
                                    </Link>
                                    <Link
                                        :href="route('admin.support.live-chat', ticket.id)"
                                        class="text-green-600 hover:text-green-700 text-sm font-medium"
                                    >
                                        Chat
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="ticketsList.length === 0">
                            <td colspan="8" class="px-6 py-12 text-center">
                                <TicketIcon class="h-12 w-12 mx-auto text-gray-300 mb-4" aria-hidden="true" />
                                <p class="text-gray-500">No tickets found</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
