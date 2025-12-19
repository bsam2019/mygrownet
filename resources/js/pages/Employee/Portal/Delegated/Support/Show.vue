<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/layouts/EmployeePortalLayout.vue';
import {
    ArrowLeftIcon,
    ChatBubbleLeftRightIcon,
    UserIcon,
    ClockIcon,
    PaperAirplaneIcon,
} from '@heroicons/vue/24/outline';

interface Message {
    id: number;
    message: string;
    is_staff_reply: boolean;
    created_at: string;
    user?: { id: number; name: string };
}

interface Ticket {
    id: number;
    ticket_number: string;
    subject: string;
    description?: string;
    status: string;
    priority: string;
    created_at: string;
    updated_at: string;
    user?: { id: number; name: string; email: string };
    investor?: { id: number; name: string; email: string };
    messages?: Message[];
}

interface Props {
    employee: { id: number; full_name: string };
    ticket: Ticket;
    source: 'member' | 'investor';
}

const props = defineProps<Props>();

const form = useForm({
    message: '',
});

const submitReply = () => {
    form.post(route('employee.portal.delegated.support.reply', { source: props.source, ticketId: props.ticket.id }), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        open: 'bg-blue-100 text-blue-800',
        in_progress: 'bg-amber-100 text-amber-800',
        resolved: 'bg-green-100 text-green-800',
        closed: 'bg-gray-100 text-gray-800',
    };
    return colors[status] || colors.open;
};

const getPriorityColor = (priority: string) => {
    const colors: Record<string, string> = {
        low: 'bg-gray-100 text-gray-800',
        medium: 'bg-blue-100 text-blue-800',
        high: 'bg-amber-100 text-amber-800',
        urgent: 'bg-red-100 text-red-800',
    };
    return colors[priority] || colors.medium;
};

const ticketUser = props.ticket.user || props.ticket.investor || { name: 'Unknown', email: '' };
</script>

<template>
    <EmployeePortalLayout>
        <Head :title="`Ticket: ${ticket.ticket_number}`" />

        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Back Button -->
            <Link
                :href="route('employee.portal.delegated.support.index')"
                class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900"
            >
                <ArrowLeftIcon class="h-4 w-4" aria-hidden="true" />
                Back to Support Center
            </Link>

            <!-- Ticket Header -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="text-white">
                            <p class="text-sm text-purple-200 font-mono">{{ ticket.ticket_number }}</p>
                            <h1 class="text-xl font-bold mt-1">{{ ticket.subject }}</h1>
                        </div>
                        <div class="flex items-center gap-2">
                            <span :class="[
                                'px-3 py-1 text-xs font-medium rounded-full',
                                getStatusColor(ticket.status)
                            ]">
                                {{ ticket.status.replace('_', ' ') }}
                            </span>
                            <span :class="[
                                'px-3 py-1 text-xs font-medium rounded-full',
                                getPriorityColor(ticket.priority)
                            ]">
                                {{ ticket.priority }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-gray-100 rounded-lg">
                            <UserIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">{{ source === 'member' ? 'Member' : 'Investor' }}</p>
                            <p class="font-medium text-gray-900">{{ ticketUser.name }}</p>
                            <p class="text-sm text-gray-500">{{ ticketUser.email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-gray-100 rounded-lg">
                            <ClockIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Created</p>
                            <p class="font-medium text-gray-900">{{ formatDate(ticket.created_at) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Original Description -->
                <div v-if="ticket.description" class="p-6 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">Description</h3>
                    <p class="text-gray-700 whitespace-pre-wrap">{{ ticket.description }}</p>
                </div>
            </div>

            <!-- Messages -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                        <ChatBubbleLeftRightIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        Conversation
                    </h2>
                </div>

                <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                    <div
                        v-for="message in ticket.messages"
                        :key="message.id"
                        :class="[
                            'p-4',
                            message.is_staff_reply ? 'bg-purple-50' : 'bg-white'
                        ]"
                    >
                        <div class="flex items-start gap-3">
                            <div :class="[
                                'h-8 w-8 rounded-full flex items-center justify-center text-sm font-medium',
                                message.is_staff_reply ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-600'
                            ]">
                                {{ (message.user?.name || ticketUser.name).charAt(0).toUpperCase() }}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-medium text-gray-900">
                                        {{ message.is_staff_reply ? (message.user?.name || 'Staff') : ticketUser.name }}
                                    </span>
                                    <span v-if="message.is_staff_reply" class="px-2 py-0.5 text-xs font-medium bg-purple-100 text-purple-700 rounded-full">
                                        Staff
                                    </span>
                                    <span class="text-xs text-gray-500">{{ formatDate(message.created_at) }}</span>
                                </div>
                                <p class="text-gray-700 whitespace-pre-wrap">{{ message.message }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="!ticket.messages?.length" class="p-8 text-center text-gray-500">
                        <ChatBubbleLeftRightIcon class="h-12 w-12 mx-auto mb-3 text-gray-300" aria-hidden="true" />
                        <p>No messages yet</p>
                    </div>
                </div>

                <!-- Reply Form -->
                <div v-if="ticket.status !== 'closed'" class="p-4 border-t border-gray-200 bg-gray-50">
                    <form @submit.prevent="submitReply">
                        <div class="mb-3">
                            <label for="reply" class="sr-only">Your reply</label>
                            <textarea
                                id="reply"
                                v-model="form.message"
                                rows="3"
                                placeholder="Type your reply..."
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 resize-none"
                                :disabled="form.processing"
                            ></textarea>
                            <p v-if="form.errors.message" class="mt-1 text-sm text-red-600">{{ form.errors.message }}</p>
                        </div>
                        <div class="flex justify-end">
                            <button
                                type="submit"
                                :disabled="form.processing || !form.message.trim()"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                <PaperAirplaneIcon class="h-4 w-4" aria-hidden="true" />
                                {{ form.processing ? 'Sending...' : 'Send Reply' }}
                            </button>
                        </div>
                    </form>
                </div>

                <div v-else class="p-4 border-t border-gray-200 bg-gray-100 text-center text-gray-500">
                    This ticket is closed and cannot receive new replies.
                </div>
            </div>

            <!-- Access Notice -->
            <div class="bg-purple-50 rounded-xl p-4 text-sm text-purple-700">
                <p class="font-medium">Delegated Access Notice</p>
                <p>You are handling this support ticket through delegated permissions. All actions are logged for security purposes.</p>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
