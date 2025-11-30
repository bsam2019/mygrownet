<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue';
import {
    ArrowLeftIcon,
    PaperAirplaneIcon,
    UserIcon,
    PhoneIcon,
    EnvelopeIcon,
} from '@heroicons/vue/24/outline';

interface Comment {
    id?: number;
    content: string;
    author_type: 'employee' | 'support';
    created_at: string;
    author?: { id: number; full_name: string };
}

interface Ticket {
    id: number;
    ticket_number: string;
    subject: string;
    description?: string;
    status: string;
    priority: string;
    created_at?: string;
    employee: {
        id: number;
        full_name: string;
        email: string;
        phone?: string;
        department?: { name: string };
    };
    comments: Comment[];
}

interface Props {
    ticket: Ticket;
}

const props = defineProps<Props>();

const messages = ref<Comment[]>([...(props.ticket.comments || [])]);
const newMessage = ref('');
const chatContainer = ref<HTMLElement | null>(null);
const isConnected = ref(false);
const isTyping = ref(false);
const typingName = ref('');

// Channel reference for whispers
let chatChannel: any = null;
let typingTimeout: ReturnType<typeof setTimeout> | null = null;

// Debug: Log the initial data
console.log('[AdminLiveChat] Ticket:', props.ticket);
console.log('[AdminLiveChat] Comments:', props.ticket.comments);
console.log('[AdminLiveChat] Messages initialized:', messages.value);

// Echo instance - will be set if available
let echo: any = null;

const scrollToBottom = async () => {
    await nextTick();
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
};

// Initialize Echo if available
const initEcho = () => {
    // Try global Echo first (set in app.ts)
    if ((window as any).Echo) {
        echo = (window as any).Echo;
        subscribeToChat();
        return;
    }
    
    // Fallback: try useEcho from @laravel/echo-vue
    import('@laravel/echo-vue').then(({ useEcho }) => {
        const echoInstance = useEcho();
        if (echoInstance) {
            echo = echoInstance;
            subscribeToChat();
        } else {
            console.info('Real-time chat features disabled - Echo not configured');
        }
    }).catch(() => {
        console.info('Real-time chat features disabled');
    });
};

const subscribeToChat = () => {
    if (!echo) {
        console.log('[AdminLiveChat] Echo not available');
        return;
    }

    try {
        console.log('[AdminLiveChat] Subscribing to channel: support.ticket.' + props.ticket.id);
        chatChannel = echo.private(`support.ticket.${props.ticket.id}`);
        const channel = chatChannel;

        // Listen for connection events
        if (echo.connector && echo.connector.pusher) {
            echo.connector.pusher.connection.bind('connected', () => {
                console.log('[AdminLiveChat] WebSocket connected');
                isConnected.value = true;
            });
            
            echo.connector.pusher.connection.bind('disconnected', () => {
                console.log('[AdminLiveChat] WebSocket disconnected');
                isConnected.value = false;
            });
            
            echo.connector.pusher.connection.bind('error', (error: any) => {
                console.warn('[AdminLiveChat] WebSocket error:', error);
                isConnected.value = false;
            });
            
            // Check current state
            const state = echo.connector.pusher.connection.state;
            console.log('[AdminLiveChat] Current connection state:', state);
            isConnected.value = state === 'connected';
        }

        channel.listen('.chat.message', (data: any) => {
            console.log('[AdminLiveChat] Received message:', data);
            if (data.sender_type === 'employee') {
                messages.value.push({
                    content: data.message,
                    author_type: 'employee',
                    created_at: data.sent_at,
                    author: { id: data.sender_id, full_name: data.sender_name },
                });
                scrollToBottom();
            }
        });

        channel.listenForWhisper('typing', (data: { name: string }) => {
            console.log('[AdminLiveChat] Received typing indicator from:', data.name);
            typingName.value = data.name || 'Employee';
            isTyping.value = true;
            // Clear previous timeout
            if (typingTimeout) clearTimeout(typingTimeout);
            typingTimeout = setTimeout(() => { 
                isTyping.value = false; 
                typingName.value = '';
            }, 3000);
        });

    } catch (e) {
        console.warn('[AdminLiveChat] Failed to subscribe to chat channel:', e);
        isConnected.value = false;
    }
};

// Polling fallback for when real-time isn't available
let pollInterval: ReturnType<typeof setInterval> | null = null;

