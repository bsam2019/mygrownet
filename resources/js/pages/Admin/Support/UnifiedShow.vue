<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';
import {
    ArrowLeftIcon,
    ChatBubbleLeftRightIcon,
    ClockIcon,
    UserIcon,
    BuildingOfficeIcon,
    UserGroupIcon,
    PaperAirplaneIcon,
} from '@heroicons/vue/24/outline';

interface Comment {
    id: number;
    author_name: string;
    author_type: 'user' | 'support';
    content: string;
    created_at: string;
}

interface Agent {
    id: number;
    name: string;
    email: string;
    role: string;
}

interface Ticket {
    id: number;
    source: 'employee' | 'member' | 'investor';
    ticket_number: string;
    subject: string;
    description: string;
    category: string;
    priority: string;
    status: string;
    created_at: string;
    updated_at: string;
    assigned_to?: number;
    assigned_agent?: Agent;
    rating?: number;
    requester: {
        name: string;
        type: string;
        email?: string;
        department?: string;
    };
    comments: Comment[];
}

const props = defineProps<{ ticket: Ticket; agents?: Agent[] }>();

const replyForm = useForm({ message: '' });
const statusForm = useForm({ status: props.ticket.status, priority: props.ticket.priority });
const assignForm = useForm({ agent_id: props.ticket.assigned_to || '' });

const sendReply = () => {
    if (!replyForm.message.trim()) return;
    
    replyForm.post(route('admin.unified-support.reply', { source: props.ticket.source, id: props.ticket.id }), {
        preserveScroll: true,
        onSuccess: () => replyForm.reset(),
    });
};

const updateTicket = () => {
    statusForm.patch(route('admin.unified-support.update', { source: props.ticket.source, id: props.ticket.id }), {
        preserveScroll: true,
    });
};

