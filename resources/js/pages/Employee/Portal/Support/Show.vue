<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import {
    ArrowLeftIcon,
    PaperAirplaneIcon,
    ClockIcon,
    UserIcon,
    TagIcon,
    FlagIcon,
} from '@heroicons/vue/24/outline';

interface Comment {
    id: number;
    content: string;
    author_type: 'employee' | 'support';
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
    assignee?: { id: number; full_name: string };
    comments: Comment[];
}

interface Props {
    ticket: Ticket;
    categories: Record<string, string>;
}

const props = defineProps<Props>();

const messages = ref<Comment[]>([...props.ticket.comments]);
const newMessage = ref('');
const chatContainer = ref<HTMLElement | null>(null);
const isConnected = ref(false);
const isTyping = ref(false);
const typingName = ref('');
const isSending = ref(false);

// Echo instance - will be set if available
let echo: any = null;

// Channel reference for whispers
let chatChannel: any = null;
let typingTimeout: ReturnType<typeof setTimeout> | null = null;

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
        }
    }).catch(() => {
        // Real-time features disabled
    });
};

const subscribeToChat = () => {
    if (!echo) return;

    try {
        chatChannel = echo.private(`support.ticket.${props.ticket.id}`);
        const channel = chatChannel;

        // Listen for connection events
        if (echo.connector && echo.connector.pusher) {
            echo.connector.pusher.connection.bind('connected', () => {
                console.log('[EmployeeChat] WebSocket connected');
                isConnected.value = true;
            });
            
            echo.connector.pusher.connection.bind('disconnected', () => {
                console.log('[EmployeeChat] WebSocket disconnected');
                isConnected.value = false;
            });
            
            // Check current state
            const state = echo.connector.pusher.connection.state;
            isConnected.value = state === 'connected';
        }

        channel.listen('.chat.message', (data: any) => {
            console.log('[EmployeeChat] Received message:', data);
            if (data.sender_type === 'support') {
                messages.value.push({
                    id: Date.now(),
                    content: data.message,
                    author_type: 'support',
                    created_at: data.sent_at,
                    author: { id: data.sender_id, full_name: data.sender_name },
                });
                scrollToBottom();
            }
        });

        channel.listenForWhisper('typing', (data: { name: string }) => {
            console.log('[EmployeeChat] Received typing indicator from:', data.name);
            typingName.value = data.name || 'Support';
            isTyping.value = true;
            // Clear previous timeout
            if (typingTimeout) clearTimeout(typingTimeout);
            typingTimeout = setTimeout(() => { 
                isTyping.value = false; 
                typingName.value = '';
            }, 3000);
        });

        isConnected.value = true;
    } catch (e) {
        console.warn('Failed to subscribe to chat channel:', e);
    }
};

// Polling fallback for when real-time isn't available
let pollInterval: ReturnType<typeof setInterval> | null = null;

const startPolling = () => {
    if (pollInterval) return;
    
    console.log('[EmployeeChat] Starting polling fallback');
    console.log('[EmployeeChat] Polling URL:', route('employee.portal.support.show', props.ticket.id));
    
    pollInterval = setInterval(async () => {
        console.log('[EmployeeChat] Polling for new messages... Current count:', messages.value.length);
        // Always poll as backup - WebSocket connection state can be unreliable
        try {
            const response = await fetch(route('employee.portal.support.show', props.ticket.id), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin', // Include cookies for auth
            });
            
            console.log('[EmployeeChat] Poll response status:', response.status);
            
            if (response.ok) {
                const data = await response.json();
                console.log('[EmployeeChat] Poll data received, comments count:', data.ticket?.comments?.length || 0);
                
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
                        console.log('[EmployeeChat] New messages from polling:', newComments.length);
                        messages.value.push(...newComments);
                        scrollToBottom();
                    }
                }
            }
        } catch (e) {
            console.warn('[EmployeeChat] Polling failed:', e);
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
    console.log('[EmployeeChat] Component mounted');
    console.log('[EmployeeChat] Initial messages count:', messages.value.length);
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

const sendMessage = async () => {
    if (!newMessage.value.trim() || isSending.value) {
        return;
    }

    const messageText = newMessage.value.trim();
    newMessage.value = '';
    isSending.value = true;

    // Add optimistically
    messages.value.push({
        id: Date.now(),
        content: messageText,
        author_type: 'employee',
        created_at: new Date().toISOString(),
        author: { id: 0, full_name: 'You' },
    });
    scrollToBottom();

    // Use fetch to avoid page refresh
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    try {
        const endpoint = route('employee.portal.support.chat', props.ticket.id);

        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ message: messageText }),
            credentials: 'same-origin', // Include cookies
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Failed to send message:', errorText);
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();
        console.log('[SupportShow] Message sent successfully:', data);

        isSending.value = false;
    } catch (error) {
        console.error('[SupportShow] Failed to send message:', error);
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
        chatChannel.whisper('typing', { name: 'Employee' });
        console.log('[EmployeeChat] Sent typing indicator');
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

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString([], { month: 'short', day: 'numeric', year: 'numeric' });
};

