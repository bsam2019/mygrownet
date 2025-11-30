<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import {
    ArrowLeftIcon,
    PaperAirplaneIcon,
    UserIcon,
    BuildingOfficeIcon,
    UserGroupIcon,
} from '@heroicons/vue/24/outline';

interface Comment {
    id: number;
    author_name: string;
    author_type: 'user' | 'support';
    content: string;
    created_at: string;
}

interface Ticket {
    id: number;
    source: 'employee' | 'member' | 'investor';
    ticket_number: string;
    subject: string;
    description: string;
    status: string;
    channel_name?: string;
    requester: {
        name: string;
        type: string;
    };
    comments: Comment[];
}

const props = defineProps<{ ticket: Ticket }>();

const messages = ref<Comment[]>([...props.ticket.comments]);
const newMessage = ref('');
const isSending = ref(false);
const chatContainer = ref<HTMLElement | null>(null);
const isTyping = ref(false);
const typingName = ref('');

// Add original description as first message
const allMessages = ref<Comment[]>([
    {
        id: 0,
        author_name: props.ticket.requester.name,
        author_type: 'user',
        content: props.ticket.description,
        created_at: new Date().toISOString(),
    },
    ...props.ticket.comments,
]);

let echo: any = null;
let chatChannel: any = null;
let typingTimeout: ReturnType<typeof setTimeout> | null = null;
let pollInterval: ReturnType<typeof setInterval> | null = null;
let lastTypingTime = 0;

const isConnected = ref(false);

const scrollToBottom = async () => {
    await nextTick();
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
};

// Send typing indicator to the user
const sendTypingIndicator = () => {
    const now = Date.now();
    if (chatChannel && now - lastTypingTime > 2000) {
        lastTypingTime = now;
        chatChannel.whisper('typing', { name: 'Support' });
    }
};

// Handle input for typing indicator
const handleInput = () => {
    if (newMessage.value.trim()) {
        sendTypingIndicator();
    }
};

const initEcho = () => {
    if ((window as any).Echo) {
        echo = (window as any).Echo;
        subscribeToChat();
    }
};

// Determine the correct channel based on ticket source
const getChannelName = () => {
    switch (props.ticket.source) {
        case 'investor':
            return `investor.support.${props.ticket.id}`;
        case 'member':
            return `member.support.${props.ticket.id}`;
        case 'employee':
        default:
            return `support.ticket.${props.ticket.id}`;
    }
};

