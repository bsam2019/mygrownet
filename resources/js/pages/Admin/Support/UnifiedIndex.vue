<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch, onMounted, onUnmounted } from 'vue';
import {
    TicketIcon,
    MagnifyingGlassIcon,
    ChatBubbleLeftRightIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    UserIcon,
    BuildingOfficeIcon,
    UserGroupIcon,
    BellAlertIcon,
    SignalIcon,
    SignalSlashIcon,
} from '@heroicons/vue/24/outline';

interface Ticket {
    id: number;
    source: 'employee' | 'member' | 'investor';
    ticket_number: string;
    subject: string;
    category: string;
    priority: string;
    status: string;
    assigned_to?: number;
    created_at: string;
    updated_at: string;
    comments_count: number;
    unread_count?: number;
    requester_name: string;
    requester_type: string;
    department?: string;
}

interface Agent {
    id: number;
    name: string;
    email: string;
    role: string;
}

interface Props {
    tickets: Ticket[];
    stats: {
        total: number;
        open: number;
        in_progress: number;
        pending: number;
        resolved: number;
        urgent: number;
        employee_tickets: number;
        member_tickets: number;
        investor_tickets: number;
        my_tickets: number;
        unassigned: number;
    };
    filters: {
        source?: string;
        status?: string;
        priority?: string;
        search?: string;
        assigned?: string;
    };
    currentUserId: number;
    agents?: Agent[];
}

const props = defineProps<Props>();

// Filters
const search = ref(props.filters.search || '');
const source = ref(props.filters.source || '');
const status = ref(props.filters.status || '');
const priority = ref(props.filters.priority || '');
const assigned = ref(props.filters.assigned || '');

// Real-time state
const isConnected = ref(false);
const newTicketAlert = ref(false);
const newTicketData = ref<any>(null);
const localTickets = ref<Ticket[]>([...props.tickets]);

// Quick assign state
const showAssignModal = ref(false);
const selectedTicketForAssign = ref<Ticket | null>(null);
const assigningTicketId = ref<number | null>(null);

// Quick assign a ticket
const quickAssign = (ticket: Ticket) => {
    selectedTicketForAssign.value = ticket;
    showAssignModal.value = true;
};

const assignToAgent = (agentId: number | null) => {
    if (!selectedTicketForAssign.value) return;
    
    assigningTicketId.value = selectedTicketForAssign.value.id;
    
    router.post(route('admin.unified-support.assign', { 
        source: selectedTicketForAssign.value.source, 
        id: selectedTicketForAssign.value.id 
    }), {
        agent_id: agentId,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showAssignModal.value = false;
            selectedTicketForAssign.value = null;
            assigningTicketId.value = null;
        },
        onError: () => {
            assigningTicketId.value = null;
        }
    });
};

const assignToMe = (ticket: Ticket) => {
    assigningTicketId.value = ticket.id;
    
    router.post(route('admin.unified-support.assign', { 
        source: ticket.source, 
        id: ticket.id 
    }), {
        agent_id: props.currentUserId,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            assigningTicketId.value = null;
        },
        onError: () => {
            assigningTicketId.value = null;
        }
    });
};

// Echo instance
let echo: any = null;
let adminChannel: any = null;

// Apply filters
const applyFilters = () => {
    router.get(route('admin.unified-support.index'), {
        search: search.value || undefined,
        source: source.value || undefined,
        status: status.value || undefined,
        priority: priority.value || undefined,
        assigned: assigned.value || undefined,
    }, { preserveState: true });
};

watch([source, status, priority, assigned], applyFilters);

let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});

// Update local tickets when props change
watch(() => props.tickets, (newTickets) => {
    localTickets.value = [...newTickets];
}, { deep: true });

