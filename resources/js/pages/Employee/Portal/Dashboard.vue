<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import TimeOffRequestModal from '@/components/Employee/TimeOffRequestModal.vue';
import { 
    ClipboardDocumentListIcon, 
    FlagIcon, 
    CalendarDaysIcon, 
    ClockIcon,
    BellIcon,
    ArrowTrendingUpIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    PlusIcon,
    ChatBubbleLeftRightIcon,
    TicketIcon,
} from '@heroicons/vue/24/outline';

interface Balance {
    type: string;
    label: string;
    allowance: number;
    used: number;
    remaining: number;
    available: number;
}

interface SupportTicket {
    id: number;
    ticket_number: string;
    subject: string;
    status: string;
    priority: string;
    source: 'member' | 'investor';
    user_name: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    employee: {
        id: number;
        full_name: string;
        position?: { title: string };
        department?: { name: string };
    };
    taskStats: {
        total: number;
        completed: number;
        in_progress: number;
        overdue: number;
    };
    goalsSummary: {
        total_goals: number;
        completed_goals: number;
        active_goals: number;
        average_progress: number;
    };
    attendanceSummary: {
        today: {
            clocked_in: boolean;
            clocked_out: boolean;
            on_break: boolean;
            hours_worked: number | null;
        };
        weekly_hours: number | null;
        status: string;
    };
    timeOffSummary: {
        pending_count: number;
        total_remaining: number;
    };
    timeOffBalances?: Record<string, Balance>;
    recentTasks: Array<{
        id: number;
        title: string;
        priority: string;
        due_date: string;
        status: string;
    }>;
    activeGoals: Array<{
        id: number;
        title: string;
        progress: number;
        due_date: string;
    }>;
    unreadNotifications: number;
    assignedTickets?: SupportTicket[];
    supportStats?: {
        assigned_to_me: number;
        total_open: number;
    };
}

const props = withDefaults(defineProps<Props>(), {
    taskStats: () => ({ total: 0, completed: 0, in_progress: 0, overdue: 0 }),
    goalsSummary: () => ({ total_goals: 0, completed_goals: 0, active_goals: 0, average_progress: 0 }),
    attendanceSummary: () => ({
        today: { clocked_in: false, clocked_out: false, on_break: false, hours_worked: 0 },
        weekly_hours: 0,
        status: 'not_clocked_in',
    }),
    timeOffSummary: () => ({ pending_count: 0, total_remaining: 0 }),
    timeOffBalances: () => ({}),
    recentTasks: () => [],
    activeGoals: () => [],
    unreadNotifications: 0,
});

// Time Off Modal
const showTimeOffModal = ref(false);

const handleTimeOffSuccess = () => {
    // Refresh the page data after successful submission
    router.reload({ only: ['timeOffSummary', 'timeOffBalances'] });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        'not_clocked_in': 'text-gray-500',
        'working': 'text-green-600',
        'on_break': 'text-amber-500',
        'clocked_out': 'text-blue-600',
    };
    return colors[status] || 'text-gray-500';
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        'not_clocked_in': 'Not Clocked In',
        'working': 'Working',
        'on_break': 'On Break',
        'clocked_out': 'Clocked Out',
    };
    return labels[status] || status;
};

const getPriorityColor = (priority: string) => {
    const colors: Record<string, string> = {
        'low': 'bg-gray-100 text-gray-700',
        'medium': 'bg-blue-100 text-blue-700',
        'high': 'bg-amber-100 text-amber-700',
        'urgent': 'bg-red-100 text-red-700',
    };
    return colors[priority] || 'bg-gray-100 text-gray-700';
};
</script>

