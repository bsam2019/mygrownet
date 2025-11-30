<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import {
    PlusIcon,
    MagnifyingGlassIcon,
    FunnelIcon,
    EyeIcon,
    PencilIcon,
    TrashIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    CheckCircleIcon,
    XCircleIcon,
} from '@heroicons/vue/24/outline';
import Pagination from '@/Components/Pagination.vue';

interface Task {
    id: number;
    title: string;
    description: string | null;
    status: string;
    priority: string;
    due_date: string | null;
    is_overdue: boolean;
    assignee: { id: number; name: string } | null;
    assigner: { id: number; name: string } | null;
    department: string | null;
    created_at: string;
}

interface Props {
    tasks: {
        data: Task[];
        links: any[];
        meta?: any;
    };
    stats: {
        total: number;
        pending: number;
        in_progress: number;
        completed: number;
        overdue: number;
    };
    employees: { id: number; name: string }[];
    departments: { id: number; name: string }[];
    filters: {
        status?: string;
        priority?: string;
        department_id?: string;
        assigned_to?: string;
        search?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const priorityFilter = ref(props.filters.priority || '');
const departmentFilter = ref(props.filters.department_id || '');
const assigneeFilter = ref(props.filters.assigned_to || '');
const showFilters = ref(false);

const applyFilters = () => {
    router.get(route('admin.tasks.index'), {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        priority: priorityFilter.value || undefined,
        department_id: departmentFilter.value || undefined,
        assigned_to: assigneeFilter.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    priorityFilter.value = '';
    departmentFilter.value = '';
    assigneeFilter.value = '';
    applyFilters();
};

const deleteTask = (task: Task) => {
    if (confirm(`Are you sure you want to delete "${task.title}"?`)) {
        router.delete(route('admin.tasks.destroy', task.id));
    }
};

const updateStatus = (task: Task, newStatus: string) => {
    router.patch(route('admin.tasks.update-status', task.id), {
        status: newStatus,
    }, {
        preserveScroll: true,
    });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        pending: 'bg-yellow-100 text-yellow-800',
        in_progress: 'bg-blue-100 text-blue-800',
        completed: 'bg-green-100 text-green-800',
        cancelled: 'bg-gray-100 text-gray-800',
        on_hold: 'bg-purple-100 text-purple-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getPriorityColor = (priority: string) => {
    const colors: Record<string, string> = {
        low: 'bg-gray-100 text-gray-700',
        medium: 'bg-blue-100 text-blue-700',
        high: 'bg-orange-100 text-orange-700',
        urgent: 'bg-red-100 text-red-700',
    };
    return colors[priority] || 'bg-gray-100 text-gray-700';
};

// Debounced search
let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 500);
});
</script>

<template>
    <Head title="Task Management" />
    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Task Management</h1>
                        <p class="text-gray-600 mt-1">Assign and track employee tasks</p>
                    </div>
                    <Link
                        :href="route('admin.tasks.create')"
                        class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                        Create Task
                    </Link>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-4">
                        <p class="text-sm text-gray-500">Total Tasks</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <p class="text-sm text-gray-500">Pending</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ stats.pending }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <p class="text-sm text-gray-500">In Progress</p>
                        <p class="text-2xl font-bold text-blue-600">{{ stats.in_progress }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <p class="text-sm text-gray-500">Completed</p>
                        <p class="text-2xl font-bold text-green-600">{{ stats.completed }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <p class="text-sm text-gray-500">Overdue</p>
                        <p class="text-2xl font-bold text-red-600">{{ stats.overdue }}</p>
                    </div>
                </div>

                <!-- Search and Filters -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="p-4 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row gap-4">
                            <div class="flex-1 relative">
                                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search tasks..."
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                            <button
                                @click="showFilters = !showFilters"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                            >
                                <FunnelIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                                Filters
                            </button>
                        </div>
                    </div>

                    <!-- Filter Panel -->
                    <div v-if="showFilters" class="p-4 bg-gray-50 border-b border-gray-200">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select
                                    v-model="statusFilter"
                                    @change="applyFilters"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2"
                                >
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="on_hold">On Hold</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                                <select
                                    v-model="priorityFilter"
                                    @change="applyFilters"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2"
                                >
                                    <option value="">All Priorities</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                                <select
                                    v-model="departmentFilter"
                                    @change="applyFilters"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2"
                                >
                                    <option value="">All Departments</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                        {{ dept.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Assignee</label>
                                <select
                                    v-model="assigneeFilter"
                                    @change="applyFilters"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2"
                                >
                                    <option value="">All Employees</option>
                                    <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                        {{ emp.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button
                                @click="clearFilters"
                                class="text-sm text-gray-600 hover:text-gray-900"
                            >
                                Clear all filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tasks Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assignee</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="task in tasks.data" :key="task.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-start">
                                            <div>
                                                <Link
                                                    :href="route('admin.tasks.show', task.id)"
                                                    class="text-sm font-medium text-gray-900 hover:text-blue-600"
                                                >
                                                    {{ task.title }}
                                                </Link>
                                                <p v-if="task.department" class="text-xs text-gray-500 mt-1">
                                                    {{ task.department }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span v-if="task.assignee" class="text-sm text-gray-900">
                                            {{ task.assignee.name }}
                                        </span>
                                        <span v-else class="text-sm text-gray-400">Unassigned</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <select
                                            :value="task.status"
                                            @change="updateStatus(task, ($event.target as HTMLSelectElement).value)"
                                            :class="[getStatusColor(task.status), 'text-xs font-medium px-2 py-1 rounded-full border-0 cursor-pointer']"
                                        >
                                            <option value="pending">Pending</option>
                                            <option value="in_progress">In Progress</option>
                                            <option value="completed">Completed</option>
                                            <option value="on_hold">On Hold</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="[getPriorityColor(task.priority), 'text-xs font-medium px-2 py-1 rounded-full']">
                                            {{ task.priority }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div v-if="task.due_date" class="flex items-center">
                                            <ClockIcon
                                                :class="['h-4 w-4 mr-1', task.is_overdue ? 'text-red-500' : 'text-gray-400']"
                                                aria-hidden="true"
                                            />
                                            <span :class="['text-sm', task.is_overdue ? 'text-red-600 font-medium' : 'text-gray-600']">
                                                {{ task.due_date }}
                                            </span>
                                        </div>
                                        <span v-else class="text-sm text-gray-400">No due date</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <Link
                                                :href="route('admin.tasks.show', task.id)"
                                                class="p-1 text-gray-400 hover:text-blue-600"
                                                title="View"
                                            >
                                                <EyeIcon class="h-5 w-5" aria-hidden="true" />
                                            </Link>
                                            <Link
                                                :href="route('admin.tasks.edit', task.id)"
                                                class="p-1 text-gray-400 hover:text-blue-600"
                                                title="Edit"
                                            >
                                                <PencilIcon class="h-5 w-5" aria-hidden="true" />
                                            </Link>
                                            <button
                                                @click="deleteTask(task)"
                                                class="p-1 text-gray-400 hover:text-red-600"
                                                title="Delete"
                                            >
                                                <TrashIcon class="h-5 w-5" aria-hidden="true" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="tasks.data.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                        No tasks found. Create your first task to get started.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="tasks.links && tasks.links.length > 3" class="px-6 py-4 border-t border-gray-200">
                        <Pagination :links="tasks.links" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
