<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import {
    ChatBubbleLeftRightIcon,
    TicketIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    UserIcon,
    ArrowRightIcon,
    BellAlertIcon,
} from '@heroicons/vue/24/outline';

interface Ticket {
    id: number;
    ticket_number: string;
    subject: string;
    status: string;
    priority: string;
    created_at: string;
    updated_at: string;
    last_message_at?: string;
    comments_count: number;
    employee: {
        id: number;
        full_name: string;
        department?: { name: string };
    };
}

interface Props {
    activeTickets: Ticket[];
    stats: {
        total: number;
        open: number;
        in_progress: number;
        urgent: number;
        avg_response_time: string;
    };
}

const props = defineProps<Props>();

const tickets = ref<Ticket[]>([...props.activeTickets]);
const statsData = ref({ ...props.stats });
const newTicketAlert = ref<{ id: number; ticket_number: string; subject: string; employee_name: string; priority: string } | null>(null);

// Sync with props when they change
watch(() => props.activeTickets, (newData) => {
    tickets.value = [...newData];
});
watch(() => props.stats, (newStats) => {
    statsData.value = { ...newStats };
});

// Echo instance - will be set if available
let echo: any = null;

// Initialize Echo if available
const initEcho = () => {
    // Try global Echo first (set in app.ts)
    if ((window as any).Echo) {
        echo = (window as any).Echo;
        subscribeToAdmin();
        return;
    }
    
    // Fallback: try useEcho from @laravel/echo-vue
    import('@laravel/echo-vue').then(({ useEcho }) => {
        const echoInstance = useEcho();
        if (echoInstance) {
            echo = echoInstance;
            subscribeToAdmin();
        }
    }).catch(() => {
        // Real-time features disabled
    });
};

const subscribeToAdmin = () => {
    if (!echo) return;
    
    try {
        console.log('[AdminDashboard] Subscribing to support.admin channel');
        echo.private('support.admin')
            .listen('.ticket.created', (data: any) => {
                console.log('[AdminDashboard] New ticket created:', data);
                
                // Show detailed alert
                newTicketAlert.value = {
                    id: data.ticket_id,
                    ticket_number: data.ticket_number,
                    subject: data.subject,
                    employee_name: data.employee_name,
                    priority: data.priority,
                };
                
                // Play notification sound (optional)
                try {
                    const audio = new Audio('/sounds/notification.mp3');
                    audio.volume = 0.5;
                    audio.play().catch(() => {});
                } catch (e) {}
                
                // Add the new ticket to the list
                const newTicket: Ticket = {
                    id: data.ticket_id,
                    ticket_number: data.ticket_number,
                    subject: data.subject,
                    status: 'open',
                    priority: data.priority,
                    created_at: data.created_at,
                    updated_at: data.created_at,
                    comments_count: 0,
                    employee: {
                        id: data.employee_id || 0,
                        full_name: data.employee_name,
                        department: data.department_name ? { name: data.department_name } : undefined,
                    },
                };
                tickets.value.unshift(newTicket);

                // Update stats
                statsData.value.open++;
                if (data.priority === 'urgent') {
                    statsData.value.urgent++;
                }

                // Auto-hide after 10 seconds
                setTimeout(() => {
                    if (newTicketAlert.value?.id === data.ticket_id) {
                        newTicketAlert.value = null;
                    }
                }, 10000);
            });
    } catch (e) {
        console.warn('[AdminDashboard] Failed to subscribe to admin channel:', e);
    }
};

onMounted(() => {
    initEcho();
});

onUnmounted(() => {
    if (echo) {
        try {
            echo.leave('support.admin');
        } catch (e) {
            // Ignore errors when leaving
        }
    }
});

const getStatusColor = (s: string) => ({
    open: 'bg-blue-100 text-blue-700 border-blue-200',
    in_progress: 'bg-amber-100 text-amber-700 border-amber-200',
    pending: 'bg-purple-100 text-purple-700 border-purple-200',
}[s] || 'bg-gray-100 text-gray-700 border-gray-200');

const getPriorityColor = (p: string) => ({
    urgent: 'bg-red-500',
    high: 'bg-amber-500',
    medium: 'bg-blue-500',
    low: 'bg-gray-400',
}[p] || 'bg-gray-400');

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

const urgentTickets = computed(() => 
    tickets.value.filter(t => t.priority === 'urgent' && t.status !== 'resolved')
);

const openTickets = computed(() => 
    tickets.value.filter(t => t.status === 'open')
);

const inProgressTickets = computed(() => 
    tickets.value.filter(t => t.status === 'in_progress')
);
</script>