const subscribeToChat = () => {
    if (!echo) {
        console.warn('[UnifiedLiveChat] Echo not available');
        return;
    }
    
    try {
        // Use channel_name from props if available, otherwise determine from source
        const channelName = props.ticket.channel_name || getChannelName();
        console.log(`[UnifiedLiveChat] Subscribing to channel: ${channelName}`);
        console.log(`[UnifiedLiveChat] Ticket source: ${props.ticket.source}, ID: ${props.ticket.id}`);
        chatChannel = echo.private(channelName);

        // Track connection state
        if (echo.connector && echo.connector.pusher) {
            const pusher = echo.connector.pusher;
            
            // Log current state
            console.log('[UnifiedLiveChat] Pusher connection state:', pusher.connection.state);
            
            pusher.connection.bind('connected', () => {
                console.log('[UnifiedLiveChat] WebSocket connected');
                isConnected.value = true;
            });
            pusher.connection.bind('disconnected', () => {
                console.log('[UnifiedLiveChat] WebSocket disconnected');
                isConnected.value = false;
            });
            pusher.connection.bind('error', (err: any) => {
                console.error('[UnifiedLiveChat] WebSocket error:', err);
            });
            
            isConnected.value = pusher.connection.state === 'connected';
            
            // Log subscription events
            chatChannel.subscription.bind('pusher:subscription_succeeded', () => {
                console.log(`[UnifiedLiveChat] ✅ Successfully subscribed to: private-${channelName}`);
            });
            chatChannel.subscription.bind('pusher:subscription_error', (status: any) => {
                console.error(`[UnifiedLiveChat] ❌ Subscription error for: private-${channelName}`, status);
            });
        } else {
            console.warn('[UnifiedLiveChat] Pusher connector not available');
            isConnected.value = true;
        }

        chatChannel.listen('.chat.message', (data: any) => {
            console.log('[UnifiedLiveChat] 🎉 Received message via broadcast:', data);
            // Don't add our own messages (support messages) - they're added optimistically
            if (data.sender_type !== 'support') {
                // Check for duplicates by content and timestamp (within 5 seconds)
                const isDuplicate = allMessages.value.some(m => 
                    m.content === data.message && 
                    Math.abs(new Date(m.created_at).getTime() - new Date(data.sent_at).getTime()) < 5000
                );
                
                if (!isDuplicate) {
                    console.log('[UnifiedLiveChat] Adding new message to chat');
                    allMessages.value.push({
                        id: data.id || Date.now(),
                        author_name: data.sender_name,
                        author_type: data.sender_type === 'support' ? 'support' : 'user',
                        content: data.message,
                        created_at: data.sent_at,
                    });
                    scrollToBottom();
                } else {
                    console.log('[UnifiedLiveChat] Duplicate message ignored');
                }
            } else {
                console.log('[UnifiedLiveChat] Ignoring own support message');
            }
        });

        chatChannel.listenForWhisper('typing', (data: { name: string }) => {
            console.log('[UnifiedLiveChat] Typing indicator from:', data.name);
            typingName.value = data.name;
            isTyping.value = true;
            if (typingTimeout) clearTimeout(typingTimeout);
            typingTimeout = setTimeout(() => {
                isTyping.value = false;
            }, 3000);
        });
        
        console.log('[UnifiedLiveChat] Event listeners attached');
    } catch (e) {
        console.error('[UnifiedLiveChat] Failed to subscribe to chat channel:', e);
        isConnected.value = false;
    }
};

const sendMessage = async () => {
    if (!newMessage.value.trim() || isSending.value) return;

    const messageText = newMessage.value.trim();
    newMessage.value = '';
    isSending.value = true;

    // Add optimistically
    const tempMessage: Comment = {
        id: Date.now(),
        author_name: 'Support',
        author_type: 'support',
        content: messageText,
        created_at: new Date().toISOString(),
    };
    allMessages.value.push(tempMessage);
    scrollToBottom();

    try {
        const response = await fetch(
            route('admin.unified-support.reply', { source: props.ticket.source, id: props.ticket.id }),
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({ message: messageText }),
            }
        );

        if (!response.ok) {
            // Remove optimistic message on failure
            allMessages.value = allMessages.value.filter(m => m.id !== tempMessage.id);
        }
    } catch (e) {
        allMessages.value = allMessages.value.filter(m => m.id !== tempMessage.id);
    } finally {
        isSending.value = false;
    }
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    } else {
        // Send typing indicator on any other key
        sendTypingIndicator();
    }
};

