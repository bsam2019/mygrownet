<template>
    <AdminLayout title="Messages">
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg">
                                    <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900">Messages</h1>
                                <p class="mt-1 text-sm text-gray-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                                    </svg>
                                    Communicate with members and staff
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <Link
                                :href="route('admin.messages.broadcast')"
                                class="inline-flex items-center px-6 py-3 border border-transparent rounded-xl shadow-lg text-sm font-semibold text-white bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200 transform hover:scale-105"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                                </svg>
                                Broadcast
                            </Link>
                            <button
                                @click="showComposeModal = true"
                                class="inline-flex items-center px-6 py-3 border border-transparent rounded-xl shadow-lg text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Compose Message
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 border-2 border-blue-200 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-blue-600">Unread Messages</p>
                                <p class="text-3xl font-bold text-blue-900 mt-2">{{ unreadCount }}</p>
                            </div>
                            <div class="h-12 w-12 rounded-xl bg-blue-500 flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-6 border-2 border-green-200 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-green-600">Total Messages</p>
                                <p class="text-3xl font-bold text-green-900 mt-2">{{ messages.length }}</p>
                            </div>
                            <div class="h-12 w-12 rounded-xl bg-green-500 flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl p-6 border-2 border-purple-200 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-purple-600">Active Conversations</p>
                                <p class="text-3xl font-bold text-purple-900 mt-2">{{ activeConversations }}</p>
                            </div>
                            <div class="h-12 w-12 rounded-xl bg-purple-500 flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages Container -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                    <!-- Tabs -->
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 border-b-2 border-gray-200">
                        <nav class="flex space-x-1 px-6 py-2" aria-label="Tabs">
                            <Link
                                :href="route('admin.messages.index', { tab: 'inbox' })"
                                :class="[
                                    tab === 'inbox'
                                        ? 'bg-white text-blue-600 shadow-sm'
                                        : 'text-gray-600 hover:text-gray-900 hover:bg-white/50',
                                    'flex items-center px-6 py-3 rounded-xl font-semibold text-sm transition-all duration-200'
                                ]"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                Inbox
                                <span v-if="unreadCount > 0" class="ml-2 bg-blue-500 text-white py-1 px-2.5 rounded-full text-xs font-bold shadow-sm">
                                    {{ unreadCount }}
                                </span>
                            </Link>
                            <Link
                                :href="route('admin.messages.index', { tab: 'sent' })"
                                :class="[
                                    tab === 'sent'
                                        ? 'bg-white text-blue-600 shadow-sm'
                                        : 'text-gray-600 hover:text-gray-900 hover:bg-white/50',
                                    'flex items-center px-6 py-3 rounded-xl font-semibold text-sm transition-all duration-200'
                                ]"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                Sent
                            </Link>
                        </nav>
                    </div>

                    <!-- Messages List -->
                    <div class="divide-y divide-gray-100">
                        <div
                            v-if="messages.length === 0"
                            class="px-6 py-16 text-center"
                        >
                            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 mb-4">
                                <svg class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <p class="text-lg font-semibold text-gray-900">No messages yet</p>
                            <p class="text-sm text-gray-500 mt-1">Start a conversation by composing a new message</p>
                            <button
                                @click="showComposeModal = true"
                                class="mt-6 inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 transition-colors"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Compose Message
                            </button>
                        </div>

                        <Link
                            v-for="message in messages"
                            :key="message.id"
                            :href="route('admin.messages.show', message.id)"
                            class="block px-6 py-5 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center space-x-3">
                                        <p :class="[
                                            'text-sm font-medium',
                                            !message.isRead && tab === 'inbox' ? 'text-gray-900' : 'text-gray-600'
                                        ]">
                                            {{ tab === 'inbox' ? message.senderName : message.recipientName }}
                                        </p>
                                        <span
                                            v-if="!message.isRead && tab === 'inbox'"
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800"
                                        >
                                            New
                                        </span>
                                    </div>
                                    <p :class="[
                                        'mt-1 text-sm',
                                        !message.isRead && tab === 'inbox' ? 'font-semibold text-gray-900' : 'text-gray-600'
                                    ]">
                                        {{ message.subject }}
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500 line-clamp-2">
                                        {{ message.preview }}
                                    </p>
                                </div>
                                <div class="ml-4 flex-shrink-0 text-sm text-gray-500">
                                    {{ formatDate(message.createdAt) }}
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compose Message Modal -->
        <AdminComposeMessageModal
            :show="showComposeModal"
            :users="users"
            @close="showComposeModal = false"
        />
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import AdminComposeMessageModal from '@/Components/Messaging/AdminComposeMessageModal.vue';
import Swal from 'sweetalert2';

interface Message {
    id: number;
    senderId: number;
    senderName: string;
    recipientId: number;
    recipientName: string;
    subject: string;
    body: string;
    preview: string;
    isRead: boolean;
    readAt: string | null;
    parentId: number | null;
    createdAt: string;
    updatedAt: string;
}

interface User {
    id: number;
    name: string;
    email: string;
}

const props = withDefaults(defineProps<{
    messages: Message[];
    tab: 'inbox' | 'sent';
    users?: User[];
}>(), {
    users: () => []
});

const showComposeModal = ref(false);

const page = usePage();

const unreadCount = computed(() => {
    return props.messages.filter(m => !m.isRead).length;
});

// Show success toast if there's a success message
onMounted(() => {
    const flash = page.props.flash as any;
    if (flash?.success) {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: flash.success,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    }
});

function formatDate(dateString: string): string {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    
    return date.toLocaleDateString();
}
</script>
