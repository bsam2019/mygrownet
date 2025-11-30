<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import { ref } from 'vue';
import {
    ArrowLeftIcon,
    ClockIcon,
    UserIcon,
    FolderIcon,
    TagIcon,
    PaperClipIcon,
    ChatBubbleLeftIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';

interface Comment {
    id: number;
    content: string;
    created_at: string;
    employee: { full_name: string };
}

interface Task {
    id: number;
    title: string;
    description: string;
    priority: string;
    status: string;
    due_date: string;
    started_at: string;
    completed_at: string;
    estimated_hours: number;
    actual_hours: number;
    tags: string[];
    notes: string;
    assigner?: { full_name: string };
    department?: { name: string };
    comments: Comment[];
    attachments: any[];
}

interface Props {
    task: Task;
}

const props = defineProps<Props>();

const commentForm = useForm({
    content: '',
});

const submitComment = () => {
    commentForm.post(route('employee.portal.tasks.add-comment', props.task.id), {
        preserveScroll: true,
        onSuccess: () => {
            commentForm.reset();
        },
    });
};

const updateStatus = (newStatus: string) => {
    router.patch(route('employee.portal.tasks.update-status', props.task.id), {
        status: newStatus,
    }, { preserveScroll: true });
};

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

const isOverdue = () => {
    if (!props.task.due_date || ['completed', 'cancelled'].includes(props.task.status)) return false;
    return new Date(props.task.due_date) < new Date();
};

const formatDate = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-US', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const formatDateTime = (dateString: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head :title="task.title" />

    <EmployeePortalLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link :href="route('employee.portal.tasks.index')"
                    class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
                    aria-label="Back to tasks">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                </Link>
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-gray-900">{{ task.title }}</h1>
                        <span :class="getPriorityColor(task.priority)"
                            class="px-2 py-1 text-xs font-medium rounded-full">
                            {{ task.priority }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Description -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h2 class="font-semibold text-gray-900 mb-4">Description</h2>
                        <p v-if="task.description" class="text-gray-600 whitespace-pre-wrap">
                            {{ task.description }}
                        </p>
                        <p v-else class="text-gray-400 italic">No description provided</p>
                    </div>

                    <!-- Status Actions -->
                    <div v-if="task.status !== 'completed' && task.status !== 'cancelled'"
                        class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h2 class="font-semibold text-gray-900 mb-4">Update Status</h2>
                        <div class="flex flex-wrap gap-3">
                            <button v-if="task.status === 'todo'"
                                @click="updateStatus('in_progress')"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                Start Working
                            </button>
                            <button v-if="task.status === 'in_progress'"
                                @click="updateStatus('review')"
                                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                                Submit for Review
                            </button>
                            <button v-if="['in_progress', 'review'].includes(task.status)"
                                @click="updateStatus('completed')"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                Mark Complete
                            </button>
                            <button v-if="task.status !== 'todo'"
                                @click="updateStatus('todo')"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                Move to To Do
                            </button>
                        </div>
                    </div>

                    <!-- Comments -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h2 class="font-semibold text-gray-900 mb-4">
                            Comments ({{ task.comments?.length || 0 }})
                        </h2>

                        <!-- Comment Form -->
                        <form @submit.prevent="submitComment" class="mb-6">
                            <textarea v-model="commentForm.content"
                                rows="3"
                                placeholder="Add a comment..."
                                class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </textarea>
                            <div class="flex justify-end mt-2">
                                <button type="submit"
                                    :disabled="commentForm.processing || !commentForm.content.trim()"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 transition-colors">
                                    {{ commentForm.processing ? 'Posting...' : 'Post Comment' }}
                                </button>
                            </div>
                        </form>

                        <!-- Comments List -->
                        <div class="space-y-4">
                            <div v-for="comment in task.comments" :key="comment.id"
                                class="flex gap-3 p-4 bg-gray-50 rounded-lg">
                                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                    <span class="text-sm font-medium text-blue-600">
                                        {{ comment.employee?.full_name?.[0] || '?' }}
                                    </span>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-gray-900">
                                            {{ comment.employee?.full_name }}
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            {{ formatDateTime(comment.created_at) }}
                                        </span>
                                    </div>
                                    <p class="text-gray-600 mt-1">{{ comment.content }}</p>
                                </div>
                            </div>

                            <div v-if="!task.comments?.length" class="text-center py-8 text-gray-400">
                                <ChatBubbleLeftIcon class="h-8 w-8 mx-auto mb-2" aria-hidden="true" />
                                <p>No comments yet</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Status Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h2 class="font-semibold text-gray-900 mb-4">Details</h2>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500">Status</p>
                                <span :class="getStatusColor(task.status)"
                                    class="inline-block mt-1 px-3 py-1 text-sm font-medium rounded-full">
                                    {{ task.status.replace('_', ' ') }}
                                </span>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">Due Date</p>
                                <p :class="isOverdue() ? 'text-red-600' : 'text-gray-900'" class="font-medium mt-1">
                                    {{ formatDate(task.due_date) }}
                                    <ExclamationTriangleIcon v-if="isOverdue()" class="inline h-4 w-4 ml-1" aria-hidden="true" />
                                </p>
                            </div>

                            <div v-if="task.assigner">
                                <p class="text-sm text-gray-500">Assigned By</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <UserIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                                    <span class="text-gray-900">{{ task.assigner.full_name }}</span>
                                </div>
                            </div>

                            <div v-if="task.department">
                                <p class="text-sm text-gray-500">Department</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <FolderIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                                    <span class="text-gray-900">{{ task.department.name }}</span>
                                </div>
                            </div>

                            <div v-if="task.estimated_hours">
                                <p class="text-sm text-gray-500">Estimated Time</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <ClockIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                                    <span class="text-gray-900">{{ task.estimated_hours }}h</span>
                                </div>
                            </div>

                            <div v-if="task.started_at">
                                <p class="text-sm text-gray-500">Started</p>
                                <p class="text-gray-900 mt-1">{{ formatDateTime(task.started_at) }}</p>
                            </div>

                            <div v-if="task.completed_at">
                                <p class="text-sm text-gray-500">Completed</p>
                                <p class="text-gray-900 mt-1">{{ formatDateTime(task.completed_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tags -->
                    <div v-if="task.tags?.length" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h2 class="font-semibold text-gray-900 mb-4">Tags</h2>
                        <div class="flex flex-wrap gap-2">
                            <span v-for="tag in task.tags" :key="tag"
                                class="px-2 py-1 bg-gray-100 text-gray-700 text-sm rounded-full">
                                {{ tag }}
                            </span>
                        </div>
                    </div>

                    <!-- Attachments -->
                    <div v-if="task.attachments?.length" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h2 class="font-semibold text-gray-900 mb-4">Attachments</h2>
                        <div class="space-y-2">
                            <a v-for="attachment in task.attachments" :key="attachment.id"
                                :href="attachment.url"
                                target="_blank"
                                class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded-lg transition-colors">
                                <PaperClipIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                                <span class="text-sm text-blue-600 hover:text-blue-700">
                                    {{ attachment.name }}
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
