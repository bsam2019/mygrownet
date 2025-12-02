<script setup lang="ts">
import { ref, computed, nextTick, onMounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import GrowBizLayout from '@/Layouts/GrowBizLayout.vue';
import {
    ArrowLeftIcon,
    PaperAirplaneIcon,
} from '@heroicons/vue/24/outline';

interface ConversationMessage {
    id: number;
    senderId: number;
    senderName: string;
    body: string;
    createdAt: string;
    isOwn: boolean;
}

interface TeamMember {
    id: number;
    name: string;
    role: string;
}

const props = defineProps<{
    conversation: ConversationMessage[];
    otherUser: {
        id: number;
        name: string;
    };
    teamMembers: TeamMember[];
}>();

const page = usePage();
const currentUserId = computed(() => (page.props as any).auth?.user?.id);

const replyForm = ref({
    body: '',
});
const sending = ref(false);
const messagesContainer = ref<HTMLElement | null>(null);

const getInitials = (name: string): string => {
    if (!name) return '?';
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
};

const formatTime = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-US', { 
        hour: 'numeric', 
        minute: '2-digit',
        hour12: true 
    });
};

const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    const now = new Date();
    const diffDays = Math.floor((now.getTime() - date.getTime()) / 86400000);
    
    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Yesterday';
    if (diffDays < 7) return date.toLocaleDateString('en-US', { weekday: 'long' });
    
    return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric',
        year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
    });
};

// Group messages by date
const groupedMessages = computed(() => {
    const groups: { date: string; messages: ConversationMessage[] }[] = [];
    let currentDate = '';
    
    for (const message of props.conversation) {
        const messageDate = formatDate(message.createdAt);
        if (messageDate !== currentDate) {
            currentDate = messageDate;
            groups.push({ date: messageDate, messages: [] });
        }
        groups[groups.length - 1].messages.push(message);
    }
    
    return groups;
});

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

onMounted(() => {
    scrollToBottom();
});

const sendReply = () => {
    if (!replyForm.value.body.trim() || sending.value) return;
    
    // Find the first message to get the ID for reply
    const firstMessage = props.conversation[0];
    if (!firstMessage) return;
    
    sending.value = true;
    router.post(route('growbiz.messages.reply', firstMessage.id), {
        body: replyForm.value.body,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            replyForm.value.body = '';
            scrollToBottom();
        },
        onFinish: () => {
            sending.value = false;
        },
    });
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendReply();
    }
};
</script>

<template>
    <GrowBizLayout>
        <div class="flex flex-col h-[calc(100vh-8rem)]">
            <!-- Header -->
            <div class="flex items-center gap-3 px-4 py-3 bg-white border-b border-gray-100">
                <Link
                    :href="route('growbiz.messages.index')"
                    class="p-2 -ml-2 rounded-xl hover:bg-gray-100 transition-colors"
                    aria-label="Back to messages"
                >
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </Link>
                
                <div class="flex items-center gap-3 flex-1">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center">
                        <span class="text-sm font-semibold text-white">
                            {{ getInitials(otherUser.name) }}
                        </span>
                    </div>
                    <div>
                        <h1 class="font-semibold text-gray-900">{{ otherUser.name }}</h1>
                        <p class="text-xs text-gray-500">Team member</p>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <div 
                ref="messagesContainer"
                class="flex-1 overflow-y-auto px-4 py-4 space-y-6 bg-gray-50"
            >
                <div v-for="group in groupedMessages" :key="group.date">
                    <!-- Date separator -->
                    <div class="flex items-center justify-center mb-4">
                        <span class="px-3 py-1 text-xs font-medium text-gray-500 bg-white rounded-full shadow-sm">
                            {{ group.date }}
                        </span>
                    </div>
                    
                    <!-- Messages in group -->
                    <div class="space-y-3">
                        <div
                            v-for="message in group.messages"
                            :key="message.id"
                            :class="[
                                'flex',
                                message.isOwn ? 'justify-end' : 'justify-start'
                            ]"
                        >
                            <div :class="[
                                'max-w-[80%] rounded-2xl px-4 py-2.5',
                                message.isOwn 
                                    ? 'bg-emerald-600 text-white rounded-br-md' 
                                    : 'bg-white text-gray-900 rounded-bl-md shadow-sm'
                            ]">
                                <p class="text-sm whitespace-pre-wrap break-words">{{ message.body }}</p>
                                <p :class="[
                                    'text-[10px] mt-1',
                                    message.isOwn ? 'text-emerald-200' : 'text-gray-400'
                                ]">
                                    {{ formatTime(message.createdAt) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty state -->
                <div v-if="conversation.length === 0" class="text-center py-12">
                    <p class="text-gray-500">No messages yet. Start the conversation!</p>
                </div>
            </div>

            <!-- Reply Input -->
            <div class="px-4 py-3 bg-white border-t border-gray-100">
                <div class="flex items-end gap-2">
                    <div class="flex-1 relative">
                        <textarea
                            v-model="replyForm.body"
                            @keydown="handleKeydown"
                            rows="1"
                            placeholder="Type a message..."
                            class="w-full px-4 py-3 pr-12 rounded-2xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-colors resize-none max-h-32"
                            style="min-height: 48px;"
                        ></textarea>
                    </div>
                    <button
                        @click="sendReply"
                        :disabled="!replyForm.body.trim() || sending"
                        class="flex-shrink-0 w-12 h-12 rounded-full bg-emerald-600 text-white flex items-center justify-center hover:bg-emerald-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        aria-label="Send message"
                    >
                        <PaperAirplaneIcon v-if="!sending" class="h-5 w-5" aria-hidden="true" />
                        <svg v-else class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </GrowBizLayout>
</template>
