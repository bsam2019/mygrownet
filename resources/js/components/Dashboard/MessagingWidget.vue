<template>
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <h3 class="text-lg font-semibold text-gray-900">Messages</h3>
                <span
                    v-if="messagingData.unread_count > 0"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                >
                    {{ messagingData.unread_count }} new
                </span>
            </div>
            <Link
                :href="route('mygrownet.messages.index')"
                class="text-sm font-medium text-blue-600 hover:text-blue-700"
            >
                View All
            </Link>
        </div>

        <div v-if="messagingData.has_messages" class="divide-y divide-gray-200">
            <Link
                v-for="message in messagingData.recent_messages"
                :key="message.id"
                :href="route('mygrownet.messages.show', message.id)"
                class="block px-6 py-4 hover:bg-gray-50 transition"
            >
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center space-x-2">
                            <p :class="[
                                'text-sm font-medium',
                                !message.is_read ? 'text-gray-900' : 'text-gray-600'
                            ]">
                                {{ message.sender_name }}
                            </p>
                            <span
                                v-if="!message.is_read"
                                class="inline-flex h-2 w-2 rounded-full bg-blue-600"
                            ></span>
                        </div>
                        <p :class="[
                            'mt-1 text-sm',
                            !message.is_read ? 'font-semibold text-gray-900' : 'text-gray-600'
                        ]">
                            {{ message.subject }}
                        </p>
                        <p class="mt-1 text-sm text-gray-500 line-clamp-1">
                            {{ message.preview }}
                        </p>
                    </div>
                    <div class="ml-4 flex-shrink-0 text-xs text-gray-500">
                        {{ message.created_at_human }}
                    </div>
                </div>
            </Link>
        </div>

        <div v-else class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <p class="mt-2 text-sm text-gray-500">No messages yet</p>
            <p class="mt-1 text-xs text-gray-400">
                Messages from your network and support team will appear here
            </p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface Message {
    id: number;
    sender_name: string;
    subject: string;
    preview: string;
    is_read: boolean;
    created_at: string;
    created_at_human: string;
}

interface MessagingData {
    unread_count: number;
    recent_messages: Message[];
    has_messages: boolean;
}

defineProps<{
    messagingData: MessagingData;
}>();
</script>
