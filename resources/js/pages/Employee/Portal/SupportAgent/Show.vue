<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import EmployeePortalLayout from '@/Layouts/EmployeePortalLayout.vue';
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue';
import {
    ArrowLeftIcon,
    PaperAirplaneIcon,
    UserIcon,
    TagIcon,
    FlagIcon,
    ClockIcon,
    CheckCircleIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

interface Comment {
    id: number;
    content: string;
    author_type: 'user' | 'support';
    author_name: string;
    created_at: string;
}

interface Ticket {
    id: number;
    ticket_number: string;
    subject: string;
    description: string;
    status: string;
    priority: string;
    category: string;
    source: 'member' | 'investor';
    user_id: number;
    user_name: string;
    user_email: string;
    created_at: string;
    updated_at: string;
    assigned_to: number | null;
    comments: Comment[];
}

interface Props {
    employee: any;
    ticket: Ticket;
    channel: string;
}

const props = defineProps<Props>();

const messages = ref<Comment[]>([...props.ticket.comments]);
const newMessage = ref('');
const chatContainer = ref<HTMLElement | null>(null);
const isConnected = ref(false);
const isTyping = ref(false);
const typingName = ref('');
const isSending = ref(false);
const showStatusModal = ref(false);
const selectedStatus = ref(props.ticket.status);

let echo: any = null;
let chatChannel: any = null;
let typingTimeout: ReturnType<typeof setTimeout> | null = null;

const scrollToBottom = async () => {
    await nextTick();
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
};

const initEcho = () => {
    if ((window as any).Echo) {
        echo = (window as any).Echo;
        subscribeToChat();
        return;
    }
    
    import('@laravel/echo-vue').then(({ useEcho }) => {
        const echoInstance = useEcho();
        if (echoInstance) {
            echo = echoInstance;
            subscribeToChat();
        }
    }).catch(() => {
        console.log('[SupportAgent] Real-time features disabled');
    });
};

const subscribeToChat = () => {
    if (!echo) return;

    try {
        chatChannel = echo.private(props.channel);

        if (echo.connector && echo.connector.pusher) {
            echo.connector.pusher.connection.bind('connected', () => {
                isConnected.value = true;
            });
            
            echo.connector.pusher.connection.bind('disconnected', () => {
                isConnected.value = false;
            });
            
            const state = echo.connector.pusher.connection.state;
            isConnected.value = state === 'connected';
        } else {
            isConnected.value = true;
        }

        chatChannel.listen('.chat.message', (data: any) => {
            console.log('[SupportAgent] Received message:', data);
            // Don't add our own messages (already added optimistically)
            if (data.sender_type !== 'support') {
                const isDuplicate = messages.value.some(m => 
                    m.content === data.message && 
                    Math.abs(new Date(m.created_at).getTime() - new Date(data.sent_at).getTime()) < 5000
                );
                
                if (!isDuplicate) {
                    messages.value.push({
                        id: data.id || Date.now(),
                        content: data.message,
                        author_type: 'user',
                        author_name: data.sender_name,
                        created_at: data.sent_at,
                    });
                    scrollToBottom();
                }
            }
        });

        chatChannel.listenForWhisper('typing', (data: { name: string }) => {
            typingName.value = data.name || props.ticket.user_name;
            isTyping.value = true;
            if (typingTimeout) clearTimeout(typingTimeout);
            typingTimeout = setTimeout(() => { 
                isTyping.value = false; 
                typingName.value = '';
            }, 3000);
        });

    } catch (e) {
        console.warn('[SupportAgent] Failed to subscribe:', e);
    }
};

onMounted(() => {
    initEcho();
    scrollToBottom();
});

onUnmounted(() => {
    if (echo && props.channel) {
        try {
            echo.leave(props.channel);
        } catch (e) {}
    }
});

const sendMessage = async () => {
    if (!newMessage.value.trim() || isSending.value) return;

    const messageText = newMessage.value.trim();
    newMessage.value = '';
    isSending.value = true;

    // Add optimistically
    messages.value.push({
        id: Date.now(),
        content: messageText,
        author_type: 'support',
        author_name: props.employee.full_name,
        created_at: new Date().toISOString(),
    });
    scrollToBottom();

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    try {
        const response = await fetch(route('employee.portal.support-agent.reply', props.ticket.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ 
                message: messageText,
                source: props.ticket.source,
            }),
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        console.log('[SupportAgent] Message sent successfully');
    } catch (error) {
        console.error('[SupportAgent] Failed to send:', error);
        messages.value.pop();
    } finally {
        isSending.value = false;
    }
};

const updateStatus = async () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    try {
        const response = await fetch(route('employee.portal.support-agent.update-status', props.ticket.id), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ 
                status: selectedStatus.value,
                source: props.ticket.source,
            }),
            credentials: 'same-origin',
        });

        if (response.ok) {
            showStatusModal.value = false;
            router.reload({ only: ['ticket'] });
        }
    } catch (error) {
        console.error('[SupportAgent] Failed to update status:', error);
    }
};

const assignToMe = async () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    try {
        await fetch(route('employee.portal.support-agent.assign', props.ticket.id), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ 
                source: props.ticket.source,
            }),
            credentials: 'same-origin',
        });
        
        router.reload({ only: ['ticket'] });
    } catch (error) {
        console.error('[SupportAgent] Failed to assign:', error);
    }
};

let lastTypingTime = 0;
const sendTypingIndicator = () => {
    const now = Date.now();
    if (chatChannel && now - lastTypingTime > 2000) {
        lastTypingTime = now;
        chatChannel.whisper('typing', { name: props.employee.full_name });
    }
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
};

