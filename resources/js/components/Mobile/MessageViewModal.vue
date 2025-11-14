<template>
    <Transition name="slide-up">
        <div v-if="show" class="fixed top-0 left-0 right-0 flex flex-col" style="background-color: #e5ddd5; z-index: 9999; bottom: 70px;">
            <!-- Modern Header -->
            <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white shadow-2xl flex-shrink-0">
                <div class="px-4 py-4 backdrop-blur-sm bg-white/10">
                    <div class="flex items-center gap-3">
                        <button
                            @click="$emit('close')"
                            class="p-2.5 hover:bg-white/20 rounded-xl transition-all duration-200 active:scale-95 backdrop-blur-sm"
                        >
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <div class="flex-1 min-w-0">
                            <h1 class="text-lg font-bold truncate tracking-tight">
                                {{ conversation[0]?.subject || 'Message' }}
                            </h1>
                            <p class="text-sm text-white/80 truncate mt-0.5">
                                {{ conversation[0]?.senderName || 'Loading...' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex-1 flex flex-col items-center justify-center">
                <div class="relative">
                    <div class="animate-spin rounded-full h-16 w-16 border-4 border-blue-200"></div>
                    <div class="animate-spin rounded-full h-16 w-16 border-4 border-t-blue-600 absolute top-0 left-0"></div>
                </div>
                <p class="text-gray-600 mt-6 font-medium">Loading conversation...</p>
            </div>

            <!-- Messages Thread (WhatsApp style) -->
            <div v-if="!loading" class="flex-1 overflow-y-auto px-3 py-4 space-y-3">
                <!-- Messages as chat bubbles -->
                <div
                    v-for="(message, index) in conversation"
                    :key="message.id"
                    :class="[
                        'flex',
                        message.senderId === $page.props.auth.user.id ? 'justify-end' : 'justify-start'
                    ]"
                >
                    <!-- Message Bubble -->
                    <div
                        :class="[
                            'max-w-[85%] rounded-2xl px-4 py-3 shadow-sm',
                            message.senderId === $page.props.auth.user.id
                                ? 'bg-blue-500 text-white rounded-br-sm'
                                : 'bg-white text-gray-900 rounded-bl-sm'
                        ]"
                    >
                        <!-- Sender name (only for received messages) -->
                        <p 
                            v-if="message.senderId !== $page.props.auth.user.id" 
                            class="text-xs font-semibold text-blue-600 mb-1"
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
                                <!-- Double gray checks for delivered but not read -->
                                <svg 
                                    v-if="!message.isRead"
                                    class="w-[18px] h-4 text-blue-100"
                                    fill="none" 
                                    viewBox="0 0 20 16" 
                                    stroke="currentColor" 
                                    stroke-width="2"
                                >
                                    <!-- First check -->
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M1 8l3 3 6-6" />
                                    <!-- Second check (slightly offset) -->
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 8l3 3 6-6" />
                                </svg>
                                <!-- Double blue checks for read -->
                                <svg 
                                    v-else
                                    class="w-[18px] h-4 text-blue-400"
                                    fill="none" 
                                    viewBox="0 0 20 16" 
                                    stroke="currentColor" 
                                    stroke-width="2"
                                >
                                    <!-- First check -->
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M1 8l3 3 6-6" />
                                    <!-- Second check (slightly offset) -->
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 8l3 3 6-6" />
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reply Input (WhatsApp style) -->
            <div v-if="!loading && conversation.length > 0" class="flex-shrink-0 bg-gray-100 border-t border-gray-300 safe-bottom" style="position: relative; z-index: 10000;">
                <form @submit.prevent="sendReply" class="p-3">
                    <div class="flex items-center gap-2">
                        <div class="flex-1">
                            <textarea
                                ref="replyInput"
                                v-model="replyBody"
                                rows="1"
                                class="w-full rounded-3xl border-2 border-gray-300 bg-white focus:border-blue-500 focus:outline-none resize-none px-4 py-3 text-base max-h-32 overflow-y-auto"
                                placeholder="Type a message..."
                                :disabled="sending"
                                @input="autoResize"
                                @keydown.enter.exact.prevent="sendReply"
                            ></textarea>
                        </div>
                        <!-- Send button -->
                        <button
                            type="submit"
                            :disabled="sending || !replyBody.trim()"
                            class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-500 hover:bg-blue-600 text-white disabled:bg-gray-300 disabled:cursor-not-allowed active:scale-95 transition-all flex items-center justify-center shadow-lg"
                        >
                            <svg v-if="sending" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 74 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Transition>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';
import Swal from 'sweetalert2';

const page = usePage();

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
    show: boolean;
    messageId: number | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const conversation = ref<Message[]>([]);
const loading = ref(false);
const sending = ref(false);
const replyBody = ref('');
const replyInput = ref<HTMLTextAreaElement | null>(null);

