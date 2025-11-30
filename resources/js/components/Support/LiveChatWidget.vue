<script setup lang="ts">
/**
 * Unified Live Chat Widget
 * 
 * Based on the working Employee LiveChatWidget implementation.
 * Supports: Employee Portal, Investor Portal, Member Portal
 * 
 * Usage:
 * <UnifiedLiveChatWidget user-type="investor" :user-id="investor.id" :user-name="investor.name" />
 * <UnifiedLiveChatWidget user-type="member" :user-id="user.id" :user-name="user.name" />
 * <UnifiedLiveChatWidget user-type="employee" :user-id="employee.id" :user-name="employee.full_name" />
 */
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import { route } from 'ziggy-js';
import {
    ChatBubbleLeftRightIcon,
    XMarkIcon,
    PaperAirplaneIcon,
    MinusIcon,
    TicketIcon,
    PlusIcon,
    ArrowLeftIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline';

interface Message {
    id?: number;
    sender_id: number;
    sender_name: string;
    sender_type: 'user' | 'support' | 'employee' | 'investor' | 'member';
    message: string;
    sent_at: string;
}

interface ExistingTicket {
    id: number;
    ticket_number: string;
    subject: string;
    status: string;
    updated_at: string;
    comments_count: number;
}

interface Props {
    userType: 'employee' | 'investor' | 'member';
    userId: number;
    userName: string;
    ticketId?: number;
}

const props = withDefaults(defineProps<Props>(), {
    userId: 0,
    userName: 'Guest',
    ticketId: undefined,
});

const emit = defineEmits<{
    ticketCreated: [ticketId: number];
}>();

// View states: 'menu' | 'new-chat' | 'chat'
const currentView = ref<'menu' | 'new-chat' | 'chat'>('menu');
const isOpen = ref(false);
const isMinimized = ref(false);
const messages = ref<Message[]>([]);
const newMessage = ref('');
const isConnected = ref(false);
const isTyping = ref(false);
const typingName = ref('');
const chatContainer = ref<HTMLElement | null>(null);
const echoAvailable = ref(false);
const isMobile = ref(false);

// Pre-chat form
const selectedCategory = ref('general');
const initialMessage = ref('');

// Categories based on user type
const categories = computed(() => {
    if (props.userType === 'investor') {
        return [
            { value: 'general', label: 'General Inquiry' },
            { value: 'investment', label: 'Investment Question' },
            { value: 'withdrawal', label: 'Withdrawal Help' },
            { value: 'account', label: 'Account Issue' },
            { value: 'returns', label: 'Returns & Dividends' },
        ];
    } else if (props.userType === 'member') {
        return [
            { value: 'general', label: 'General Inquiry' },
            { value: 'subscription', label: 'Subscription Help' },
            { value: 'learning', label: 'Learning Content' },
            { value: 'commission', label: 'Commissions & Earnings' },
            { value: 'account', label: 'Account Issue' },
        ];
    }
    // Employee default
    return [
        { value: 'general', label: 'General Inquiry' },
        { value: 'technical', label: 'Technical Issue' },
        { value: 'billing', label: 'Billing & Payments' },
        { value: 'account', label: 'Account Help' },
        { value: 'feedback', label: 'Feedback & Suggestions' },
    ];
});

// Existing tickets/conversations
const existingTickets = ref<ExistingTicket[]>([]);
const isLoadingTickets = ref(false);

// Pagination for long histories
const maxHistoryMessages = 50;
const hasMoreMessages = ref(false);
const totalMessages = ref(0);

// Internal ticket ID tracking
const currentTicketId = ref<number | undefined>(props.ticketId);
const currentTicketNumber = ref<string>('');

// Echo instance
let echo: any = null;
let chatChannel: any = null;
let typingTimeout: ReturnType<typeof setTimeout> | null = null;

// Route helpers based on user type
const getRoute = (action: string, ticketId?: number): string => {
    try {
        switch (props.userType) {
            case 'investor':
                if (action === 'list') return route('investor.support.list-json');
                if (action === 'show') return route('investor.support.show-json', { id: ticketId });
                if (action === 'create') return route('investor.support.quick-chat');
                if (action === 'chat') return route('investor.support.chat', { id: ticketId });
                break;
            case 'member':
                if (action === 'list') return route('mygrownet.support.list-json');
                if (action === 'show') return route('mygrownet.support.show-json', { id: ticketId });
                if (action === 'create') return route('mygrownet.support.quick-chat');
                if (action === 'chat') return route('mygrownet.support.chat', { id: ticketId });
                break;
            default: // employee
                if (action === 'list') return route('employee.portal.support.index');
                if (action === 'show') return route('employee.portal.support.show', { ticket: ticketId });
                if (action === 'create') return route('employee.portal.support.quick-chat');
                if (action === 'chat') return route('employee.portal.support.chat', { ticket: ticketId });
        }
    } catch (e) {
        console.error('[LiveChat] Route generation failed:', e);
    }
    return '';
};

// Channel name based on user type
const getChannelName = (ticketId: number): string => {
    switch (props.userType) {
        case 'investor': return `investor.support.${ticketId}`;
        case 'member': return `member.support.${ticketId}`;
        default: return `support.ticket.${ticketId}`;
    }
};

// Get sender type for messages
const getSenderType = (): string => {
    return props.userType; // 'employee', 'investor', or 'member'
};

// Initialize Echo if available
const initEcho = () => {
    if ((window as any).Echo) {
        echo = (window as any).Echo;
        echoAvailable.value = true;
        if (currentTicketId.value) {
            subscribeToChat();
        }
        return;
    }
    
    // Fallback: try useEcho from @laravel/echo-vue
    import('@laravel/echo-vue').then(({ useEcho }) => {
        const echoInstance = useEcho();
        if (echoInstance) {
            echo = echoInstance;
            echoAvailable.value = true;
            if (props.ticketId) {
                subscribeToChat();
            }
        }
    }).catch(() => {
        // Real-time features disabled - chat still works via HTTP
    });
};

// Subscribe to chat channel when ticket exists
const subscribeToChat = () => {
    if (!currentTicketId.value || !echo || !echoAvailable.value) return;

    try {
        const channelName = getChannelName(currentTicketId.value);
        console.log('[LiveChat] Subscribing to channel:', channelName);
        chatChannel = echo.private(channelName);

        // Listen for connection events
        if (echo.connector && echo.connector.pusher) {
            echo.connector.pusher.connection.bind('connected', () => {
                console.log('[LiveChat] WebSocket connected');
                isConnected.value = true;
            });
            
            echo.connector.pusher.connection.bind('disconnected', () => {
                console.log('[LiveChat] WebSocket disconnected');
                isConnected.value = false;
            });
            
            const state = echo.connector.pusher.connection.state;
            isConnected.value = state === 'connected';
        } else {
            isConnected.value = true;
        }

        chatChannel.listen('.chat.message', (data: any) => {
            console.log('[LiveChat] Received message via WebSocket:', data);
            
            // Check if this message already exists (by content + timestamp or by id)
            const isDuplicate = messages.value.some(m => {
                // If we have an ID, check by ID
                if (data.id && m.id === data.id) return true;
                // Otherwise check by content + approximate time (within 5 seconds)
                const timeDiff = Math.abs(new Date(m.sent_at).getTime() - new Date(data.sent_at).getTime());
                return m.message === data.message && m.sender_id === data.sender_id && timeDiff < 5000;
            });
            
            if (isDuplicate) {
                console.log('[LiveChat] Skipping duplicate message');
                return;
            }
            
            // Don't add our own messages (they're already added optimistically)
            // For investor: sender_type will be 'investor', for member: 'member', for employee: 'employee'
            const isOwnMessage = data.sender_id === props.userId && 
                (data.sender_type === props.userType || data.sender_type === 'user');
            
            if (!isOwnMessage) {
                console.log('[LiveChat] Adding new message from:', data.sender_type);
                messages.value.push({
                    id: data.id,
                    sender_id: data.sender_id,
                    sender_name: data.sender_name,
                    sender_type: data.sender_type === 'support' ? 'support' : 'user',
                    message: data.message,
                    sent_at: data.sent_at,
                });
                scrollToBottom();
            } else {
                console.log('[LiveChat] Skipping own message');
            }
        });

        chatChannel.listenForWhisper('typing', (data: { name: string }) => {
            typingName.value = data.name || 'Support';
            isTyping.value = true;
            if (typingTimeout) clearTimeout(typingTimeout);
            typingTimeout = setTimeout(() => {
                isTyping.value = false;
                typingName.value = '';
            }, 3000);
        });

    } catch (e) {
        console.warn('[LiveChat] Failed to subscribe to chat channel:', e);
        isConnected.value = false;
    }
};

const unsubscribeFromChat = () => {
    if (!currentTicketId.value || !echo || !echoAvailable.value) return;
    try {
        echo.leave(getChannelName(currentTicketId.value));
    } catch (e) {
        // Ignore errors when leaving
    }
    isConnected.value = false;
    chatChannel = null;
};

// Watch for ticket ID changes
watch(() => props.ticketId, (newId) => {
    if (newId) {
        currentTicketId.value = newId;
    }
});

watch(currentTicketId, (newId, oldId) => {
    if (oldId) {
        unsubscribeFromChat();
    }
    if (newId && echoAvailable.value) {
        subscribeToChat();
    }
});

const checkMobile = () => {
    isMobile.value = window.innerWidth < 1024;
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
    initEcho();
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
    unsubscribeFromChat();
});

