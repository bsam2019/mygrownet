<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import GrowBizLayout from '@/Layouts/GrowBizLayout.vue';
import {
    EnvelopeIcon,
    PaperAirplaneIcon,
    PlusIcon,
    InboxIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';

interface Message {
    id: number;
    senderId: number;
    senderName: string;
    recipientId: number;
    recipientName: string;
    subject: string;
    preview: string;
    isRead: boolean;
    createdAt: string;
}

interface TeamMember {
    id: number;
    name: string;
    role: string;
}

const props = defineProps<{
    messages: Message[];
    pagination: {
        currentPage: number;
        lastPage: number;
        total: number;
    };
    tab: 'inbox' | 'sent';
    teamMembers: TeamMember[];
}>();

const showComposeModal = ref(false);
const composeForm = ref({
    recipient_id: '',
    subject: '',
    body: '',
});
const sending = ref(false);
const errors = ref<Record<string, string>>({});

const formatDate = (dateString: string): string => {
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
    
    return date.toLocaleDateString();
};

const getInitials = (name: string): string => {
    if (!name) return '?';
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
};

const sendMessage = () => {
    errors.value = {};
    
    if (!composeForm.value.recipient_id) {
        errors.value.recipient_id = 'Please select a recipient';
        return;
    }
    if (!composeForm.value.subject.trim()) {
        errors.value.subject = 'Subject is required';
        return;
    }
    if (!composeForm.value.body.trim()) {
        errors.value.body = 'Message is required';
        return;
    }
    
    sending.value = true;
    router.post(route('growbiz.messages.store'), composeForm.value, {
        onSuccess: () => {
            showComposeModal.value = false;
            composeForm.value = { recipient_id: '', subject: '', body: '' };
        },
        onError: (errs) => {
            errors.value = errs as Record<string, string>;
        },
        onFinish: () => {
            sending.value = false;
        },
    });
};

const closeModal = () => {
    showComposeModal.value = false;
    composeForm.value = { recipient_id: '', subject: '', body: '' };
    errors.value = {};
};
</script>

<template>
    <GrowBizLayout>
        <div class="p-4">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Messages</h1>
                    <p class="text-sm text-gray-500">Team communication</p>
                </div>
                <button
                    @click="teamMembers.length > 0 ? showComposeModal = true : null"
                    :class="[
                        'flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-xl transition-colors shadow-sm',
                        teamMembers.length > 0
                            ? 'text-white bg-emerald-600 hover:bg-emerald-700 cursor-pointer'
                            : 'text-gray-400 bg-gray-200 cursor-not-allowed'
                    ]"
                    :title="teamMembers.length === 0 ? 'Add team members first to send messages' : 'Compose new message'"
                >
                    <PlusIcon class="h-4 w-4" aria-hidden="true" />
                    New
                </button>
            </div>

            <!-- Tabs -->
            <div class="flex gap-2 mb-4">
                <Link
                    :href="route('growbiz.messages.index', { tab: 'inbox' })"
                    :class="[
                        'flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition-colors',
                        tab === 'inbox'
                            ? 'bg-emerald-100 text-emerald-700'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                    ]"
                >
                    <InboxIcon class="h-4 w-4" aria-hidden="true" />
                    Inbox
                </Link>
                <Link
                    :href="route('growbiz.messages.index', { tab: 'sent' })"
                    :class="[
                        'flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition-colors',
                        tab === 'sent'
                            ? 'bg-emerald-100 text-emerald-700'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                    ]"
                >
                    <PaperAirplaneIcon class="h-4 w-4" aria-hidden="true" />
                    Sent
                </Link>
            </div>

            <!-- Messages List -->
            <div class="space-y-2">
                <Link
                    v-for="message in messages"
                    :key="message.id"
                    :href="route('growbiz.messages.show', message.id)"
                    :class="[
                        'block p-4 rounded-xl transition-all',
                        message.isRead || tab === 'sent'
                            ? 'bg-white'
                            : 'bg-emerald-50/50 border border-emerald-100'
                    ]"
                >
                    <div class="flex items-start gap-3">
                        <!-- Avatar -->
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center">
                            <span class="text-sm font-semibold text-white">
                                {{ getInitials(tab === 'inbox' ? message.senderName : message.recipientName) }}
                            </span>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <p :class="[
                                    'text-sm truncate',
                                    !message.isRead && tab === 'inbox' ? 'font-semibold text-gray-900' : 'font-medium text-gray-700'
                                ]">
                                    {{ tab === 'inbox' ? message.senderName : `To: ${message.recipientName}` }}
                                </p>
                                <span class="text-xs text-gray-400 whitespace-nowrap">
                                    {{ formatDate(message.createdAt) }}
                                </span>
                            </div>
                            <p :class="[
                                'text-sm truncate mb-1',
                                !message.isRead && tab === 'inbox' ? 'font-medium text-gray-800' : 'text-gray-600'
                            ]">
                                {{ message.subject.replace('[GrowBiz] ', '') }}
                            </p>
                            <p class="text-sm text-gray-500 line-clamp-1">
                                {{ message.preview }}
                            </p>
                        </div>
                        
                        <!-- Unread indicator -->
                        <div v-if="!message.isRead && tab === 'inbox'" class="flex-shrink-0 w-2 h-2 bg-emerald-500 rounded-full mt-2"></div>
                    </div>
                </Link>

                <!-- Empty State -->
                <div v-if="messages.length === 0" class="text-center py-12">
                    <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <EnvelopeIcon class="h-8 w-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">
                        {{ tab === 'inbox' ? 'No messages yet' : 'No sent messages' }}
                    </h3>
                    <p class="text-sm text-gray-500 mb-4">
                        {{ tab === 'inbox' ? 'Messages from your team will appear here' : 'Messages you send will appear here' }}
                    </p>
                    <button
                        v-if="teamMembers.length > 0"
                        @click="showComposeModal = true"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-emerald-600 bg-emerald-50 rounded-xl hover:bg-emerald-100 transition-colors"
                    >
                        <PlusIcon class="h-4 w-4" aria-hidden="true" />
                        Send a message
                    </button>
                    <p v-else class="text-sm text-gray-400">
                        Add team members to start messaging
                    </p>
                </div>
            </div>
        </div>

        <!-- Compose Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div 
                    v-if="showComposeModal" 
                    class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm"
                    @click="closeModal"
                />
            </Transition>

            <Transition
                enter-active-class="transition-transform duration-300 ease-out"
                enter-from-class="translate-y-full"
                enter-to-class="translate-y-0"
                leave-active-class="transition-transform duration-200 ease-in"
                leave-from-class="translate-y-0"
                leave-to-class="translate-y-full"
            >
                <div 
                    v-if="showComposeModal"
                    class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md z-50 bg-white rounded-t-3xl shadow-2xl max-h-[90vh] overflow-hidden flex flex-col"
                >
                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                        <h2 class="text-lg font-bold text-gray-900">New Message</h2>
                        <button
                            @click="closeModal"
                            class="p-2 rounded-full hover:bg-gray-100 transition-colors"
                            aria-label="Close"
                        >
                            <XMarkIcon class="h-5 w-5 text-gray-500" aria-hidden="true" />
                        </button>
                    </div>

                    <!-- Form -->
                    <div class="flex-1 overflow-y-auto p-6 space-y-4">
                        <!-- Recipient -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                            <select
                                v-model="composeForm.recipient_id"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-colors"
                            >
                                <option value="">Select team member...</option>
                                <option v-for="member in teamMembers" :key="member.id" :value="member.id">
                                    {{ member.name }} ({{ member.role }})
                                </option>
                            </select>
                            <p v-if="errors.recipient_id" class="mt-1 text-sm text-red-500">{{ errors.recipient_id }}</p>
                        </div>

                        <!-- Subject -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                            <input
                                v-model="composeForm.subject"
                                type="text"
                                placeholder="What's this about?"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-colors"
                            />
                            <p v-if="errors.subject" class="mt-1 text-sm text-red-500">{{ errors.subject }}</p>
                        </div>

                        <!-- Message -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                            <textarea
                                v-model="composeForm.body"
                                rows="5"
                                placeholder="Type your message..."
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-colors resize-none"
                            ></textarea>
                            <p v-if="errors.body" class="mt-1 text-sm text-red-500">{{ errors.body }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="px-6 py-4 border-t border-gray-100 flex gap-3">
                        <button
                            @click="closeModal"
                            class="flex-1 py-3 rounded-xl bg-gray-100 text-gray-700 font-semibold hover:bg-gray-200 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="sendMessage"
                            :disabled="sending"
                            class="flex-1 py-3 rounded-xl bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        >
                            <PaperAirplaneIcon v-if="!sending" class="h-4 w-4" aria-hidden="true" />
                            <svg v-else class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ sending ? 'Sending...' : 'Send' }}
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </GrowBizLayout>
</template>
