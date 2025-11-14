<template>
    <AdminLayout title="Message Conversation">
        <div class="h-[calc(100vh-4rem)] flex flex-col">
            <!-- Header with Back Button and Conversation Info -->
            <div class="bg-white border-b border-gray-200 px-6 py-4">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('admin.messages.index')"
                        class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                    >
                        <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center">
                            <span class="text-white font-semibold text-sm">
                                {{ messages[0]?.senderName.charAt(0).toUpperCase() }}
                            </span>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">{{ messages[0]?.senderName }}</h2>
                            <p class="text-sm text-gray-500">{{ messages[0]?.subject }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- WhatsApp-style Chat Area -->
            <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4" style="background-color: #e5ddd5;">
                <!-- Message Bubbles -->
                <div
                    v-for="(message, index) in messages"
                    :key="message.id"
                    :class="[
                        'flex',
                        message.senderId === $page.props.auth.user.id ? 'justify-end' : 'justify-start'
                    ]"
                >
                    <!-- Message Bubble -->
                    <div
                        :class="[
                            'max-w-[70%] rounded-2xl px-4 py-3 shadow-sm',
                            message.senderId === $page.props.auth.user.id
                                ? 'bg-blue-500 text-white rounded-br-sm'
                                : 'bg-white text-gray-900 rounded-bl-sm'
                        ]"
                    >
                        <!-- Sender name (only for received messages) -->
                        <p 
                            v-if="message.senderId !== $page.props.auth.user.id" 
                            class="text-sm font-semibold text-blue-600 mb-1"
                        >
                            {{ message.senderName }}
                        </p>
                        
                        <!-- Message text -->
                        <p class="text-[15px] leading-relaxed whitespace-pre-wrap break-words">
                            {{ message.body }}
                        </p>
                        
                        <!-- Timestamp with read status -->
                        <div 
                            :class="[
                                'flex items-center justify-end gap-1 mt-1',
                                message.senderId === $page.props.auth.user.id
                                    ? 'text-blue-100'
                                    : 'text-gray-500'
                            ]"
                        >
                            <span class="text-[11px]">
                                {{ formatTime(message.createdAt) }}
                            </span>
                            <!-- Read receipts (only for sent messages) -->
                            <span v-if="message.senderId === $page.props.auth.user.id" class="flex items-center ml-1">
                                <svg 
                                    :class="[
                                        'w-[18px] h-4',
                                        message.isRead ? 'text-blue-300' : 'text-blue-100'
                                    ]"
                                    fill="none" 
                                    viewBox="0 0 20 16" 
                                    stroke="currentColor" 
                                    stroke-width="2"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M1 8l3 3 6-6" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 8l3 3 6-6" />
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reply Input (WhatsApp style) -->
            <div class="bg-gray-100 border-t border-gray-300 px-6 py-4">
                <form @submit.prevent="sendReply" class="flex items-center gap-3">
                    <div class="flex-1">
                        <textarea
                            v-model="form.body"
                            rows="1"
                            class="w-full rounded-3xl border-2 border-gray-300 bg-white focus:border-blue-500 focus:outline-none resize-none px-4 py-3 text-base max-h-32 overflow-y-auto"
                            placeholder="Type a message..."
                            :disabled="form.processing"
                            @keydown.enter.exact.prevent="sendReply"
                        ></textarea>
                    </div>
                    <button
                        type="submit"
                        :disabled="form.processing || !form.body.trim()"
                        class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-500 hover:bg-blue-600 text-white disabled:bg-gray-300 disabled:cursor-not-allowed active:scale-95 transition-all flex items-center justify-center shadow-lg"
                    >
                        <svg v-if="form.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 74 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';

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
    otherUserId: number;
}>();

const form = useForm({
    recipient_id: props.otherUserId,
    subject: props.messages[0]?.subject.startsWith('Re:') ? props.messages[0].subject : `Re: ${props.messages[0]?.subject}`,
    body: '',
    parent_id: props.messages[0]?.id,
});

function sendReply() {
    if (!form.body.trim() || form.processing) return;
    
    form.post(route('admin.messages.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('body');
        },
    });
}

function formatTime(dateString: string): string {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}
</script>
