<template>
    <Head title="Messages" />
    
    <div class="min-h-screen bg-gray-50">
        <!-- Mobile Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg sticky top-0 z-10">
            <div class="px-4 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button
                            @click="router.visit(route('mygrownet.mobile-dashboard'))"
                            class="p-2 hover:bg-white/10 rounded-lg transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <h1 class="text-xl font-bold">Messages</h1>
                    </div>
                    <button
                        @click="showComposeModal = true"
                        class="p-2 bg-white/20 hover:bg-white/30 rounded-lg transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex border-t border-white/20">
                <button
                    @click="activeTab = 'inbox'"
                    :class="[
                        'flex-1 py-3 text-sm font-medium transition-colors relative',
                        activeTab === 'inbox' ? 'text-white' : 'text-white/60'
                    ]"
                >
                    Inbox
                    <span v-if="unreadCount > 0" class="ml-1 px-1.5 py-0.5 bg-red-500 text-white text-xs rounded-full">
                        {{ unreadCount }}
                    </span>
                    <div v-if="activeTab === 'inbox'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-white"></div>
                </button>
                <button
                    @click="activeTab = 'sent'"
                    :class="[
                        'flex-1 py-3 text-sm font-medium transition-colors relative',
                        activeTab === 'sent' ? 'text-white' : 'text-white/60'
                    ]"
                >
                    Sent
                    <div v-if="activeTab === 'sent'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-white"></div>
                </button>
            </div>
        </div>

        <!-- Messages List -->
        <div class="p-4 space-y-2 pb-20">
            <div v-if="filteredMessages.length === 0" class="text-center py-12">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <p class="text-gray-500 font-medium">No messages yet</p>
                <p class="text-gray-400 text-sm mt-1">Your messages will appear here</p>
            </div>

            <div
                v-for="message in filteredMessages"
                :key="message.id"
                @click="openMessage(message)"
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 active:scale-98 transition-transform"
            >
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                            <span class="text-white font-semibold text-sm">
                                {{ getInitial(activeTab === 'inbox' ? message.senderName : message.recipientName) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <p :class="[
                                'text-sm font-semibold truncate',
                                !message.isRead && activeTab === 'inbox' ? 'text-gray-900' : 'text-gray-600'
                            ]">
                                {{ activeTab === 'inbox' ? message.senderName : message.recipientName }}
                            </p>
                            <span class="text-xs text-gray-500 ml-2 flex-shrink-0">
                                {{ formatTime(message.createdAt) }}
                            </span>
                        </div>
                        <p :class="[
                            'text-sm mb-1',
                            !message.isRead && activeTab === 'inbox' ? 'font-semibold text-gray-900' : 'text-gray-700'
                        ]">
                            {{ message.subject }}
                        </p>
                        <p class="text-sm text-gray-500 line-clamp-2">
                            {{ message.preview }}
                        </p>
                        <div v-if="!message.isRead && activeTab === 'inbox'" class="mt-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
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
            @close="showComposeModal = false"
        />

        <!-- Bottom Navigation -->
        <BottomNavigation :active-tab="'messages'" />
    </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import ComposeMessageModal from '@/Components/Messaging/ComposeMessageModal.vue';
import BottomNavigation from '@/components/Mobile/BottomNavigation.vue';

// Explicitly set no layout for mobile view
defineOptions({
    layout: null
});

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
}>();

const activeTab = ref<'inbox' | 'sent'>('inbox');
const showComposeModal = ref(false);

const filteredMessages = computed(() => {
    return props.messages.filter(m => {
        if (activeTab.value === 'inbox') {
            return !m.parentId; // Only show root messages in inbox
        }
        return true; // Show all sent messages
    });
});

const unreadCount = computed(() => {
    return props.messages.filter(m => !m.isRead).length;
});

function getInitial(name: string): string {
    return name.charAt(0).toUpperCase();
}

function formatTime(dateString: string): string {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins}m`;
    if (diffHours < 24) return `${diffHours}h`;
    if (diffDays < 7) return `${diffDays}d`;
    
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function openMessage(message: Message) {
    router.visit(route('mygrownet.messages.show', { id: message.id, mobile: 1 }));
}
</script>