const assignTicket = () => {
    assignForm.post(route('admin.unified-support.assign', { source: props.ticket.source, id: props.ticket.id }), {
        preserveScroll: true,
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
    urgent: 'bg-red-100 text-red-700',
    high: 'bg-orange-100 text-orange-700',
    medium: 'bg-yellow-100 text-yellow-700',
    low: 'bg-gray-100 text-gray-700',
}[p] || 'bg-gray-100 text-gray-700');

const formatDate = (dateString: string) => new Date(dateString).toLocaleString();
</script>

<template>
    <Head :title="`Ticket ${ticket.ticket_number}`" />
    <AdminLayout>
        <div class="py-6">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 flex items-center gap-4">
                    <Link :href="route('admin.unified-support.index')" class="p-2 hover:bg-gray-100 rounded-lg">
                        <ArrowLeftIcon class="h-5 w-5 text-gray-600" />
                    </Link>
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold text-gray-900">{{ ticket.ticket_number }}</h1>
                            <span :class="[
                                ticket.source === 'employee' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700',
                                'px-2 py-1 text-xs font-medium rounded-full flex items-center gap-1'
                            ]">
                                <component :is="ticket.source === 'employee' ? BuildingOfficeIcon : UserGroupIcon" class="h-3 w-3" />
                                {{ ticket.source === 'employee' ? 'Employee' : 'Member' }}
                            </span>
                            <span :class="[getStatusColor(ticket.status), 'px-2 py-1 text-xs font-medium rounded-full']">
                                {{ ticket.status.replace('_', ' ') }}
                            </span>
                            <span :class="[getPriorityColor(ticket.priority), 'px-2 py-1 text-xs font-medium rounded-full']">
                                {{ ticket.priority }}
                            </span>
                        </div>
                        <p class="text-gray-600 mt-1">{{ ticket.subject }}</p>
                    </div>
                    <Link
                        :href="route('admin.unified-support.live-chat', { source: ticket.source, id: ticket.id })"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2"
                    >
                        <ChatBubbleLeftRightIcon class="h-5 w-5" />
                        Live Chat
                    </Link>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Original Request -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Original Request</h2>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-gray-700 whitespace-pre-wrap">{{ ticket.description }}</p>
                                <p class="text-xs text-gray-500 mt-3">{{ formatDate(ticket.created_at) }}</p>
                            </div>
                        </div>

                        <!-- Conversation -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Conversation</h2>
                            
                            <div v-if="ticket.comments.length === 0" class="text-center py-8 text-gray-500">
                                <ChatBubbleLeftRightIcon class="h-12 w-12 mx-auto mb-3 text-gray-300" />
                                <p>No replies yet</p>
                            </div>

                            <div v-else class="space-y-4">
                                <div
                                    v-for="comment in ticket.comments"
                                    :key="comment.id"
                                    :class="[
                                        'p-4 rounded-lg',
                                        comment.author_type === 'support' ? 'bg-blue-50 ml-8' : 'bg-gray-50 mr-8'
                                    ]"
                                >
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="font-medium text-gray-900">{{ comment.author_name }}</span>
                                        <span :class="[
                                            comment.author_type === 'support' ? 'bg-blue-100 text-blue-700' : 'bg-gray-200 text-gray-700',
                                            'px-2 py-0.5 text-xs rounded-full'
                                        ]">
                                            {{ comment.author_type === 'support' ? 'Support' : 'User' }}
                                        </span>
                                    </div>
                                    <p class="text-gray-700 whitespace-pre-wrap">{{ comment.content }}</p>
                                    <p class="text-xs text-gray-500 mt-2">{{ formatDate(comment.created_at) }}</p>
                                </div>
                            </div>

                            <!-- Reply Form -->
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <h3 class="text-sm font-medium text-gray-900 mb-3">Send Reply</h3>
                                <textarea
                                    v-model="replyForm.message"
                                    rows="4"
                                    placeholder="Type your reply..."
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                ></textarea>
                                <div class="mt-3 flex justify-end">
                                    <button
                                        @click="sendReply"
                                        :disabled="!replyForm.message.trim() || replyForm.processing"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center gap-2"
                                    >
                                        <PaperAirplaneIcon class="h-4 w-4" />
                                        Send Reply
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Requester Info -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Requester</h2>
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                        <UserIcon class="h-5 w-5 text-gray-600" />
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ ticket.requester.name }}</p>
                                        <p class="text-sm text-gray-500">{{ ticket.requester.type }}</p>
                                    </div>
                                </div>
                                <div v-if="ticket.requester.email" class="text-sm text-gray-600">
                                    {{ ticket.requester.email }}
                                </div>
                                <div v-if="ticket.requester.department" class="text-sm text-gray-600">
                                    {{ ticket.requester.department }}
                                </div>
                            </div>
                        </div>

                        <!-- Update Status -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Update Ticket</h2>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                    <select v-model="statusForm.status" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                        <option value="open">Open</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="pending">Pending</option>
                                        <option value="resolved">Resolved</option>
                                        <option value="closed">Closed</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                                    <select v-model="statusForm.priority" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                                <button
                                    @click="updateTicket"
                                    :disabled="statusForm.processing"
                                    class="w-full px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 disabled:opacity-50"
                                >
                                    Update Ticket
                                </button>
                            </div>
                        </div>

                        <!-- Assign Agent -->
                        <div v-if="agents && agents.length > 0" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Assign Agent</h2>
                            <div class="space-y-4">
                                <div v-if="ticket.assigned_agent" class="flex items-center gap-3 p-3 bg-blue-50 rounded-lg mb-3">
                                    <div class="w-8 h-8 bg-blue-200 rounded-full flex items-center justify-center">
                                        <UserIcon class="h-4 w-4 text-blue-600" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ ticket.assigned_agent.name }}</p>
                                        <p class="text-xs text-gray-500">Currently assigned</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Agent</label>
                                    <select v-model="assignForm.agent_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                        <option value="">Unassigned</option>
                                        <option v-for="agent in agents" :key="agent.id" :value="agent.id">
                                            {{ agent.name }} ({{ agent.role }})
                                        </option>
                                    </select>
                                </div>
                                <button
                                    @click="assignTicket"
                                    :disabled="assignForm.processing"
                                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                                >
                                    {{ ticket.assigned_to ? 'Reassign' : 'Assign' }} Ticket
                                </button>
                            </div>
                        </div>

                        <!-- Ticket Details -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Details</h2>
                            <dl class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Category</dt>
                                    <dd class="text-gray-900 font-medium">{{ ticket.category }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Created</dt>
                                    <dd class="text-gray-900">{{ formatDate(ticket.created_at) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Updated</dt>
                                    <dd class="text-gray-900">{{ formatDate(ticket.updated_at) }}</dd>
                                </div>
                                <div v-if="ticket.rating" class="flex justify-between">
                                    <dt class="text-gray-500">Rating</dt>
                                    <dd class="text-gray-900 flex items-center gap-1">
                                        <span v-for="i in 5" :key="i" :class="i <= ticket.rating ? 'text-yellow-400' : 'text-gray-300'">★</span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