// Subscribe to real-time channels
const subscribeToRealtime = () => {
    // Check if Echo is available
    if (!(window as any).Echo) {
        console.warn('[UnifiedIndex] Echo not available, retrying in 1s...');
        setTimeout(subscribeToRealtime, 1000);
        return;
    }

    echo = (window as any).Echo;
    console.log('[UnifiedIndex] Echo found, setting up subscriptions...');

    // Monitor connection state
    if (echo.connector?.pusher) {
        const pusher = echo.connector.pusher;
        
        // Initial state
        isConnected.value = pusher.connection.state === 'connected';
        console.log('[UnifiedIndex] Initial connection state:', pusher.connection.state);

        pusher.connection.bind('state_change', (states: any) => {
            console.log('[UnifiedIndex] Connection state changed:', states.current);
            isConnected.value = states.current === 'connected';
        });
    }

    // Subscribe to support.admin channel for new ticket notifications
    try {
        console.log('[UnifiedIndex] Subscribing to private-support.admin...');
        adminChannel = echo.private('support.admin');
        
        adminChannel.listen('.ticket.created', (data: any) => {
            console.log('[UnifiedIndex] 🎉 NEW TICKET EVENT RECEIVED:', data);
            
            // Show alert
            newTicketAlert.value = true;
            newTicketData.value = data;
            
            // Play sound
            try {
                const audio = new Audio('/sounds/notification.mp3');
                audio.volume = 0.5;
                audio.play().catch(() => {});
            } catch (e) {}
            
            // Reload data to get the new ticket
            router.reload({ only: ['tickets', 'stats'] });
            
            // Hide alert after 10 seconds
            setTimeout(() => {
                newTicketAlert.value = false;
                newTicketData.value = null;
            }, 10000);
        });

        console.log('[UnifiedIndex] ✅ Subscribed to support.admin channel');
    } catch (e) {
        console.error('[UnifiedIndex] ❌ Failed to subscribe to support.admin:', e);
    }
};

// Unsubscribe from channels
const unsubscribeFromRealtime = () => {
    if (echo && adminChannel) {
        try {
            echo.leave('support.admin');
            console.log('[UnifiedIndex] Left support.admin channel');
        } catch (e) {
            console.warn('[UnifiedIndex] Error leaving channel:', e);
        }
    }
};

// Helper functions
const getStatusColor = (s: string) => ({
    open: 'bg-blue-100 text-blue-700',
    in_progress: 'bg-amber-100 text-amber-700',
    pending: 'bg-purple-100 text-purple-700',
    resolved: 'bg-green-100 text-green-700',
    closed: 'bg-gray-100 text-gray-700',
}[s] || 'bg-gray-100 text-gray-700');

const getPriorityColor = (p: string) => ({
    urgent: 'bg-red-100 text-red-700',
    high: 'bg-orange-100 text-orange-700',
    medium: 'bg-yellow-100 text-yellow-700',
    low: 'bg-gray-100 text-gray-700',
}[p] || 'bg-gray-100 text-gray-700');

const getSourceColor = (s: string) => ({
    employee: 'bg-indigo-100 text-indigo-700',
    member: 'bg-emerald-100 text-emerald-700',
    investor: 'bg-violet-100 text-violet-700',
}[s] || 'bg-gray-100 text-gray-700');

const getSourceIcon = (s: string) => {
    if (s === 'employee') return BuildingOfficeIcon;
    if (s === 'investor') return UserIcon;
    return UserGroupIcon;
};

const getSourceLabel = (s: string) => ({
    employee: 'Employee',
    member: 'Member',
    investor: 'Investor',
}[s] || s);

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const mins = Math.floor(diff / 60000);
    
    if (mins < 1) return 'Just now';
    if (mins < 60) return `${mins}m ago`;
    if (mins < 1440) return `${Math.floor(mins / 60)}h ago`;
    if (mins < 10080) return `${Math.floor(mins / 1440)}d ago`;
    return date.toLocaleDateString();
};

// Lifecycle
onMounted(() => {
    subscribeToRealtime();
});

onUnmounted(() => {
    unsubscribeFromRealtime();
});
</script>