const startPolling = () => {
    if (pollInterval) {
        console.log('[AdminLiveChat] Polling already running');
        return;
    }
    
    console.log('[AdminLiveChat] Starting polling - will check every 5 seconds');
    console.log('[AdminLiveChat] Polling URL:', route('admin.support.show', props.ticket.id));
    
    pollInterval = setInterval(async () => {
        console.log('[AdminLiveChat] Polling for new messages... Current count:', messages.value.length);
        // Always poll as backup - WebSocket connection state can be unreliable
        try {
            const url = route('admin.support.show', props.ticket.id);
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin', // Include cookies for auth
            });
            
            console.log('[AdminLiveChat] Poll response status:', response.status);
            
            if (response.ok) {
                const data = await response.json();
                console.log('[AdminLiveChat] Poll data received, comments count:', data.ticket?.comments?.length || 0);
                
                if (data.ticket?.comments) {
                    // Update messages if there are new ones
                    // Compare by ID if available, or by content+author_type to avoid duplicates
                    const newComments = data.ticket.comments.filter((c: Comment) => 
                        !messages.value.some(m => 
                            (m.id && m.id === c.id) || 
                            (m.content === c.content && m.author_type === c.author_type)
                        )
                    );
                    if (newComments.length > 0) {
                        console.log('[AdminLiveChat] New messages from polling:', newComments.length);
                        messages.value.push(...newComments);
                        scrollToBottom();
                    }
                }
            }
        } catch (e) {
            console.warn('[AdminLiveChat] Polling failed:', e);
        }
    }, 5000); // Poll every 5 seconds
};

const stopPolling = () => {
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
};

onMounted(() => {
    console.log('[AdminLiveChat] Component mounted');
    console.log('[AdminLiveChat] Initial messages count:', messages.value.length);
    initEcho();
    scrollToBottom();
    
    // Polling disabled - focusing on Reverb for real-time updates
    // startPolling();
});

onUnmounted(() => {
    stopPolling();
    if (echo) {
        try {
            echo.leave(`support.ticket.${props.ticket.id}`);
        } catch (e) {
            // Ignore errors when leaving
        }
    }
});

const isSending = ref(false);

const sendMessage = async (event?: Event) => {
    // Prevent any default behavior
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    if (!newMessage.value.trim() || isSending.value) {
        console.log('[AdminLiveChat] Cannot send: empty message or already sending');
        return;
    }

    const messageText = newMessage.value.trim();
    newMessage.value = '';
    isSending.value = true;

    console.log('[AdminLiveChat] Sending message:', messageText);

    // Add optimistically
    messages.value.push({
        content: messageText,
        author_type: 'support',
        created_at: new Date().toISOString(),
        author: { id: 0, full_name: 'You' },
    });
    scrollToBottom();

    // Use fetch to avoid page refresh
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    console.log('[AdminLiveChat] CSRF Token:', csrfToken ? 'Present' : 'Missing');

    try {
        const endpoint = route('admin.support.chat', props.ticket.id);
        console.log('[AdminLiveChat] Endpoint:', endpoint);

        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ message: messageText }),
        });

        console.log('[AdminLiveChat] Response status:', response.status);

        if (!response.ok) {
            const errorText = await response.text();
            console.error('[AdminLiveChat] Error response:', errorText);
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        console.log('[AdminLiveChat] Message sent successfully:', data);

        isSending.value = false;
    } catch (error) {
        console.error('[AdminLiveChat] Failed to send message:', error);
        // Remove optimistic message on error
        messages.value.pop();
        isSending.value = false;
    }
};

// Send typing indicator (debounced)
let lastTypingTime = 0;
const sendTypingIndicator = () => {
    const now = Date.now();
    // Only send typing indicator every 2 seconds to avoid spam
    if (chatChannel && now - lastTypingTime > 2000) {
        lastTypingTime = now;
        chatChannel.whisper('typing', { name: 'Support' });
        console.log('[AdminLiveChat] Sent typing indicator');
    }
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
};

const handleInput = () => {
    // Send typing indicator when user types
    if (newMessage.value.trim()) {
        sendTypingIndicator();
    }
};