const handleInput = () => {
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
    pending: 'bg-purple-100 text-purple-700',
    resolved: 'bg-green-100 text-green-700',
    closed: 'bg-gray-100 text-gray-700',
}[s] || 'bg-gray-100 text-gray-700');

const getPriorityColor = (p: string) => ({
    urgent: 'text-red-600',
    high: 'text-amber-600',
    medium: 'text-blue-600',
    low: 'text-gray-500',
}[p] || 'text-gray-500');

const getSourceBadge = (source: string) => {
    return source === 'investor' 
        ? 'bg-indigo-100 text-indigo-700' 
        : 'bg-emerald-100 text-emerald-700';
};

const canReply = computed(() => !['closed', 'resolved'].includes(props.ticket.status));
</script>

<template>
    <Head :title="`Ticket #${ticket.ticket_number}`" />

    <EmployeePortalLayout>
        <div class="h-[calc(100vh-180px)] flex flex-col">
            <!-- Header -->
            <div class="bg-white border-b border-gray-200 px-6 py-4 flex items-center gap-4 rounded-t-xl">
                <Link :href="route('employee.portal.support-agent.tickets')" class="p-2 hover:bg-gray-100 rounded-lg">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                </Link>
                <div class="flex-1">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h1 class="text-lg font-semibold text-gray-900">#{{ ticket.ticket_number }}</h1>
                        <span :class="[getSourceBadge(ticket.source), 'px-2 py-0.5 text-xs font-medium rounded capitalize']">
                            {{ ticket.source }}
                        </span>
                        <button 
                            @click="showStatusModal = true"
                            :class="[getStatusColor(ticket.status), 'px-2 py-0.5 text-xs font-medium rounded-full cursor-pointer hover:opacity-80']"
                        >
                            {{ ticket.status.replace('_', ' ') }}
                        </button>
                        <span v-if="isConnected" class="flex items-center gap-1 text-xs text-green-600">
                            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                            Live
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 mt-0.5">{{ ticket.subject }}</p>
                </div>
                <button 
                    v-if="!ticket.assigned_to"
                    @click="assignToMe"
                    class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700"
                >
                    Assign to Me
                </button>
            </div>

            <!-- Main Content -->
            <div class="flex-1 flex overflow-hidden bg-white rounded-b-xl">
                <!-- Chat Area -->
                <div class="flex-1 flex flex-col">
                    <!-- Ticket Details Banner -->
                    <div class="bg-gray-50 px-6 py-3 border-b border-gray-100">
                        <div class="flex items-center gap-6 text-sm flex-wrap">
                            <div class="flex items-center gap-2">
                                <UserIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                                <span class="text-gray-900 font-medium">{{ ticket.user_name }}</span>
                                <span class="text-gray-500">({{ ticket.user_email }})</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <TagIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                                <span class="text-gray-600 capitalize">{{ ticket.category }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <FlagIcon :class="['h-4 w-4', getPriorityColor(ticket.priority)]" aria-hidden="true" />
                                <span class="capitalize" :class="getPriorityColor(ticket.priority)">{{ ticket.priority }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <ClockIcon class="h-4 w-4 text-gray-400" aria-hidden="true" />
                                <span class="text-gray-600">{{ formatDate(ticket.created_at) }}</span>
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
                            :class="['flex', msg.author_type === 'support' ? 'justify-end' : 'justify-start']">
                            <div :class="['max-w-[70%] rounded-2xl px-4 py-3',
                                msg.author_type === 'support' 
                                    ? 'bg-blue-600 text-white rounded-br-md' 
                                    : 'bg-white text-gray-900 rounded-bl-md shadow-sm']">
                                <p v-if="msg.author_type === 'user'" class="text-xs text-blue-600 font-medium mb-1">
                                    {{ msg.author_name }}
                                </p>
                                <p class="text-sm whitespace-pre-wrap">{{ msg.content }}</p>
                                <p :class="['text-xs mt-1', msg.author_type === 'support' ? 'text-blue-200' : 'text-gray-400']">
                                    {{ formatTime(msg.created_at) }}
                                </p>
                            </div>
                        </div>

                        <!-- Typing indicator -->
                        <div v-if="isTyping" class="flex justify-start items-center gap-2">
                            <div class="bg-white rounded-2xl rounded-bl-md px-4 py-3 shadow-sm">
                                <p class="text-xs text-gray-500 mb-1">{{ typingName }} is typing...</p>
                                <div class="flex gap-1">
                                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></span>
                                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                                </div>
                            </div>
                        </div>

                        <div v-if="messages.length === 0" class="text-center py-8 text-gray-500">
                            <p>No messages yet. Start the conversation!</p>
                        </div>
                    </div>

                    <!-- Input -->
                    <div v-if="canReply" class="p-4 bg-white border-t border-gray-200">
                        <div class="flex items-end gap-3">
                            <textarea 
                                v-model="newMessage" 
                                @keydown="handleKeydown"
                                @input="handleInput"
                                rows="2" 
                                placeholder="Type your reply..."
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
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Update Modal -->
        <Teleport to="body">
            <div v-if="showStatusModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="bg-white rounded-xl p-6 w-full max-w-sm mx-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">Update Status</h3>
                        <button @click="showStatusModal = false" class="p-1 hover:bg-gray-100 rounded">
                            <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                    </div>
                    <select 
                        v-model="selectedStatus"
                        class="w-full border-gray-300 rounded-lg mb-4"
                    >
                        <option value="open">Open</option>
                        <option value="in_progress">In Progress</option>
                        <option value="pending">Pending</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                    <div class="flex gap-3">
                        <button 
                            @click="showStatusModal = false"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button 
                            @click="updateStatus"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                        >
                            Update
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </EmployeePortalLayout>
</template>