const formatTime = (dateString: string) => {
    return new Date(dateString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const goBack = () => {
    router.visit(route('admin.unified-support.index'));
};

onMounted(() => {
    initEcho();
    scrollToBottom();
    
    // POLLING DISABLED FOR DEBUGGING BROADCAST ISSUES
    // Uncomment below to re-enable fallback polling
    /*
    pollInterval = setInterval(async () => {
        // Skip polling if WebSocket is connected
        if (isConnected.value) {
            return;
        }
        
        try {
            const response = await fetch(
                route('admin.unified-support.show', { source: props.ticket.source, id: props.ticket.id }),
                { headers: { 'Accept': 'application/json' } }
            );
            if (response.ok) {
                const data = await response.json();
                const newComments = data.ticket.comments;
                // Only update if we have more comments than before (excluding the initial description)
                const currentCommentCount = allMessages.value.length - 1; // -1 for the description
                if (newComments.length > currentCommentCount) {
                    // Merge new comments without duplicates
                    const existingIds = new Set(allMessages.value.map(m => m.id));
                    const newOnes = newComments.filter((c: Comment) => !existingIds.has(c.id));
                    if (newOnes.length > 0) {
                        allMessages.value.push(...newOnes);
                        scrollToBottom();
                    }
                }
            }
        } catch (e) {}
    }, 10000);
    */
    console.log('[UnifiedLiveChat] Polling DISABLED - relying on WebSocket only');
});

onUnmounted(() => {
    if (echo && props.ticket.id) {
        const channelName = props.ticket.channel_name || getChannelName();
        echo.leave(channelName);
    }
    if (pollInterval) clearInterval(pollInterval);
    if (typingTimeout) clearTimeout(typingTimeout);
});
</script>

<template>
    <Head :title="`Chat - ${ticket.ticket_number}`" />
    <AdminLayout>
        <div class="h-[calc(100vh-4rem)] flex flex-col">
            <!-- Header -->
            <div class="bg-white border-b border-gray-200 px-6 py-4 flex items-center gap-4">
                <button @click="goBack" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Go back">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" />
                </button>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <h1 class="text-lg font-semibold text-gray-900">{{ ticket.ticket_number }}</h1>
                        <span :class="[
                            ticket.source === 'employee' ? 'bg-indigo-100 text-indigo-700' : 
                            ticket.source === 'investor' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700',
                            'px-2 py-0.5 text-xs font-medium rounded-full flex items-center gap-1'
                        ]">
                            <component :is="ticket.source === 'employee' ? BuildingOfficeIcon : UserGroupIcon" class="h-3 w-3" />
                            {{ ticket.source === 'employee' ? 'Employee' : ticket.source === 'investor' ? 'Investor' : 'Member' }}
                        </span>
                        <!-- Connection Status -->
                        <span :class="[
                            isConnected ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500',
                            'px-2 py-0.5 text-xs font-medium rounded-full flex items-center gap-1'
                        ]">
                            <span :class="['w-1.5 h-1.5 rounded-full', isConnected ? 'bg-green-500' : 'bg-gray-400']"></span>
                            {{ isConnected ? 'Live' : 'Offline' }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600">{{ ticket.subject }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-900">{{ ticket.requester.name }}</p>
                    <p class="text-xs text-gray-500">{{ ticket.requester.type }}</p>
                </div>
            </div>

            <!-- Messages -->
            <div ref="chatContainer" class="flex-1 overflow-y-auto p-6 bg-gray-50 space-y-4">
                <div
                    v-for="msg in allMessages"
                    :key="msg.id"
                    :class="['flex', msg.author_type === 'support' ? 'justify-end' : 'justify-start']"
                >
                    <div
                        :class="[
                            'max-w-[70%] rounded-2xl px-4 py-3',
                            msg.author_type === 'support'
                                ? 'bg-blue-600 text-white rounded-br-md'
                                : 'bg-white text-gray-900 rounded-bl-md shadow-sm border border-gray-200'
                        ]"
                    >
                        <p v-if="msg.author_type !== 'support'" class="text-xs text-blue-600 font-medium mb-1">
                            {{ msg.author_name }}
                        </p>
                        <p class="text-sm whitespace-pre-wrap">{{ msg.content }}</p>
                        <p :class="['text-xs mt-1', msg.author_type === 'support' ? 'text-blue-200' : 'text-gray-400']">
                            {{ formatTime(msg.created_at) }}
                        </p>
                    </div>
                </div>

                <!-- Typing indicator -->
                <div v-if="isTyping" class="flex justify-start">
                    <div class="bg-white rounded-2xl rounded-bl-md px-4 py-3 shadow-sm border border-gray-200">
                        <p class="text-xs text-gray-500 mb-1">{{ typingName }} is typing...</p>
                        <div class="flex gap-1">
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input -->
            <div class="bg-white border-t border-gray-200 p-4">
                <div class="flex items-end gap-3">
                    <textarea
                        v-model="newMessage"
                        @keydown="handleKeydown"
                        @input="handleInput"
                        placeholder="Type your reply..."
                        rows="1"
                        class="flex-1 resize-none border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    ></textarea>
                    <button
                        @click="sendMessage"
                        :disabled="!newMessage.trim() || isSending"
                        class="p-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        aria-label="Send message"
                    >
                        <PaperAirplaneIcon class="h-5 w-5" />
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