// Expose method to open chat from parent
const openChat = () => {
    isOpen.value = true;
    if (isMinimized.value) {
        isMinimized.value = false;
    }
};

defineExpose({
    openChat
});

const scrollToBottom = async () => {
    await nextTick();
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
};

const isSending = ref(false);

const sendMessage = (event?: Event) => {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    if (!newMessage.value.trim() || isSending.value) {
        return;
    }

    const messageText = newMessage.value.trim();
    newMessage.value = '';
    isSending.value = true;

    console.log('[LiveChat] Sending message:', messageText);

    // Add message optimistically
    const optimisticMessage: Message = {
        sender_id: props.userId,
        sender_name: props.userName,
        sender_type: 'user',
        message: messageText,
        sent_at: new Date().toISOString(),
    };
    messages.value.push(optimisticMessage);
    scrollToBottom();

    // Send to server
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const endpoint = currentTicketId.value 
        ? getRoute('chat', currentTicketId.value)
        : getRoute('create');

    console.log('[LiveChat] Endpoint:', endpoint);

    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ message: messageText }),
        credentials: 'same-origin',
    })
    .then(response => {
        console.log('[LiveChat] Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('[LiveChat] Message sent successfully:', data);
        isSending.value = false;
        if (data.ticket_id && !currentTicketId.value) {
            console.log('[LiveChat] New ticket created, ID:', data.ticket_id);
            currentTicketId.value = data.ticket_id;
            currentTicketNumber.value = data.ticket_number || '';
            emit('ticketCreated', data.ticket_id);
            if (echoAvailable.value) {
                subscribeToChat();
            }
        }
    })
    .catch(error => {
        console.error('Failed to send message:', error);
        messages.value.pop();
        isSending.value = false;
    });
};