const getStatusColor = (s: string) => ({
    open: 'bg-blue-100 text-blue-700',
    in_progress: 'bg-amber-100 text-amber-700',
    waiting: 'bg-purple-100 text-purple-700',
    resolved: 'bg-green-100 text-green-700',
    closed: 'bg-gray-100 text-gray-700',
}[s] || 'bg-gray-100 text-gray-700');

const getPriorityColor = (p: string) => ({
    urgent: 'text-red-600',
    high: 'text-amber-600',
    medium: 'text-blue-600',
    low: 'text-gray-500',
}[p] || 'text-gray-500');
</script>

<template>
    <Head :title="`Ticket #${ticket.ticket_number}`" />

    <EmployeePortalLayout>
        <div class="h-[calc(100vh-180px)] flex flex-col">
            <!-- Header -->
            <div class="bg-white border-b border-gray-200 px-6 py-4 flex items-center gap-4 rounded-t-xl">
                <Link :href="route('employee.portal.support.index')" class="p-2 hover:bg-gray-100 rounded-lg">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                </Link>
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <h1 class="text-lg font-semibold text-gray-900">#{{ ticket.ticket_number }}</h1>
                        <span :class="[getStatusColor(ticket.status), 'px-2 py-0.5 text-xs font-medium rounded-full']">
                            {{ ticket.status.replace('_', ' ') }}
                        </span>
                        <span v-if="isConnected" class="flex items-center gap-1 text-xs text-green-600">
                            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                            Live
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 mt-0.5">{{ ticket.subject }}</p>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 flex overflow-hidden bg-white rounded-b-xl">
                <!-- Chat Area -->
                <div class="flex-1 flex flex-col">
                    <!-- Ticket Details Banner -->
                    <div class="bg-gray-50 px-6 py-3 border-b border-gray-100">
                        <div class="flex items-center gap-6 text-sm">
                            <div class="flex items-center gap-2">
                                <TagIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                                <span class="text-gray-600">{{ categories[ticket.category] || ticket.category }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <FlagIcon :class="['h-4 w-4', getPriorityColor(ticket.priority)]" aria-hidden="true" />
                                <span class="capitalize" :class="getPriorityColor(ticket.priority)">{{ ticket.priority }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <ClockIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                                <span class="text-gray-600">{{ formatDate(ticket.created_at) }}</span>
                            </div>
                            <div v-if="ticket.assignee" class="flex items-center gap-2">
                                <UserIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                                <span class="text-gray-600">{{ ticket.assignee.full_name }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Original Description -->
                    <div class="px-6 py-4 border-b border-gray-100 bg-blue-50/50">
                        <p class="text-xs text-gray-500 mb-1">Original Request</p>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ ticket.description }}</p>
                    </div>

                    <!-- Messages -->
                    <div ref="chatContainer" class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50">
                        <div v-for="msg in messages" :key="msg.id"
                            :class="['flex', msg.author_type === 'employee' ? 'justify-end' : 'justify-start']">
                            <div :class="['max-w-[70%] rounded-2xl px-4 py-3',
                                msg.author_type === 'employee' 
                                    ? 'bg-blue-600 text-white rounded-br-md' 
                                    : 'bg-white text-gray-900 rounded-bl-md shadow-sm']">
                                <p v-if="msg.author_type === 'support'" class="text-xs text-blue-600 font-medium mb-1">
                                    {{ msg.author?.full_name || 'Support' }}
                                </p>
                                <p class="text-sm whitespace-pre-wrap">{{ msg.content }}</p>
                                <p :class="['text-xs mt-1', msg.author_type === 'employee' ? 'text-blue-200' : 'text-gray-400']">
                                    {{ formatTime(msg.created_at) }}
                                </p>
                            </div>
                        </div>

                        <!-- Typing indicator -->
                        <div v-if="isTyping" class="flex justify-start items-center gap-2">
                            <div class="bg-white rounded-2xl rounded-bl-md px-4 py-3 shadow-sm">
                                <p class="text-xs text-gray-500 mb-1">{{ typingName || 'Support' }} is typing...</p>
                                <div class="flex gap-1">
                                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></span>
                                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Empty state -->
                        <div v-if="messages.length === 0" class="text-center py-8 text-gray-500">
                            <p>No messages yet. Start the conversation!</p>
                        </div>
                    </div>

                    <!-- Input -->
                    <div v-if="ticket.status !== 'closed' && ticket.status !== 'resolved'" class="p-4 bg-white border-t border-gray-200">
                        <div class="flex items-end gap-3">
                            <textarea 
                                v-model="newMessage" 
                                @keydown="handleKeydown"
                                @input="handleInput"
                                rows="2" 
                                placeholder="Type your message..."
                                class="flex-1 resize-none border-gray-300 rounded-xl focus:ring-blue-500 focus:border-blue-500"
                            ></textarea>
                            <button 
                                type="button"
                                @click.prevent="sendMessage" 
                                :disabled="!newMessage.trim() || isSending"
                                class="p-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <PaperAirplaneIcon class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>
                    </div>

                    <!-- Closed ticket notice -->
                    <div v-else class="p-4 bg-gray-100 border-t border-gray-200 text-center">
                        <p class="text-gray-600">This ticket has been {{ ticket.status }}.</p>
                        <Link :href="route('employee.portal.support.create')" class="text-blue-600 hover:text-blue-700 text-sm">
                            Create a new ticket
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </EmployeePortalLayout>
</template>
