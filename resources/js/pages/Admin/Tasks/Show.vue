<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeftIcon,
    PencilIcon,
    TrashIcon,
    ClockIcon,
    UserIcon,
    CalendarIcon,
    TagIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

interface Task {
    id: number;
    title: string;
    description: string | null;
    status: string;
    priority: string;
    due_date: string | null;
    started_at: string | null;
    completed_at: string | null;
    estimated_hours: number | null;
    actual_hours: number | null;
    is_overdue: boolean;
    tags: string[];
    notes: string | null;
    assignee: { id: number; name: string; email: string } | null;
    assigner: { id: number; name: string } | null;
    department: string | null;
    comments: { id: number; content: string; author: string; created_at: string }[];
    created_at: string;
    updated_at: string;
}

interface Props {
    task: Task;
}

const props = defineProps<Props>();

const deleteTask = () => {
    if (confirm(`Are you sure you want to delete "${props.task.title}"?`)) {
        router.delete(route('admin.tasks.destroy', props.task.id));
    }
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

const formatStatus = (status: string) => {
    return status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
};
</script>

<template>
    <Head :title="`Task: ${task.title}`" />
    <AdminLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('admin.tasks.index')"
                        class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4"
                    >
                        <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                        Back to Tasks
                    </Link>
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ task.title }}</h1>
                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                <span :class="[getStatusColor(task.status), 'px-2 py-1 text-xs font-medium rounded-full']">
                                    {{ formatStatus(task.status) }}
                                </span>
                                <span :class="[getPriorityColor(task.priority), 'px-2 py-1 text-xs font-medium rounded-full']">
                                    {{ task.priority }} priority
                                </span>
                                <span v-if="task.is_overdue" class="inline-flex items-center px-2 py-1 bg-red-100 text-red-700 text-xs font-medium rounded-full">
                                    <ExclamationTriangleIcon class="h-3 w-3 mr-1" aria-hidden="true" />
                                    Overdue
                                </span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <Link
                                :href="route('admin.tasks.edit', task.id)"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                            >
                                <PencilIcon class="h-4 w-4 mr-2" aria-hidden="true" />
                                Edit
                            </Link>
                            <button
                                @click="deleteTask"
                                class="inline-flex items-center px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50"
                            >
                                <TrashIcon class="h-4 w-4 mr-2" aria-hidden="true" />
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Description -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Description</h2>
                            <p v-if="task.description" class="text-gray-700 whitespace-pre-wrap">{{ task.description }}</p>
                            <p v-else class="text-gray-400 italic">No description provided</p>
                        </div>

                        <!-- Notes -->
                        <div v-if="task.notes" class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Additional Notes</h2>
                            <p class="text-gray-700 whitespace-pre-wrap">{{ task.notes }}</p>
                        </div>

                        <!-- Comments/Activity -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Activity</h2>
                            <div v-if="task.comments.length > 0" class="space-y-4">
                                <div
                                    v-for="comment in task.comments"
                                    :key="comment.id"
                                    class="flex gap-3 p-3 bg-gray-50 rounded-lg"
                                >
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                            <UserIcon class="h-4 w-4 text-blue-600" aria-hidden="true" />
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-gray-900">{{ comment.author }}</span>
                                            <span class="text-xs text-gray-500">{{ comment.created_at }}</span>
                                        </div>
                                        <p class="text-gray-700 mt-1">{{ comment.content }}</p>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-gray-400 italic">No activity yet</p>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Details -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Details</h2>
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-sm text-gray-500">Assigned To</dt>
                                    <dd class="mt-1">
                                        <div v-if="task.assignee" class="flex items-center gap-2">
                                            <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                                <UserIcon class="h-4 w-4 text-gray-600" aria-hidden="true" />
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900">{{ task.assignee.name }}</p>
                                                <p class="text-xs text-gray-500">{{ task.assignee.email }}</p>
                                            </div>
                                        </div>
                                        <span v-else class="text-gray-400">Unassigned</span>
                                    </dd>
                                </div>
                                <div v-if="task.assigner">
                                    <dt class="text-sm text-gray-500">Assigned By</dt>
                                    <dd class="mt-1 font-medium text-gray-900">{{ task.assigner.name }}</dd>
                                </div>
                                <div v-if="task.department">
                                    <dt class="text-sm text-gray-500">Department</dt>
                                    <dd class="mt-1 font-medium text-gray-900">{{ task.department }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm text-gray-500">Due Date</dt>
                                    <dd class="mt-1 flex items-center gap-2">
                                        <CalendarIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                                        <span :class="task.is_overdue ? 'text-red-600 font-medium' : 'text-gray-900'">
                                            {{ task.due_date || 'No due date' }}
                                        </span>
                                    </dd>
                                </div>
                                <div v-if="task.estimated_hours">
                                    <dt class="text-sm text-gray-500">Estimated Hours</dt>
                                    <dd class="mt-1 flex items-center gap-2">
                                        <ClockIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                                        <span class="text-gray-900">{{ task.estimated_hours }} hours</span>
                                    </dd>
                                </div>
                                <div v-if="task.actual_hours">
                                    <dt class="text-sm text-gray-500">Actual Hours</dt>
                                    <dd class="mt-1 text-gray-900">{{ task.actual_hours }} hours</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Timeline -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h2>
                            <dl class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Created</dt>
                                    <dd class="text-gray-900">{{ task.created_at }}</dd>
                                </div>
                                <div v-if="task.started_at" class="flex justify-between">
                                    <dt class="text-gray-500">Started</dt>
                                    <dd class="text-gray-900">{{ task.started_at }}</dd>
                                </div>
                                <div v-if="task.completed_at" class="flex justify-between">
                                    <dt class="text-gray-500">Completed</dt>
                                    <dd class="text-green-600">{{ task.completed_at }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Last Updated</dt>
                                    <dd class="text-gray-900">{{ task.updated_at }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Tags -->
                        <div v-if="task.tags && task.tags.length > 0" class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Tags</h2>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="tag in task.tags"
                                    :key="tag"
                                    class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-sm"
                                >
                                    <TagIcon class="h-3 w-3 mr-1" aria-hidden="true" />
                                    {{ tag }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