<template>
    <Head title="Live Support Dashboard" />
    <AdminLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Live Support Dashboard</h1>
                    <p class="text-gray-500 mt-1">Monitor and respond to employee support requests in real-time</p>
                </div>
                <Link 
                    :href="route('admin.support.index')"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
                >
                    <TicketIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                    All Tickets
                </Link>
            </div>

            <!-- New Ticket Alert -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
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
                        <Link
                            :href="route('admin.support.live-chat', newTicketAlert.id)"
                            class="px-4 py-2 bg-white text-blue-600 rounded-lg font-medium hover:bg-blue-50 transition-colors"
                        >
                            Open Chat
                        </Link>
                        <button
                            @click="newTicketAlert = null"
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

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-3xl font-bold text-blue-600">{{ statsData.open }}</p>
                            <p class="text-sm text-gray-500 mt-1">Open Tickets</p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-xl">
                            <ClockIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-3xl font-bold text-amber-600">{{ statsData.in_progress }}</p>
                            <p class="text-sm text-gray-500 mt-1">In Progress</p>
                        </div>
                        <div class="p-3 bg-amber-100 rounded-xl">
                            <ChatBubbleLeftRightIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-3xl font-bold text-red-600">{{ statsData.urgent }}</p>
                            <p class="text-sm text-gray-500 mt-1">Urgent</p>
                        </div>
                        <div class="p-3 bg-red-100 rounded-xl">
                            <ExclamationTriangleIcon class="h-6 w-6 text-red-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-3xl font-bold text-gray-900">{{ statsData.avg_response_time }}</p>
                            <p class="text-sm text-gray-500 mt-1">Avg Response</p>
                        </div>
                        <div class="p-3 bg-gray-100 rounded-xl">
                            <ClockIcon class="h-6 w-6 text-gray-600" aria-hidden="true" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Urgent Tickets Alert -->
            <div v-if="urgentTickets.length > 0" class="bg-red-50 border border-red-200 rounded-xl p-4">
                <div class="flex items-center gap-2 mb-3">
                    <ExclamationTriangleIcon class="h-5 w-5 text-red-600" aria-hidden="true" />
                    <h3 class="font-semibold text-red-800">Urgent Tickets Requiring Attention</h3>
                </div>
                <div class="space-y-2">
                    <Link
                        v-for="ticket in urgentTickets"
                        :key="ticket.id"
                        :href="route('admin.support.live-chat', ticket.id)"
                        class="flex items-center justify-between p-3 bg-white rounded-lg hover:bg-red-50 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-full bg-red-100 flex items-center justify-center">
                                <UserIcon class="h-4 w-4 text-red-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">#{{ ticket.ticket_number }}</p>
                                <p class="text-sm text-gray-500">{{ ticket.employee.full_name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500">{{ formatTime(ticket.updated_at) }}</span>
                            <ArrowRightIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Active Conversations Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Open Tickets -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                            <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                            Open Tickets
                        </h2>
                        <span class="text-sm text-gray-500">{{ openTickets.length }} tickets</span>
                    </div>
                    <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                        <Link
                            v-for="ticket in openTickets"
                            :key="ticket.id"
                            :href="route('admin.support.live-chat', ticket.id)"
                            class="flex items-center gap-4 p-4 hover:bg-gray-50 transition-colors"
                        >
                            <div class="relative">
                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                    <UserIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                                </div>
                                <span :class="[getPriorityColor(ticket.priority), 'absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white']"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="font-medium text-gray-900 truncate">{{ ticket.employee.full_name }}</p>
                                    <span class="text-xs text-gray-400">#{{ ticket.ticket_number }}</span>
                                </div>
                                <p class="text-sm text-gray-500 truncate">{{ ticket.subject }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500">{{ formatTime(ticket.updated_at) }}</p>
                                <span v-if="ticket.comments_count > 0" class="text-xs text-blue-600">
                                    {{ ticket.comments_count }} messages
                                </span>
                            </div>
                        </Link>
                        <div v-if="openTickets.length === 0" class="p-8 text-center text-gray-500">
                            <ChatBubbleLeftRightIcon class="h-12 w-12 mx-auto text-gray-300 mb-2" aria-hidden="true" />
                            <p>No open tickets</p>
                        </div>
                    </div>
                </div>

                <!-- In Progress Tickets -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                            <span class="w-2 h-2 bg-amber-500 rounded-full"></span>
                            In Progress
                        </h2>
                        <span class="text-sm text-gray-500">{{ inProgressTickets.length }} tickets</span>
                    </div>
                    <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                        <Link
                            v-for="ticket in inProgressTickets"
                            :key="ticket.id"
                            :href="route('admin.support.live-chat', ticket.id)"
                            class="flex items-center gap-4 p-4 hover:bg-gray-50 transition-colors"
                        >
                            <div class="relative">
                                <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                    <UserIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                                </div>
                                <span :class="[getPriorityColor(ticket.priority), 'absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white']"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="font-medium text-gray-900 truncate">{{ ticket.employee.full_name }}</p>
                                    <span class="text-xs text-gray-400">#{{ ticket.ticket_number }}</span>
                                </div>
                                <p class="text-sm text-gray-500 truncate">{{ ticket.subject }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500">{{ formatTime(ticket.updated_at) }}</p>
                                <span v-if="ticket.comments_count > 0" class="text-xs text-amber-600">
                                    {{ ticket.comments_count }} messages
                                </span>
                            </div>
                        </Link>
                        <div v-if="inProgressTickets.length === 0" class="p-8 text-center text-gray-500">
                            <ChatBubbleLeftRightIcon class="h-12 w-12 mx-auto text-gray-300 mb-2" aria-hidden="true" />
                            <p>No tickets in progress</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