// Send typing indicator (debounced)
let lastTypingTime = 0;
const sendTypingIndicator = () => {
    const now = Date.now();
    if (chatChannel && now - lastTypingTime > 2000) {
        lastTypingTime = now;
        chatChannel.whisper('typing', { name: props.userName });
    }
};

const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
};

const handleInput = (e: Event) => {
    const target = e.target as HTMLTextAreaElement;
    target.style.height = 'auto';
    target.style.height = Math.min(target.scrollHeight, 120) + 'px';
    
    if (newMessage.value.trim()) {
        sendTypingIndicator();
    }
};

const toggleChat = () => {
    if (isMinimized.value) {
        isMinimized.value = false;
    } else {
        isOpen.value = !isOpen.value;
    }
};

const minimizeChat = () => {
    isMinimized.value = true;
};

const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const formatRelativeTime = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 1) return 'Just now';
    if (mins < 60) return `${mins}m ago`;
    if (mins < 1440) return `${Math.floor(mins / 60)}h ago`;
    return date.toLocaleDateString();
};

// Load existing tickets for the user
const loadExistingTickets = async () => {
    if (!props.userId) return;
    
    isLoadingTickets.value = true;
    try {
        const response = await fetch(getRoute('list'), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
        
        if (response.ok) {
            const data = await response.json();
            existingTickets.value = (data.tickets || []).filter((t: ExistingTicket) => 
                ['open', 'in_progress', 'pending'].includes(t.status)
            ).slice(0, 5);
        }
    } catch (e) {
        console.warn('[LiveChat] Failed to load existing tickets:', e);
    } finally {
        isLoadingTickets.value = false;
    }
};

// Start a new chat with category
const startNewChat = async () => {
    if (!initialMessage.value.trim()) {
        return;
    }
    
    const messageText = initialMessage.value.trim();
    initialMessage.value = '';
    currentView.value = 'chat';
    
    // Add optimistic message
    messages.value = [{
        sender_id: props.userId,
        sender_name: props.userName,
        sender_type: 'user',
        message: messageText,
        sent_at: new Date().toISOString(),
    }];
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    try {
        const response = await fetch(getRoute('create'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ 
                message: messageText,
                category: selectedCategory.value,
            }),
            credentials: 'same-origin',
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.ticket_id) {
                currentTicketId.value = data.ticket_id;
                currentTicketNumber.value = data.ticket_number || '';
                emit('ticketCreated', data.ticket_id);
                
                if (echoAvailable.value) {
                    subscribeToChat();
                }
            }
        }
    } catch (e) {
        console.error('[LiveChat] Failed to create ticket:', e);
    }
};

