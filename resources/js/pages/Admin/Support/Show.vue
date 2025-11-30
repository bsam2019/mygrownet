<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import {
    ArrowLeftIcon,
    ChatBubbleLeftRightIcon,
    UserIcon,
    PaperAirplaneIcon,
    CheckCircleIcon,
} from '@heroicons/vue/24/outline';

interface Comment {
    id: number;
    content: string;
    author_type: 'employee' | 'support';
    is_internal: boolean;
    created_at: string;
    author?: { id: number; full_name: string };
}

interface Ticket {
    id: number;
    ticket_number: string;
    subject: string;
    description: string;
    category: string;
    priority: string;
    status: string;
    created_at: string;
    updated_at: string;
    resolved_at?: string;
    employee: {
        id: number;
        full_name: string;
        email: string;
        phone?: string;
        department?: { name: string };
        position?: { title: string };
    };
    assigned_to?: { id: number; full_name: string };
    comments: Comment[];
}

interface Props {
    ticket: Ticket;
    supportAgents: Array<{ id: number; first_name: string; last_name: string }>;
}

const props = defineProps<Props>();

const updateForm = useForm({
    status: props.ticket.status,
    priority: props.ticket.priority,
    assigned_to: props.ticket.assigned_to?.id || null,
});

const commentForm = useForm({ content: '', is_internal: false });

const updateTicket = () => {
    updateForm.patch(route('admin.support.update', props.ticket.id), { preserveScroll: true });
};

const addComment = () => {
    commentForm.post(route('admin.support.comment', props.ticket.id), {
        preserveScroll: true,
        onSuccess: () => commentForm.reset('content'),
    });
};

const getStatusColor = (s: string) => ({
    open: 'bg-blue-100 text-blue-700',
    in_progress: 'bg-amber-100 text-amber-700',
    pending: 'bg-purple-100 text-purple-700',
    resolved: 'bg-green-100 text-green-700',
    closed: 'bg-gray-100 text-gray-700',
}[s] || 'bg-gray-100 text-gray-700');

const getPriorityColor = (p: string) => ({
    low: 'bg-gray-100 text-gray-600',
    medium: 'bg-blue-100 text-blue-600',
    high: 'bg-amber-100 text-amber-600',
    urgent: 'bg-red-100 text-red-600',
}[p] || 'bg-gray-100 text-gray-600');

const formatDate = (date: string) => new Date(date).toLocaleString();
</script>

<template>
    <Head :title="`Ticket #${ticket.ticket_number}`" />
    <AdminLayout>
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <Link :href="route('admin.support.index')" class="p-2 hover:bg-gray-100 rounded-lg">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                </Link>
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold text-gray-900">#{{ ticket.ticket_number }}</h1>
                        <span :class="[getStatusColor(ticket.status), 'px-3 py-1 text-sm font-medium rounded-full']">
                            {{ ticket.status.replace('_', ' ') }}
                        </span>
                    </div>
                    <p class="text-gray-500 mt-1">{{ ticket.subject }}</p>
                </div>
                <Link :href="route('admin.support.live-chat', ticket.id)" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <ChatBubbleLeftRightIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                    Live Chat
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Description</h2>
                        <div class="prose prose-sm max-w-none text-gray-600">{{ ticket.description }}</div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="p-4 border-b border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900">Conversation</h2>
                        </div>
                        <div class="p-4 space-y-4 max-h-96 overflow-y-auto">
                            <div v-for="comment in ticket.comments" :key="comment.id"
                                :class="['p-4 rounded-lg', comment.author_type === 'support' ? 'bg-blue-50 ml-8' : 'bg-gray-50 mr-8', comment.is_internal ? 'border-2 border-dashed border-amber-300' : '']">
                                <div class="flex items-center gap-2 mb-2">
                                    <div :class="['h-8 w-8 rounded-full flex items-center justify-center', comment.author_type === 'support' ? 'bg-blue-200' : 'bg-gray-200']">
                                        <UserIcon class="h-4 w-4" :class="comment.author_type === 'support' ? 'text-blue-600' : 'text-gray-600'" aria-hidden="true" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ comment.author?.full_name || 'Support' }}</p>
                                        <p class="text-xs text-gray-500">{{ formatDate(comment.created_at) }}</p>
                                    </div>
                                    <span v-if="comment.is_internal" class="ml-auto text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded">Internal</span>
                                </div>
                                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ comment.content }}</p>
                            </div>
                            <div v-if="ticket.comments.length === 0" class="text-center py-8 text-gray-500">No comments yet</div>
                        </div>
                        <div class="p-4 border-t border-gray-100">
                            <form @submit.prevent="addComment">
                                <textarea v-model="commentForm.content" rows="3" placeholder="Write a reply..." class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"></textarea>
                                <div class="flex items-center justify-between mt-3">
                                    <label class="flex items-center gap-2 text-sm text-gray-600">
                                        <input type="checkbox" v-model="commentForm.is_internal" class="rounded border-gray-300 text-blue-600" />
                                        Internal note
                                    </label>
                                    <button type="submit" :disabled="!commentForm.content || commentForm.processing" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                        <PaperAirplaneIcon class="h-4 w-4 mr-2" aria-hidden="true" />Send
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Submitted By</h3>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center">
                                <UserIcon class="h-6 w-6 text-gray-500" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ ticket.employee.full_name }}</p>
                                <p class="text-sm text-gray-500">{{ ticket.employee.position?.title }}</p>
                            </div>
                        </div>
                        <div class="space-y-2 text-sm">
                            <p><span class="text-gray-500">Dept:</span> {{ ticket.employee.department?.name }}</p>
                            <p><span class="text-gray-500">Email:</span> {{ ticket.employee.email }}</p>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Update Ticket</h3>
                        <form @submit.prevent="updateTicket" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select v-model="updateForm.status" class="w-full border-gray-300 rounded-lg">
                                    <option value="open">Open</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="pending">Pending</option>
                                    <option value="resolved">Resolved</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                                <select v-model="updateForm.priority" class="w-full border-gray-300 rounded-lg">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
                                <select v-model="updateForm.assigned_to" class="w-full border-gray-300 rounded-lg">
                                    <option :value="null">Unassigned</option>
                                    <option v-for="agent in supportAgents" :key="agent.id" :value="agent.id">{{ agent.first_name }} {{ agent.last_name }}</option>
                                </select>
                            </div>
                            <button type="submit" :disabled="updateForm.processing" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                <CheckCircleIcon class="h-4 w-4 inline mr-2" aria-hidden="true" />Update
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
