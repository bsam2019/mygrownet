<template>
    <Transition name="slide-up">
        <div v-if="show" class="fixed inset-0 z-50 bg-gradient-to-br from-gray-50 to-gray-100 flex flex-col">
            <!-- Modern Header with Glassmorphism -->
            <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 text-white shadow-2xl">
                <div class="px-4 py-4 backdrop-blur-sm bg-white/10">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <button
                                @click="$emit('close')"
                                class="p-2.5 hover:bg-white/20 rounded-xl transition-all duration-200 active:scale-95 backdrop-blur-sm"
                            >
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <div>
                                <h1 class="text-xl font-bold tracking-tight">Messages</h1>
                                <p v-if="unreadCount > 0" class="text-xs text-white/80 mt-0.5">
                                    {{ unreadCount }} unread
                                </p>
                            </div>
                        </div>
                        <button
                            @click="showComposeModal = true"
                            class="p-2.5 bg-white/20 hover:bg-white/30 rounded-xl transition-all duration-200 active:scale-95 backdrop-blur-sm shadow-lg"
                        >
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modern Tabs with Smooth Indicator -->
                <div class="flex border-t border-white/20 relative">
                    <button
                        @click="activeTab = 'inbox'"
                        :class="[
                            'flex-1 py-3.5 text-sm font-semibold transition-all duration-300 relative z-10',
                            activeTab === 'inbox' ? 'text-white' : 'text-white/60 hover:text-white/80'
                        ]"
                    >
                        <span class="flex items-center justify-center gap-2">
                            Inbox
                            <span v-if="unreadCount > 0" class="px-2 py-0.5 bg-red-500 text-white text-xs rounded-full font-bold shadow-lg">
                                {{ unreadCount }}
                            </span>
                        </span>
                    </button>
                    <button
                        @click="activeTab = 'sent'"
                        :class="[
                            'flex-1 py-3.5 text-sm font-semibold transition-all duration-300 relative z-10',
                            activeTab === 'sent' ? 'text-white' : 'text-white/60 hover:text-white/80'
                        ]"
                    >
                        Sent
                    </button>
                    <!-- Animated Indicator -->
                    <div 
                        class="absolute bottom-0 h-1 bg-white rounded-t-full transition-all duration-300 shadow-lg"
                        :class="activeTab === 'inbox' ? 'left-0 w-1/2' : 'left-1/2 w-1/2'"
                    ></div>
                </div>
            </div>

            <!-- Messages List with Modern Cards -->
            <div class="flex-1 overflow-y-auto px-4 py-4 space-y-3">
                <!-- Loading State -->
                <div v-if="loading" class="flex flex-col items-center justify-center py-20">
                    <div class="relative">
                        <div class="animate-spin rounded-full h-16 w-16 border-4 border-blue-200"></div>
                        <div class="animate-spin rounded-full h-16 w-16 border-4 border-t-blue-600 absolute top-0 left-0"></div>
                    </div>
                    <p class="text-gray-600 mt-6 font-medium">Loading messages...</p>
                </div>

                <!-- Empty State -->
                <div v-else-if="filteredMessages.length === 0" class="flex flex-col items-center justify-center py-20">
                    <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-12 h-12 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <p class="text-gray-900 font-semibold text-lg mb-2">No messages yet</p>
                    <p class="text-gray-500 text-sm text-center max-w-xs mb-4">
                        {{ activeTab === 'inbox' ? 'Your inbox is empty. Messages will appear here.' : 'You haven\'t sent any messages yet.' }}
                    </p>
                    <button
                        @click="loadMessages"
                        class="px-4 py-2 bg-blue-600 text-white rounded-xl font-semibold text-sm hover:bg-blue-700 active:scale-95 transition-all"
                    >
                        Refresh
                    </button>
                    <p class="text-xs text-gray-400 mt-4">
                        Check browser console (F12) for debug info
                    </p>
                </div>

                <!-- Message Cards -->
                <div
                    v-for="message in filteredMessages"
                    :key="message.id"
                    @click="openMessage(message)"
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 hover:shadow-md active:scale-[0.98] transition-all duration-200 cursor-pointer"
                    :class="{ 'ring-2 ring-blue-500 ring-opacity-50': !message.isRead && activeTab === 'inbox' }"
                >
                    <div class="flex items-start gap-3">
                        <!-- Avatar with Gradient -->
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-500 flex items-center justify-center shadow-lg">
                                <span class="text-white font-bold text-base">
                                    {{ getInitial(activeTab === 'inbox' ? message.senderName : message.recipientName) }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Message Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1.5">
                                <p :class="[
                                    'text-sm font-bold truncate',
                                    !message.isRead && activeTab === 'inbox' ? 'text-gray-900' : 'text-gray-600'
                                ]">
                                    {{ activeTab === 'inbox' ? message.senderName : message.recipientName }}
                                </p>
                                <span class="text-xs text-gray-500 ml-2 flex-shrink-0 font-medium">
                                    {{ formatTime(message.createdAt) }}
                                </span>
                            </div>
                            
                            <p :class="[
                                'text-sm mb-2 truncate',
                                !message.isRead && activeTab === 'inbox' ? 'font-semibold text-gray-900' : 'text-gray-700'
                            ]">
                                {{ message.subject }}
                            </p>
                            
                            <p class="text-sm text-gray-500 line-clamp-2 leading-relaxed">
                                {{ message.preview }}
                            </p>
                            
                            <!-- Unread Badge -->
                            <div v-if="!message.isRead && activeTab === 'inbox'" class="mt-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-blue-500 to-indigo-500 text-white shadow-sm">
                                    <span class="w-1.5 h-1.5 bg-white rounded-full mr-1.5 animate-pulse"></span>
                                    New
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Compose Modal -->
            <ComposeMessageModal
                :show="showComposeModal"
                @close="handleComposeClose"
            />

            <!-- Message View Modal -->
            <MessageViewModal
                :show="showMessageView"
                :message-id="selectedMessageId"
                @close="closeMessageView"
            />
        </div>
    </Transition>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import ComposeMessageModal from '@/Components/Mobile/ComposeMessageModal.vue';
