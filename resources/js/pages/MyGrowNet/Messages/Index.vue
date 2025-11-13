<template>
    <AppLayout title="Messages">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Messages</h1>
                        <p class="mt-1 text-sm text-gray-600">
                            Communicate with your network and support team
                        </p>
                    </div>
                    <button
                        @click="showComposeModal = true"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Message
                    </button>
                </div>

                <!-- Tabs -->
                <div class="bg-white rounded-lg shadow">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                            <Link
                                :href="route('mygrownet.messages.index', { tab: 'inbox' })"
                                :class="[
                                    tab === 'inbox'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                                ]"
                            >
                                Inbox
                                <span v-if="unreadCount > 0" class="ml-2 bg-blue-100 text-blue-600 py-0.5 px-2 rounded-full text-xs font-medium">
                                    {{ unreadCount }}
                                </span>
                            </Link>
                            <Link
                                :href="route('mygrownet.messages.index', { tab: 'sent' })"
                                :class="[
                                    tab === 'sent'
                                        ? 'border-blue-500 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                    'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                                ]"
                            >
                                Sent
                            </Link>
                        </nav>
                    </div>

                    <!-- Messages List -->
                    <div class="divide-y divide-gray-200">
                        <div
                            v-if="messages.length === 0"
                            class="px-6 py-12 text-center text-gray-500"
                        >
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <p class="mt-2">No messages yet</p>
                        </div>

                        <Link
                            v-for="message in messages"
                            :key="message.id"
                            :href="route('mygrownet.messages.show', message.id)"
                            class="block px-6 py-4 hover:bg-gray-50 transition"
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
        <ComposeMessageModal
            :show="showComposeModal"
            @close="showComposeModal = false"
        />
    </AppLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import ComposeMessageModal from '@/Components/Messaging/ComposeMessageModal.vue';

const showComposeModal = ref(false);

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

const props = defineProps<{
    messages: Message[];
    tab: 'inbox' | 'sent';
}>();

const unreadCount = computed(() => {
    return props.messages.filter(m => !m.isRead).length;
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