// Open an existing conversation
const openExistingTicket = async (ticket: ExistingTicket) => {
    currentTicketId.value = ticket.id;
    currentTicketNumber.value = ticket.ticket_number;
    currentView.value = 'chat';
    messages.value = [];
    hasMoreMessages.value = false;
    
    try {
        const response = await fetch(getRoute('show', ticket.id), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
        
        if (response.ok) {
            const data = await response.json();
            const allMessages: Message[] = [];
            
            // Add original description as first message
            if (data.ticket?.description) {
                allMessages.push({
                    sender_id: props.userId,
                    sender_name: props.userName,
                    sender_type: 'user',
                    message: data.ticket.description,
                    sent_at: data.ticket.created_at,
                });
            }
            
            // Add comments
            if (data.ticket?.comments) {
                data.ticket.comments.forEach((c: any) => {
                    allMessages.push({
                        id: c.id,
                        sender_id: c.author_id || 0,
                        sender_name: c.author?.full_name || c.display_author_name || 'Support',
                        sender_type: c.author_type === 'support' ? 'support' : 'user',
                        message: c.content || c.comment,
                        sent_at: c.created_at,
                    });
                });
            }
            
            totalMessages.value = allMessages.length;
            
            if (allMessages.length > maxHistoryMessages) {
                hasMoreMessages.value = true;
                messages.value = allMessages.slice(-maxHistoryMessages);
            } else {
                messages.value = allMessages;
            }
            
            scrollToBottom();
        }
    } catch (e) {
        console.error('[LiveChat] Failed to load ticket:', e);
    }
    
    if (echoAvailable.value) {
        subscribeToChat();
    }
};

// Go back to menu
const goBackToMenu = () => {
    currentView.value = 'menu';
    unsubscribeFromChat();
    currentTicketId.value = undefined;
    currentTicketNumber.value = '';
    messages.value = [];
    selectedCategory.value = 'general';
    hasMoreMessages.value = false;
};

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        open: 'bg-blue-100 text-blue-700',
        in_progress: 'bg-amber-100 text-amber-700',
        pending: 'bg-purple-100 text-purple-700',
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
};

// Initialize when chat opens
watch(isOpen, (open) => {
    if (open) {
        if (props.ticketId) {
            currentTicketId.value = props.ticketId;
            currentView.value = 'chat';
        } else {
            currentView.value = 'menu';
            loadExistingTickets();
        }
    }
});
</script>

