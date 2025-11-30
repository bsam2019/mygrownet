<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import { ref, watch } from 'vue';
import {
    FunnelIcon,
    Squares2X2Icon,
    ListBulletIcon,
    CheckCircleIcon,
    ClockIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

interface Task {
    id: number;
    title: string;
    description: string;
    priority: string;
    status: string;
    due_date: string;
    assigner?: { full_name: string };
}

interface Props {
    tasks: Task[];
    statusCounts: {
        all: number;
        todo: number;
        in_progress: number;
        review: number;
        completed: number;
        overdue: number;
    };
    filters: {
        status?: string;
        priority?: string;
    };
}

const props = defineProps<Props>();

const selectedStatus = ref(props.filters.status || 'all');
const selectedPriority = ref(props.filters.priority || '');

const statusTabs = [
    { key: 'all', label: 'All', count: props.statusCounts.all },
    { key: 'todo', label: 'To Do', count: props.statusCounts.todo },
    { key: 'in_progress', label: 'In Progress', count: props.statusCounts.in_progress },
    { key: 'review', label: 'Review', count: props.statusCounts.review },
    { key: 'completed', label: 'Completed', count: props.statusCounts.completed },
];

const priorities = ['low', 'medium', 'high', 'urgent'];

const applyFilters = () => {
    router.get(route('employee.portal.tasks.index'), {
        status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
        priority: selectedPriority.value || undefined,
    }, { preserveState: true });
};

watch([selectedStatus, selectedPriority], applyFilters);

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        'todo': 'bg-gray-100 text-gray-700',
        'in_progress': 'bg-blue-100 text-blue-700',
        'review': 'bg-purple-100 text-purple-700',
        'completed': 'bg-green-100 text-green-700',
        'cancelled': 'bg-red-100 text-red-700',
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
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

const isOverdue = (task: Task) => {
    if (!task.due_date || ['completed', 'cancelled'].includes(task.status)) return false;
    return new Date(task.due_date) < new Date();
};

const updateStatus = (taskId: number, newStatus: string) => {
    router.patch(route('employee.portal.tasks.update-status', taskId), {
        status: newStatus,
    }, { preserveScroll: true });
};
</script>

<template>
    <Head title="My Tasks" />

    <EmployeePortalLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">My Tasks</h1>
                    <p class="text-gray-500 mt-1">Manage and track your assigned tasks</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('employee.portal.tasks.kanban')"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <Squares2X2Icon class="h-5 w-5 mr-2 text-gray-500" aria-hidden="true" />
                        Kanban View
                    </Link>
                </div>
            </div>

            <!-- Status Tabs -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px overflow-x-auto">
                        <button v-for="tab in statusTabs" :key="tab.key"
                            @click="selectedStatus = tab.key"
                            :class="[
                                'px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap transition-colors',
                                selectedStatus === tab.key
                                    ? 'border-blue-500 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]">
                            {{ tab.label }}
                            <span :class="[
                                'ml-2 px-2 py-0.5 rounded-full text-xs',
                                selectedStatus === tab.key ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'
                            ]">
                                {{ tab.count }}
                            </span>
                        </button>
                    </nav>
                </div>

                <!-- Filters -->
                <div class="p-4 border-b border-gray-100 flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <FunnelIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        <span class="text-sm text-gray-500">Priority:</span>
                    </div>
                    <select v-model="selectedPriority"
                        class="text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Priorities</option>
                        <option v-for="p in priorities" :key="p" :value="p">
                            {{ p.charAt(0).toUpperCase() + p.slice(1) }}
                        </option>
                    </select>
                </div>

                <!-- Task List -->
                <div class="divide-y divide-gray-100">
                    <div v-for="task in tasks" :key="task.id" 
                        class="p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start gap-4">
                            <!-- Status Checkbox -->
                            <div class="pt-1">
                                <button v-if="task.status !== 'completed'"
                                    @click="updateStatus(task.id, task.status === 'todo' ? 'in_progress' : 'completed')"
                                    class="h-5 w-5 rounded border-2 border-gray-300 hover:border-blue-500 transition-colors"
                                    :aria-label="task.status === 'todo' ? 'Start task' : 'Complete task'">
                                </button>
                                <CheckCircleIcon v-else class="h-5 w-5 text-green-500" aria-hidden="true" />
                            </div>

                            <!-- Task Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <Link :href="route('employee.portal.tasks.show', task.id)"
                                            :class="[
                                                'font-medium hover:text-blue-600 transition-colors',
                                                task.status === 'completed' ? 'text-gray-400 line-through' : 'text-gray-900'
                                            ]">
                                            {{ task.title }}
                                        </Link>
                                        <p v-if="task.description" class="text-sm text-gray-500 mt-1 line-clamp-2">
                                            {{ task.description }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <span :class="getPriorityColor(task.priority)"
                                            class="px-2 py-1 text-xs font-medium rounded-full">
                                            {{ task.priority }}
                                        </span>
                                        <span :class="getStatusColor(task.status)"
                                            class="px-2 py-1 text-xs font-medium rounded-full">
                                            {{ task.status.replace('_', ' ') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4 mt-3 text-sm text-gray-500">
                                    <div v-if="task.due_date" class="flex items-center gap-1"
                                        :class="{ 'text-red-600': isOverdue(task) }">
                                        <ClockIcon class="h-4 w-4" aria-hidden="true" />
                                        <span>{{ new Date(task.due_date).toLocaleDateString() }}</span>
                                        <ExclamationTriangleIcon v-if="isOverdue(task)" class="h-4 w-4" aria-hidden="true" />
                                    </div>
                                    <div v-if="task.assigner">
                                        Assigned by {{ task.assigner.full_name }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="tasks.length === 0" class="p-12 text-center">
                        <CheckCircleIcon class="h-12 w-12 mx-auto text-gray-300 mb-4" aria-hidden="true" />
                        <h3 class="text-lg font-medium text-gray-900">No tasks found</h3>
                        <p class="text-gray-500 mt-1">
                            {{ selectedStatus === 'all' ? 'You have no tasks assigned.' : `No ${selectedStatus.replace('_', ' ')} tasks.` }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
