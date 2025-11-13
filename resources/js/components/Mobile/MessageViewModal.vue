<template>
    <Transition name="slide-up">
        <div v-if="show" class="fixed inset-0 z-[60] bg-gradient-to-br from-gray-50 to-gray-100 flex flex-col">
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

            <!-- Messages Thread -->
            <div v-else class="flex-1 overflow-y-auto px-4 py-4 space-y-4">
                <!-- Messages -->
                <div
                    v-for="(message, index) in conversation"
                    :key="message.id"
                    class="animate-fade-in"
                    :style="{ animationDelay: `${index * 0.05}s` }"
                >
                    <!-- Message Card -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow duration-200">
                        <!-- Message Header -->
                        <div class="flex items-start gap-3 mb-4">
                            <div class="w-11 h-11 rounded-full bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-500 flex items-center justify-center flex-shrink-0 shadow-lg">
                                <span class="text-white font-bold text-sm">
                                    {{ message.senderName.charAt(0).toUpperCase() }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-900">{{ message.senderName }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ formatDateTime(message.createdAt) }}</p>
                            </div>
                            <!-- Reply indicator for threaded messages -->
                            <div v-if="index > 0" class="flex-shrink-0">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                    Reply
                                </span>
                            </div>
                        </div>
                        
                        <!-- Message Body -->
                        <div class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed pl-14">
                            {{ message.body }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reply Input (Compact inline design like WhatsApp) -->
            <div v-if="conversation.length > 0" class="flex-shrink-0 bg-white border-t border-gray-200 shadow-lg">
                <form @submit.prevent="sendReply" class="p-3">
                    <div class="flex items-end gap-2">
                        <div class="flex-1 relative">
                            <textarea
                                ref="replyInput"
                                v-model="replyBody"
                                rows="1"
                                class="w-full rounded-2xl border-2 border-gray-300 bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-30 resize-none transition-all duration-200 px-4 py-2.5 pr-12 text-sm max-h-24 overflow-y-auto"
                                placeholder="Type a reply..."
                                :disabled="sending"
                                required
                                @input="autoResize"
                            ></textarea>
                            <!-- Character count inside textarea -->
                            <div class="absolute bottom-2 right-3 text-xs text-gray-400 pointer-events-none">
                                {{ replyBody.length }}
                            </div>
                        </div>
                        <!-- Send button as circular icon -->
                        <button
                            type="submit"
                            :disabled="sending || !replyBody.trim()"
                            class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white disabled:opacity-40 disabled:cursor-not-allowed hover:shadow-lg active:scale-95 transition-all duration-200 flex items-center justify-center"
                            :class="{ 'animate-pulse': sending }"
                        >
                            <svg v-if="sending" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 74 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <svg v-else class="w-5 h-5 transform rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-2-9-9-2-9 2 2 9 9-2zm0 0v-8" />
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
import axios from 'axios';
import Swal from 'sweetalert2';

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
    
    sending.value = true;
    try {
        const originalMessage = conversation.value[0];
        
        await axios.post(route('mygrownet.messages.store'), {
            recipient_id: originalMessage.senderId,
            subject: originalMessage.subject.startsWith('Re:') 
                ? originalMessage.subject 
                : `Re: ${originalMessage.subject}`,
            body: replyBody.value,
            parent_id: originalMessage.id,
        });
        
        replyBody.value = '';
        if (replyInput.value) {
            replyInput.value.style.height = 'auto';
        }
        await loadConversation();
        
        Swal.fire({
            icon: 'success',
            title: 'Reply Sent!',
            text: 'Your reply has been sent successfully',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
        });
    } catch (error) {
        console.error('Failed to send reply:', error);
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
</style>