<template>
    <!-- Chat Button - Hidden on mobile (< 1024px), shown on desktop -->
    <button
        v-if="!isOpen && !isMobile"
        @click="toggleChat"
        class="fixed bottom-6 right-6 p-4 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 transition-all hover:scale-105"
        style="z-index: 100000;"
        aria-label="Open live chat"
    >
        <ChatBubbleLeftRightIcon class="h-6 w-6" aria-hidden="true" />
    </button>

    <!-- Chat Window - Responsive positioning -->
    <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-4"
    >
        <div
            v-if="isOpen"
            :class="[
                'fixed bg-white shadow-2xl overflow-hidden transition-all duration-200',
                isMobile 
                    ? (isMinimized ? 'bottom-20 right-4 left-4 h-14 rounded-xl' : 'inset-4 bottom-20 rounded-2xl')
                    : (isMinimized ? 'bottom-6 right-6 w-72 h-14 rounded-xl' : 'bottom-6 right-6 w-96 h-[520px] max-h-[70vh] rounded-2xl')
            ]"
            style="z-index: 100000;"
        >
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <!-- Back button when in chat or new-chat view -->
                    <button 
                        v-if="currentView === 'chat' || currentView === 'new-chat'"
                        @click="goBackToMenu"
                        class="p-1 hover:bg-white/20 rounded-lg transition-colors"
                        aria-label="Back to menu"
                    >
                        <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                    </button>
                    <div class="relative" v-else>
                        <ChatBubbleLeftRightIcon class="h-6 w-6" aria-hidden="true" />
                    </div>
                    <div v-if="!isMinimized">
                        <h3 class="font-semibold">
                            {{ currentView === 'chat' && currentTicketNumber ? `#${currentTicketNumber}` : 'Support' }}
                        </h3>
                        <p class="text-xs text-blue-100">
                            {{ currentView === 'chat' ? (isConnected ? 'Live Chat Active' : 'Chat in progress') : 'How can we help?' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button 
                        @click="minimizeChat"
                        class="p-1.5 hover:bg-white/20 rounded-lg transition-colors"
                        aria-label="Minimize chat"
                    >
                        <MinusIcon class="h-4 w-4" aria-hidden="true" />
                    </button>
                    <button 
                        @click="isOpen = false"
                        class="p-1.5 hover:bg-white/20 rounded-lg transition-colors"
                        aria-label="Close chat"
                    >
                        <XMarkIcon class="h-4 w-4" aria-hidden="true" />
                    </button>
                </div>
            </div>

            <!-- Body (hidden when minimized) -->
            <template v-if="!isMinimized">
                <!-- MENU VIEW -->
                <div v-if="currentView === 'menu'" class="h-[calc(100%-64px)] flex flex-col">
                    <div class="flex-1 overflow-y-auto p-4 space-y-4">
                        <!-- Welcome -->
                        <div class="text-center py-2">
                            <p class="text-gray-600">Hi {{ userName }}! 👋</p>
                            <p class="text-sm text-gray-500">How can we help you today?</p>
                        </div>

                        <!-- Start New Conversation -->
                        <button
                            @click="currentView = 'new-chat'"
                            class="w-full flex items-center gap-3 p-4 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors text-left"
                        >
                            <div class="p-2 bg-blue-600 rounded-lg">
                                <PlusIcon class="h-5 w-5 text-white" aria-hidden="true" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Start New Conversation</p>
                                <p class="text-sm text-gray-500">Get help with a new issue</p>
                            </div>
                        </button>

                        <!-- Existing Conversations -->
                        <div v-if="existingTickets.length > 0">
                            <h4 class="text-sm font-medium text-gray-700 mb-2 flex items-center gap-2">
                                <ClockIcon class="h-4 w-4" aria-hidden="true" />
                                Your Open Conversations
                            </h4>
                            <div class="space-y-2">
                                <button
                                    v-for="ticket in existingTickets"
                                    :key="ticket.id"
                                    @click="openExistingTicket(ticket)"
                                    class="w-full flex items-center gap-3 p-3 bg-white border border-gray-200 hover:border-blue-300 hover:bg-gray-50 rounded-xl transition-colors text-left"
                                >
                                    <div class="p-2 bg-gray-100 rounded-lg">
                                        <TicketIcon class="h-4 w-4 text-gray-600" aria-hidden="true" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 truncate text-sm">{{ ticket.subject }}</p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span :class="[getStatusColor(ticket.status), 'px-1.5 py-0.5 text-xs rounded']">
                                                {{ ticket.status.replace('_', ' ') }}
                                            </span>
                                            <span class="text-xs text-gray-400">{{ formatRelativeTime(ticket.updated_at) }}</span>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Loading -->
                        <div v-if="isLoadingTickets" class="text-center py-4">
                            <div class="animate-spin h-6 w-6 border-2 border-blue-600 border-t-transparent rounded-full mx-auto"></div>
                        </div>
                    </div>
                </div>

                <!-- NEW CHAT VIEW (Pre-chat form) -->
                <div v-else-if="currentView === 'new-chat'" class="h-[calc(100%-64px)] flex flex-col">
                    <div class="flex-1 overflow-y-auto p-4 space-y-4">
                        <p class="text-sm text-gray-600">Please select a category and describe your issue:</p>
                        
                        <!-- Category Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                            <select
                                v-model="selectedCategory"
                                class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option v-for="cat in categories" :key="cat.value" :value="cat.value">
                                    {{ cat.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Message -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">How can we help?</label>
                            <textarea
                                v-model="initialMessage"
                                rows="4"
                                placeholder="Describe your issue or question..."
                                class="w-full px-2 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none transition-all placeholder:text-gray-400"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Start Chat Button -->
                    <div class="p-4 border-t border-gray-100">
                        <button
                            @click="startNewChat"
                            :disabled="!initialMessage.trim()"
                            class="w-full py-3 bg-blue-600 text-white rounded-xl font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            Start Conversation
                        </button>
                    </div>
                </div>

                <!-- CHAT VIEW -->
                <div v-else-if="currentView === 'chat'" class="h-[calc(100%-64px)] flex flex-col">
                    <!-- Messages -->
                    <div 
                        ref="chatContainer"
                        class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50"
                    >
                        <!-- Load More / View Full History -->
                        <div v-if="hasMoreMessages" class="text-center pb-2">
                            <span class="text-sm text-gray-500">
                                {{ totalMessages - messages.length }} earlier messages
                            </span>
                        </div>

                        <div
                            v-for="(msg, index) in messages"
                            :key="index"
                            :class="['flex', msg.sender_type !== 'support' ? 'justify-end' : 'justify-start']"
                        >
                            <div
                                :class="[
                                    'max-w-[80%] rounded-2xl px-4 py-2',
                                    msg.sender_type !== 'support'
                                        ? 'bg-blue-600 text-white rounded-br-md'
                                        : 'bg-white text-gray-900 rounded-bl-md shadow-sm'
                                ]"
                            >
                                <p v-if="msg.sender_type === 'support'" class="text-xs text-blue-600 font-medium mb-1">
                                    {{ msg.sender_name }}
                                </p>
                                <p class="text-sm whitespace-pre-wrap">{{ msg.message }}</p>
                                <p :class="['text-xs mt-1', msg.sender_type !== 'support' ? 'text-blue-200' : 'text-gray-400']">
                                    {{ formatTime(msg.sent_at) }}
                                </p>
                            </div>
                        </div>

                        <!-- Typing indicator -->
                        <div v-if="isTyping" class="flex justify-start items-center gap-2">
                            <div class="bg-white rounded-2xl rounded-bl-md px-4 py-3 shadow-sm">
                                <p class="text-xs text-gray-500 mb-1">{{ typingName || 'Support' }} is typing...</p>
                                <div class="flex gap-1">
                                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                                    <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Empty state -->
                        <div v-if="messages.length === 0" class="text-center py-8 text-gray-500">
                            <p>Loading conversation...</p>
                        </div>
                    </div>

                    <!-- Input -->
                    <div class="p-4 border-t border-gray-100 bg-white">
                        <div class="flex items-end gap-2">
                            <textarea
                                v-model="newMessage"
                                @keydown="handleKeydown"
                                @input="handleInput"
                                placeholder="Type your message..."
                                rows="2"
                                class="flex-1 resize-none border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent max-h-[100px] overflow-y-auto"
                            ></textarea>
                            <button
                                type="button"
                                @click.prevent="sendMessage"
                                :disabled="!newMessage.trim() || isSending"
                                class="flex-shrink-0 p-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                                aria-label="Send message"
                            >
                                <svg v-if="isSending" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <PaperAirplaneIcon v-else class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </Transition>
</template>

<style scoped>
@keyframes bounce {
    0%, 60%, 100% {
        transform: translateY(0);
    }
    30% {
        transform: translateY(-4px);
    }
}
</style>