const formatTime = (date: string) => {
    return new Date(date).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const getStatusColor = (s: string) => ({
    open: 'bg-blue-100 text-blue-700',
    in_progress: 'bg-amber-100 text-amber-700',
    resolved: 'bg-green-100 text-green-700',
}[s] || 'bg-gray-100 text-gray-700');
</script>

<template>
    <Head :title="`Live Chat - #${ticket.ticket_number}`" />
    <AdminLayout>
        <div class="h-[calc(100vh-120px)] flex flex-col">
            <!-- Header -->
            <div class="bg-white border-b border-gray-200 px-6 py-4 flex items-center gap-4">
                <Link :href="route('admin.support.show', ticket.id)" class="p-2 hover:bg-gray-100 rounded-lg">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                </Link>
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <h1 class="text-lg font-semibold text-gray-900">Live Chat - #{{ ticket.ticket_number }}</h1>
                        <span :class="[getStatusColor(ticket.status), 'px-2 py-0.5 text-xs font-medium rounded-full']">
                            {{ ticket.status.replace('_', ' ') }}
                        </span>
                        <span v-if="isConnected" class="flex items-center gap-1 text-xs text-green-600">
                            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                            Live
                        </span>
                        <span v-else class="flex items-center gap-1 text-xs text-amber-600">
                            <span class="w-2 h-2 bg-amber-500 rounded-full"></span>
                            Polling
                        </span>
                    </div>
                    <p class="text-sm text-gray-500">{{ ticket.subject }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-900">{{ ticket.employee.full_name }}</p>
                    <p class="text-xs text-gray-500">{{ ticket.employee.department?.name }}</p>
                </div>
            </div>

            <!-- Chat Area -->
            <div class="flex-1 flex">
                <!-- Messages -->
                <div class="flex-1 flex flex-col bg-gray-50">
                    <div ref="chatContainer" class="flex-1 overflow-y-auto p-6 space-y-4">
                        <!-- Original Ticket Description (first message from employee) -->
                        <div v-if="ticket.description" class="flex justify-start">
                            <div class="max-w-[70%] rounded-2xl px-4 py-3 bg-white text-gray-900 rounded-bl-md shadow-sm border-l-4 border-blue-400">
                                <p class="text-xs text-blue-600 font-medium mb-1">
                                    {{ ticket.employee.full_name }} · Original Request
                                </p>
                                <p class="text-sm whitespace-pre-wrap">{{ ticket.description }}</p>
                                <p class="text-xs mt-1 text-gray-400">
                                    {{ formatTime(ticket.created_at || new Date().toISOString()) }}
                                </p>
                            </div>
                        </div>
                        <!-- Empty state (only if no description AND no messages) -->
                        <div v-if="!ticket.description && messages.length === 0" class="text-center py-8 text-gray-500">
                            <p>No messages yet. Start the conversation!</p>
                        </div>
                        <div v-for="(msg, index) in messages" :key="index"
                            :class="['flex', msg.author_type === 'support' ? 'justify-end' : 'justify-start']">
                            <div :class="['max-w-[70%] rounded-2xl px-4 py-3',
                                msg.author_type === 'support' ? 'bg-blue-600 text-white rounded-br-md' : 'bg-white text-gray-900 rounded-bl-md shadow-sm']">
                                <p class="text-sm whitespace-pre-wrap">{{ msg.content }}</p>
                                <p :class="['text-xs mt-1', msg.author_type === 'support' ? 'text-blue-200' : 'text-gray-400']">
                                    {{ formatTime(msg.created_at) }}
                                </p>
                            </div>
                        </div>
                        <div v-if="isTyping" class="flex justify-start items-center gap-2">
                            <div class="bg-white rounded-2xl rounded-bl-md px-4 py-3 shadow-sm">
                                <p class="text-xs text-gray-500 mb-1">{{ typingName || 'Employee' }} is typing...</p>
                                <div class="flex gap-1">
                                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></span>
                                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Input -->
                    <div class="p-4 bg-white border-t border-gray-200">
                        <div class="flex items-end gap-3">
                            <textarea v-model="newMessage" @keydown="handleKeydown" @input="handleInput" rows="2" placeholder="Type your message..."
                                class="flex-1 resize-none border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500"></textarea>
                            <button type="button" @click.prevent="sendMessage" :disabled="!newMessage.trim() || isSending"
                                class="p-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                <PaperAirplaneIcon class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="w-72 bg-white border-l border-gray-200 p-4 hidden lg:block">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Employee Info</h3>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center">
                            <UserIcon class="h-6 w-6 text-gray-500" aria-hidden="true" />
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ ticket.employee.full_name }}</p>
                            <p class="text-sm text-gray-500">{{ ticket.employee.department?.name }}</p>
                        </div>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center gap-2 text-gray-600">
                            <EnvelopeIcon class="h-4 w-4" aria-hidden="true" />
                            <span class="truncate">{{ ticket.employee.email }}</span>
                        </div>
                        <div v-if="ticket.employee.phone" class="flex items-center gap-2 text-gray-600">
                            <PhoneIcon class="h-4 w-4" aria-hidden="true" />
                            <span>{{ ticket.employee.phone }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