import MessageViewModal from '@/Components/Mobile/MessageViewModal.vue';
import axios from 'axios';

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
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const activeTab = ref<'inbox' | 'sent'>('inbox');
const showComposeModal = ref(false);
const showMessageView = ref(false);
const selectedMessageId = ref<number | null>(null);
const messages = ref<Message[]>([]);
const loading = ref(false);

const filteredMessages = computed(() => {
    if (activeTab.value === 'inbox') {
        return messages.value.filter(m => !m.parentId);
    }
    return messages.value;
});

const unreadCount = computed(() => {
    return messages.value.filter(m => !m.isRead && !m.parentId).length;
});

function getInitial(name: string): string {
    return name?.charAt(0).toUpperCase() || '?';
}

function formatTime(dateString: string): string {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;
    
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function openMessage(message: Message) {
    selectedMessageId.value = message.id;
    showMessageView.value = true;
}

function closeMessageView() {
    showMessageView.value = false;
    selectedMessageId.value = null;
    loadMessages(); // Refresh messages
}

function handleComposeClose() {
    showComposeModal.value = false;
    loadMessages(); // Refresh after composing
}

async function loadMessages() {
    loading.value = true;
    try {
        // Use router.get to make an Inertia request and extract data
        const url = route('mygrownet.messages.index', { 
            tab: activeTab.value,
            mobile: 1 
        });
        
        console.log('🔄 Loading messages from:', url);
        
        // Make a simple fetch request instead of axios with Inertia headers
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        console.log('📦 Full response:', data);
        
        // Handle Inertia response format
        if (data.props && data.props.messages) {
            messages.value = data.props.messages;
            console.log('✅ Loaded', messages.value.length, 'messages from props.messages');
            console.log('📧 First message:', messages.value[0]);
        } else if (data.messages) {
            messages.value = data.messages;
            console.log('✅ Loaded', messages.value.length, 'messages from messages');
        } else if (Array.isArray(data)) {
            messages.value = data;
            console.log('✅ Loaded', messages.value.length, 'messages from array');
        } else {
            console.error('❌ Unexpected response structure');
            console.log('📋 Available keys:', Object.keys(data));
            console.log('📋 Full data:', JSON.stringify(data, null, 2));
            messages.value = [];
        }
    } catch (error: any) {
        console.error('❌ Failed to load messages:', error);
        console.error('📛 Error message:', error.message);
        messages.value = [];
    } finally {
        loading.value = false;
    }
}

watch(() => props.show, (newVal) => {
    if (newVal) {
        loadMessages();
    }
});

watch(activeTab, () => {
    loadMessages();
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