<template>
    <Head title="Employee Portal" />

    <EmployeePortalLayout>
        <div class="space-y-6">
            <!-- Welcome Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-6 text-white">
                <h1 class="text-2xl font-bold">Welcome back, {{ employee.full_name }}!</h1>
                <p class="text-blue-100 mt-1">
                    {{ employee.position?.title }} • {{ employee.department?.name }}
                </p>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Tasks -->
                <Link :href="route('employee.portal.tasks.index')" 
                    class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">My Tasks</p>
                            <p class="text-2xl font-bold text-gray-900">{{ taskStats.total }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ taskStats.in_progress }} in progress
                            </p>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <ClipboardDocumentListIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                        </div>
                    </div>
                    <div v-if="taskStats.overdue > 0" class="mt-3 flex items-center text-red-600 text-sm">
                        <ExclamationTriangleIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                        {{ taskStats.overdue }} overdue
                    </div>
                </Link>

                <!-- Goals -->
                <Link :href="route('employee.portal.goals.index')"
                    class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">My Goals</p>
                            <p class="text-2xl font-bold text-gray-900">{{ goalsSummary.active_goals }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ goalsSummary.average_progress }}% avg progress
                            </p>
                        </div>
                        <div class="p-3 bg-emerald-50 rounded-lg">
                            <FlagIcon class="h-6 w-6 text-emerald-600" aria-hidden="true" />
                        </div>
                    </div>
                </Link>

                <!-- Time Off -->
                <Link :href="route('employee.portal.time-off.index')"
                    class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Time Off Balance</p>
                            <p class="text-2xl font-bold text-gray-900">{{ timeOffSummary.total_remaining }}</p>
                            <p class="text-xs text-gray-500 mt-1">days remaining</p>
                        </div>
                        <div class="p-3 bg-purple-50 rounded-lg">
                            <CalendarDaysIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
                        </div>
                    </div>
                    <div v-if="timeOffSummary.pending_count > 0" class="mt-3 flex items-center text-amber-600 text-sm">
                        {{ timeOffSummary.pending_count }} pending request(s)
                    </div>
                </Link>

                <!-- Attendance -->
                <Link :href="route('employee.portal.attendance.index')"
                    class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Today's Status</p>
                            <p class="text-lg font-bold" :class="getStatusColor(attendanceSummary.status)">
                                {{ getStatusLabel(attendanceSummary.status) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ Number(attendanceSummary.weekly_hours ?? 0).toFixed(1) }}h this week
                            </p>
                        </div>
                        <div class="p-3 bg-amber-50 rounded-lg">
                            <ClockIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Tasks -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900">Upcoming Tasks</h2>
                        <Link :href="route('employee.portal.tasks.index')" class="text-sm text-blue-600 hover:text-blue-700">
                            View all
                        </Link>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div v-for="task in recentTasks" :key="task.id" class="p-4 hover:bg-gray-50">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <Link :href="route('employee.portal.tasks.show', task.id)" 
                                        class="font-medium text-gray-900 hover:text-blue-600 truncate block">
                                        {{ task.title }}
                                    </Link>
                                    <p class="text-sm text-gray-500 mt-1">
                                        Due: {{ new Date(task.due_date).toLocaleDateString() }}
                                    </p>
                                </div>
                                <span :class="getPriorityColor(task.priority)" 
                                    class="px-2 py-1 text-xs font-medium rounded-full ml-2">
                                    {{ task.priority }}
                                </span>
                            </div>
                        </div>
                        <div v-if="recentTasks.length === 0" class="p-8 text-center text-gray-500">
                            <CheckCircleIcon class="h-12 w-12 mx-auto text-gray-300 mb-3" aria-hidden="true" />
                            <p>No upcoming tasks</p>
                        </div>
                    </div>
                </div>

                <!-- Active Goals -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900">Active Goals</h2>
                        <Link :href="route('employee.portal.goals.index')" class="text-sm text-blue-600 hover:text-blue-700">
                            View all
                        </Link>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div v-for="goal in activeGoals" :key="goal.id" class="p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium text-gray-900 truncate">{{ goal.title }}</span>
                                <span class="text-sm text-gray-500">{{ goal.progress }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-emerald-500 h-2 rounded-full transition-all duration-300"
                                    :style="{ width: `${goal.progress}%` }">
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                Due: {{ new Date(goal.due_date).toLocaleDateString() }}
                            </p>
                        </div>
                        <div v-if="activeGoals.length === 0" class="p-8 text-center text-gray-500">
                            <FlagIcon class="h-12 w-12 mx-auto text-gray-300 mb-3" aria-hidden="true" />
                            <p>No active goals</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assigned Support Tickets (for Support Agents) -->
            <div v-if="assignedTickets && assignedTickets.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <ChatBubbleLeftRightIcon class="h-5 w-5 text-blue-600" aria-hidden="true" />
                        <h2 class="font-semibold text-gray-900">My Assigned Tickets</h2>
                        <span class="bg-blue-100 text-blue-700 text-xs font-medium px-2 py-0.5 rounded-full">
                            {{ assignedTickets.length }}
                        </span>
                    </div>
                    <Link :href="route('employee.portal.support-agent.tickets')" class="text-sm text-blue-600 hover:text-blue-700">
                        View all
                    </Link>
                </div>
                <div class="divide-y divide-gray-100">
                    <Link v-for="ticket in assignedTickets.slice(0, 5)" :key="ticket.id"
                        :href="route('employee.portal.support-agent.show', ticket.id)"
                        class="p-4 hover:bg-gray-50 flex items-start justify-between gap-4 block">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-mono text-gray-500">{{ ticket.ticket_number }}</span>
                                <span :class="[
                                    'px-2 py-0.5 text-xs font-medium rounded-full',
                                    ticket.source === 'member' ? 'bg-purple-100 text-purple-700' : 'bg-emerald-100 text-emerald-700'
                                ]">
                                    {{ ticket.source === 'member' ? 'Member' : 'Investor' }}
                                </span>
                            </div>
                            <p class="font-medium text-gray-900 truncate">{{ ticket.subject }}</p>
                            <p class="text-sm text-gray-500 mt-1">From: {{ ticket.user_name }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <span :class="[
                                'px-2 py-0.5 text-xs font-medium rounded-full',
                                ticket.priority === 'high' || ticket.priority === 'urgent' ? 'bg-red-100 text-red-700' :
                                ticket.priority === 'medium' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700'
                            ]">
                                {{ ticket.priority }}
                            </span>
                            <span :class="[
                                'px-2 py-0.5 text-xs font-medium rounded-full',
                                ticket.status === 'open' ? 'bg-blue-100 text-blue-700' :
                                ticket.status === 'in_progress' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700'
                            ]">
                                {{ ticket.status.replace('_', ' ') }}
                            </span>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="font-semibold text-gray-900 mb-4">Quick Actions</h2>
                <div class="flex flex-wrap gap-3">
                    <button @click="showTimeOffModal = true"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                        Request Time Off
                    </button>
                    <Link :href="route('employee.portal.tasks.kanban')"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        <ClipboardDocumentListIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                        Task Board
                    </Link>
                    <Link :href="route('employee.portal.team')"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        <ArrowTrendingUpIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                        View Team
                    </Link>
                </div>
            </div>
        </div>

        <!-- Time Off Request Modal -->
        <TimeOffRequestModal 
            :show="showTimeOffModal"
            :balances="timeOffBalances"
            @close="showTimeOffModal = false"
            @success="handleTimeOffSuccess"
        />
    </EmployeePortalLayout>
</template>