async function loadConversation() {
    if (!props.messageId) {
        console.log('❌ No messageId provided');
        return;
    }
    
    loading.value = true;
    try {
        console.log('🔄 Loading conversation for message:', props.messageId);
        const response = await axios.get(route('mygrownet.messages.show', props.messageId), {
            headers: { 
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        console.log('📦 Conversation response:', response.data);
        console.log('📋 Response keys:', Object.keys(response.data));
        console.log('📧 Messages array:', response.data.messages);
        
        // Handle both direct data and props.messages structure
        if (response.data.messages && Array.isArray(response.data.messages)) {
            // Filter to only show the specific message and its replies
            const allMessages = response.data.messages;
            
            // Find the root message (the one we clicked on or its parent)
            const clickedMessage = allMessages.find(m => m.id === props.messageId);
            const rootMessageId = clickedMessage?.parentId || props.messageId;
            
            // Get the root message and all its replies
            conversation.value = allMessages.filter(m => 
                m.id === rootMessageId || m.parentId === rootMessageId
            ).sort((a, b) => new Date(a.createdAt).getTime() - new Date(b.createdAt).getTime());
            
            console.log('✅ Loaded', conversation.value.length, 'messages in thread');
            if (conversation.value.length > 0) {
                console.log('📧 Root message:', conversation.value[0]);
            } else {
                console.warn('⚠️ No messages in thread!');
            }
        } else if (Array.isArray(response.data)) {
            conversation.value = response.data;
            console.log('✅ Loaded', conversation.value.length, 'messages from array');
        } else {
            console.error('❌ Unexpected response structure');
            console.log('📋 Full response:', JSON.stringify(response.data, null, 2));
            conversation.value = [];
        }
        
        // Mark as read
        if (conversation.value.length > 0 && !conversation.value[0].isRead) {
            await axios.post(route('mygrownet.messages.read', props.messageId));
        }
    } catch (error) {
        console.error('Failed to load conversation:', error);
        Swal.fire({
            icon: 'error',
            title: 'Failed to load',
            text: 'Could not load the conversation',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
        });
    } finally {
        loading.value = false;
    }
}

function autoResize() {
    if (replyInput.value) {
        replyInput.value.style.height = 'auto';
        replyInput.value.style.height = replyInput.value.scrollHeight + 'px';
    }
}

async function sendReply() {
    if (!replyBody.value.trim() || !conversation.value[0]) return;
    
    const messageText = replyBody.value;
    const originalMessage = conversation.value[0];
    
    // Optimistically add message to conversation (WhatsApp style)
    const tempMessage = {
        id: Date.now(), // Temporary ID
        senderId: page.props.auth.user.id,
        senderName: page.props.auth.user.name,
        recipientId: originalMessage.senderId,
        recipientName: originalMessage.senderName,
        subject: originalMessage.subject.startsWith('Re:') 
            ? originalMessage.subject 
            : `Re: ${originalMessage.subject}`,
        body: messageText,
        preview: messageText.substring(0, 100),
        isRead: false,
        readAt: null,
        parentId: originalMessage.id,
        createdAt: new Date().toISOString(),
        updatedAt: new Date().toISOString(),
    };
    
    // Add to conversation immediately
    conversation.value.push(tempMessage);
    
    // Clear input
    replyBody.value = '';
    if (replyInput.value) {
        replyInput.value.style.height = 'auto';
    }
    
    // Scroll to bottom
    setTimeout(() => {
        const messagesContainer = document.querySelector('.overflow-y-auto');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }, 100);
    
    // Send to server in background
    sending.value = true;
    try {
        const response = await axios.post(route('mygrownet.messages.store'), {
            recipient_id: originalMessage.senderId,
            subject: tempMessage.subject,
            body: messageText,
            parent_id: originalMessage.id,
        });
        
        // Update temp message with real ID from server if needed
        if (response.data.message?.id) {
            const index = conversation.value.findIndex(m => m.id === tempMessage.id);
            if (index !== -1) {
                conversation.value[index].id = response.data.message.id;
            }
        }
    } catch (error) {
        console.error('Failed to send reply:', error);
        // Remove the optimistic message on error
        const index = conversation.value.findIndex(m => m.id === tempMessage.id);
        if (index !== -1) {
            conversation.value.splice(index, 1);
        }
        // Show error toast only on failure
        Swal.fire({
            icon: 'error',
            title: 'Failed to send',
            text: 'Please try again',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
        });
    } finally {
        sending.value = false;
    }
}

function formatTime(dateString: string): string {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}

function formatDateTime(dateString: string): string {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffHours < 24) {
        return date.toLocaleString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
        });
    } else if (diffDays < 7) {
        return date.toLocaleString('en-US', {
            weekday: 'short',
            hour: 'numeric',
            minute: '2-digit',
        });
    } else {
        return date.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
        });
    }
}

watch(() => props.show, (newVal) => {
    if (newVal && props.messageId) {
        loadConversation();
    } else {
        // Reset state when closing
        conversation.value = [];
        replyBody.value = '';
    }
});
</script>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
    transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-up-enter-from,
.slide-up-leave-to {
    transform: translateY(100%);
}

@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.4s ease-out forwards;
    opacity: 0;
}

/* Smooth scrolling */
.overflow-y-auto {
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
}

/* Hide scrollbar but keep functionality */
.overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.3);
    border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: rgba(156, 163, 175, 0.5);
}

/* Safe area for mobile devices */
.safe-bottom {
    padding-bottom: env(safe-area-inset-bottom);
}
</style>