<template>
    <Head title="Unified Support" />
    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Unified Support Center</h1>
                        <p class="text-gray-600 mt-1">Manage support tickets from all sources</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <!-- Connection Status -->
                        <span 
                            :class="[
                                'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium',
                                isConnected 
                                    ? 'bg-green-100 text-green-700' 
                                    : 'bg-red-100 text-red-700'
                            ]"
                        >
                            <component 
                                :is="isConnected ? SignalIcon : SignalSlashIcon" 
                                class="h-4 w-4" 
                            />
                            {{ isConnected ? 'Live Updates' : 'Disconnected' }}
                        </span>
                    </div>
                </div>

                <!-- New Ticket Alert -->
                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-200"
                >
                    <div v-if="newTicketAlert" class="mb-4 bg-green-50 border-2 border-green-300 rounded-xl p-4 flex items-center gap-3 shadow-lg">
                        <div class="p-2 bg-green-100 rounded-full animate-bounce">
                            <BellAlertIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-green-800">🎉 New Support Ticket!</p>
                            <p v-if="newTicketData" class="text-sm text-green-700">
                                {{ newTicketData.ticket_number }} - {{ newTicketData.subject }}
                            </p>
                            <p class="text-xs text-green-600 mt-1">From: {{ newTicketData?.requester_name || 'Unknown' }} ({{ newTicketData?.source || 'unknown' }})</p>
                        </div>
                        <button @click="newTicketAlert = false" class="p-2 text-green-600 hover:text-green-800 hover:bg-green-100 rounded-full" aria-label="Dismiss alert">
                            ×
                        </button>
                    </div>
                </Transition>

                <!-- Stats Cards -->
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 mb-6">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-gray-100 rounded-lg">
                                <TicketIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
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
                                <p class="text-2xl font-bold text-blue-600">{{ stats.open }}</p>
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
                                <p class="text-2xl font-bold text-amber-600">{{ stats.in_progress }}</p>
                                <p class="text-xs text-gray-500">In Progress</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-red-100 rounded-lg">
                                <ExclamationTriangleIcon class="h-5 w-5 text-red-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-red-600">{{ stats.urgent }}</p>
                                <p class="text-xs text-gray-500">Urgent</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-indigo-100 rounded-lg">
                                <BuildingOfficeIcon class="h-5 w-5 text-indigo-600" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="text-2xl font-bold text-indigo-600">{{ stats.employee_tickets }}</p>
                                <p class="text-xs text-gray-500">Employee</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Filters (My Tickets / Unassigned) -->
                <div class="flex flex-wrap gap-2 mb-4">
                    <button
                        @click="assigned = assigned === 'me' ? '' : 'me'"
                        :class="[
                            'inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                            assigned === 'me' 
                                ? 'bg-blue-600 text-white' 
                                : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
                        ]"
                    >
                        <UserIcon class="h-4 w-4" aria-hidden="true" />
                        My Tickets
                        <span v-if="stats.my_tickets > 0" :class="[
                            'px-2 py-0.5 rounded-full text-xs',
                            assigned === 'me' ? 'bg-white/20' : 'bg-blue-100 text-blue-700'
                        ]">
                            {{ stats.my_tickets }}
                        </span>
                    </button>
                    <button
                        @click="assigned = assigned === 'unassigned' ? '' : 'unassigned'"
                        :class="[
                            'inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                            assigned === 'unassigned' 
                                ? 'bg-amber-600 text-white' 
                                : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'
                        ]"
                    >
                        <ExclamationTriangleIcon class="h-4 w-4" aria-hidden="true" />
                        Unassigned
                        <span v-if="stats.unassigned > 0" :class="[
                            'px-2 py-0.5 rounded-full text-xs',
                            assigned === 'unassigned' ? 'bg-white/20' : 'bg-amber-100 text-amber-700'
                        ]">
                            {{ stats.unassigned }}
                        </span>
                    </button>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
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
                        <select v-model="source" class="border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Sources</option>
                            <option value="employee">Employee</option>
                            <option value="member">Member</option>
                            <option value="investor">Investor</option>
                        </select>
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
                    </div>
                </div>

                <!-- Tickets Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ticket</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Source</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Requester</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="ticket in localTickets" :key="`${ticket.source}-${ticket.id}`" class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <Link :href="route('admin.unified-support.show', { source: ticket.source, id: ticket.id })" class="block">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-medium text-gray-900 hover:text-blue-600">
                                                #{{ ticket.ticket_number }}
                                            </p>
                                            <span v-if="ticket.unread_count && ticket.unread_count > 0" class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-red-500 text-white text-xs font-bold">
                                                {{ ticket.unread_count }}
                                            </span>
                                            <span v-if="ticket.assigned_to === currentUserId" class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-blue-100 text-blue-700">
                                                Mine
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-500 truncate max-w-xs">{{ ticket.subject }}</p>
                                    </Link>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="[getSourceColor(ticket.source), 'inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full']">
                                        <component :is="getSourceIcon(ticket.source)" class="h-3.5 w-3.5" aria-hidden="true" />
                                        {{ getSourceLabel(ticket.source) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                            <UserIcon class="h-4 w-4 text-gray-500" aria-hidden="true" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ ticket.requester_name }}</p>
                                            <p v-if="ticket.department" class="text-xs text-gray-500">{{ ticket.department }}</p>
                                        </div>
                                    </div>
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
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ formatDate(ticket.created_at) }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Quick Assign to Me -->
                                        <button
                                            v-if="!ticket.assigned_to && ticket.status !== 'closed' && ticket.status !== 'resolved'"
                                            @click.stop="assignToMe(ticket)"
                                            :disabled="assigningTicketId === ticket.id"
                                            class="px-2 py-1 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded disabled:opacity-50"
                                            title="Assign to me"
                                        >
                                            {{ assigningTicketId === ticket.id ? '...' : 'Take' }}
                                        </button>
                                        <!-- Assign to Others -->
                                        <button
                                            v-if="agents && agents.length > 0 && ticket.status !== 'closed' && ticket.status !== 'resolved'"
                                            @click.stop="quickAssign(ticket)"
                                            class="px-2 py-1 text-xs font-medium text-gray-600 hover:text-gray-700 hover:bg-gray-50 rounded"
                                            title="Assign to agent"
                                        >
                                            Assign
                                        </button>
                                        <Link
                                            :href="route('admin.unified-support.show', { source: ticket.source, id: ticket.id })"
                                            class="text-blue-600 hover:text-blue-700 text-sm font-medium"
                                        >
                                            View
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="localTickets.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <TicketIcon class="h-12 w-12 mx-auto text-gray-300 mb-4" aria-hidden="true" />
                                    <p class="text-gray-500">No tickets found</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Assign Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showAssignModal" class="fixed inset-0 z-50 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <!-- Backdrop -->
                        <div class="fixed inset-0 bg-black/50" @click="showAssignModal = false"></div>
                        
                        <!-- Modal -->
                        <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Assign Ticket</h3>
                            
                            <div v-if="selectedTicketForAssign" class="mb-4 p-3 bg-gray-50 rounded-lg">
                                <p class="text-sm font-medium text-gray-900">#{{ selectedTicketForAssign.ticket_number }}</p>
                                <p class="text-sm text-gray-600 truncate">{{ selectedTicketForAssign.subject }}</p>
                            </div>

                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                <!-- Assign to Me -->
                                <button
                                    @click="assignToAgent(currentUserId)"
                                    :disabled="assigningTicketId !== null"
                                    class="w-full flex items-center gap-3 p-3 rounded-lg border border-blue-200 bg-blue-50 hover:bg-blue-100 transition-colors text-left disabled:opacity-50"
                                >
                                    <div class="w-8 h-8 bg-blue-200 rounded-full flex items-center justify-center">
                                        <UserIcon class="h-4 w-4 text-blue-600" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Assign to Me</p>
                                        <p class="text-xs text-gray-500">Take ownership of this ticket</p>
                                    </div>
                                </button>

                                <!-- Unassign -->
                                <button
                                    v-if="selectedTicketForAssign?.assigned_to"
                                    @click="assignToAgent(null)"
                                    :disabled="assigningTicketId !== null"
                                    class="w-full flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors text-left disabled:opacity-50"
                                >
                                    <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                        <UserIcon class="h-4 w-4 text-gray-600" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Unassign</p>
                                        <p class="text-xs text-gray-500">Remove current assignment</p>
                                    </div>
                                </button>

                                <!-- Other Agents -->
                                <div v-if="agents && agents.length > 0" class="pt-2 border-t border-gray-200">
                                    <p class="text-xs font-medium text-gray-500 uppercase mb-2">Other Agents</p>
                                    <button
                                        v-for="agent in agents.filter(a => a.id !== currentUserId)"
                                        :key="agent.id"
                                        @click="assignToAgent(agent.id)"
                                        :disabled="assigningTicketId !== null"
                                        class="w-full flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors text-left disabled:opacity-50"
                                    >
                                        <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                            <UserIcon class="h-4 w-4 text-gray-600" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ agent.name }}</p>
                                            <p class="text-xs text-gray-500">{{ agent.role }}</p>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-4 flex justify-end">
                                <button
                                    @click="showAssignModal = false"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AdminLayout>
</template>
