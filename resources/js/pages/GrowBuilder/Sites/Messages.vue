<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    EnvelopeIcon,
    EnvelopeOpenIcon,
    MagnifyingGlassIcon,
    ArrowDownTrayIcon,
    TrashIcon,
    ArrowLeftIcon,
    CheckCircleIcon,
    ArchiveBoxIcon,
    ChatBubbleLeftEllipsisIcon,
} from '@heroicons/vue/24/outline';
import { ref, computed, watch } from 'vue';

interface Message {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    subject: string | null;
    message: string;
    status: 'unread' | 'read' | 'replied' | 'archived';
    reply: string | null;
    replied_at: string | null;
    created_at: string;
}

interface Site {
    id: number;
    name: string;
    subdomain: string;
}

interface PaginatedMessages {
    data: Message[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    site: Site;
    messages: PaginatedMessages;
    unreadCount: number;
    totalCount: number;
    filters: { status?: string; search?: string };
}>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || 'all');
const selectedMessage = ref<Message | null>(null);
const showReplyModal = ref(false);

const replyForm = useForm({
    reply: '',
});

// Debounced search
let searchTimeout: ReturnType<typeof setTimeout>;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

watch(statusFilter, () => {
    applyFilters();
});

const applyFilters = () => {
    router.get(
        route('growbuilder.sites.messages', props.site.id),
        { status: statusFilter.value, search: search.value },
        { preserveState: true, preserveScroll: true }
    );
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'unread':
            return 'bg-amber-100 text-amber-800';
        case 'read':
            return 'bg-gray-100 text-gray-800';
        case 'replied':
            return 'bg-green-100 text-green-800';
        case 'archived':
            return 'bg-gray-100 text-gray-500';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-ZM', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const openReplyModal = (message: Message) => {
    selectedMessage.value = message;
    replyForm.reply = message.reply || '';
    showReplyModal.value = true;
};

const submitReply = () => {
    if (!selectedMessage.value) return;
    
    replyForm.post(route('growbuilder.sites.messages.reply', [props.site.id, selectedMessage.value.id]), {
        preserveScroll: true,
        onSuccess: () => {
            showReplyModal.value = false;
            selectedMessage.value = null;
            replyForm.reset();
        },
    });
};

const updateStatus = (message: Message, status: string) => {
    router.put(
        route('growbuilder.sites.messages.status', [props.site.id, message.id]),
        { status },
        { preserveScroll: true }
    );
};

const deleteMessage = (message: Message) => {
    if (confirm('Are you sure you want to delete this message?')) {
        router.delete(route('growbuilder.sites.messages.delete', [props.site.id, message.id]));
    }
};

const exportMessages = () => {
    window.location.href = route('growbuilder.sites.messages.export', props.site.id);
};
</script>

<template>
    <AppLayout>
        <Head :title="`Messages - ${site.name}`" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <Link
                            :href="route('growbuilder.index')"
                            class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition"
                            aria-label="Back to dashboard"
                        >
                            <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                        </Link>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Messages</h1>
                            <p class="text-sm text-gray-500">{{ site.name }} • {{ totalCount }} total messages</p>
                        </div>
                    </div>
                    <button
                        @click="exportMessages"
                        class="inline-flex items-center gap-2 px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition"
                    >
                        <ArrowDownTrayIcon class="h-4 w-4" aria-hidden="true" />
                        Export CSV
                    </button>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-xl p-4 border border-gray-200">
                        <p class="text-2xl font-bold text-gray-900">{{ totalCount }}</p>
                        <p class="text-sm text-gray-500">Total</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 border border-gray-200">
                        <p class="text-2xl font-bold text-amber-600">{{ unreadCount }}</p>
                        <p class="text-sm text-gray-500">Unread</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 border border-gray-200">
                        <p class="text-2xl font-bold text-green-600">
                            {{ messages.data.filter(m => m.status === 'replied').length }}
                        </p>
                        <p class="text-sm text-gray-500">Replied</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 border border-gray-200">
                        <p class="text-2xl font-bold text-gray-400">
                            {{ messages.data.filter(m => m.status === 'archived').length }}
                        </p>
                        <p class="text-sm text-gray-500">Archived</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
                    <div class="flex items-center gap-4">
                        <div class="relative flex-1">
                            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search messages..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>
                        <select
                            v-model="statusFilter"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="all">All Status</option>
                            <option value="unread">Unread</option>
                            <option value="read">Read</option>
                            <option value="replied">Replied</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                </div>

                <!-- Messages List -->
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div v-if="messages.data.length === 0" class="p-12 text-center">
                        <EnvelopeIcon class="h-12 w-12 text-gray-300 mx-auto mb-4" aria-hidden="true" />
                        <h3 class="text-lg font-medium text-gray-900 mb-1">No messages yet</h3>
                        <p class="text-gray-500">Messages from your website contact form will appear here.</p>
                    </div>

                    <div v-else class="divide-y divide-gray-100">
                        <div
                            v-for="message in messages.data"
                            :key="message.id"
                            :class="[
                                'p-5 hover:bg-gray-50 transition',
                                message.status === 'unread' ? 'bg-amber-50/50' : ''
                            ]"
                        >
                            <div class="flex items-start gap-4">
                                <div :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold text-sm flex-shrink-0',
                                    message.status === 'unread' ? 'bg-amber-500' : 'bg-gray-400'
                                ]">
                                    {{ message.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2 mb-1">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-gray-900">{{ message.name }}</span>
                                            <span :class="['text-xs px-2 py-0.5 rounded-full capitalize', getStatusBadge(message.status)]">
                                                {{ message.status }}
                                            </span>
                                        </div>
                                        <span class="text-xs text-gray-400">{{ formatDate(message.created_at) }}</span>
                                    </div>
                                    <p class="text-sm text-gray-500 mb-1">{{ message.email }}</p>
                                    <p v-if="message.subject" class="text-sm font-medium text-gray-700 mb-1">{{ message.subject }}</p>
                                    <p class="text-sm text-gray-600 line-clamp-2">{{ message.message }}</p>
                                    
                                    <!-- Reply preview -->
                                    <div v-if="message.reply" class="mt-3 p-3 bg-green-50 rounded-lg border border-green-100">
                                        <p class="text-xs text-green-600 font-medium mb-1">Your reply:</p>
                                        <p class="text-sm text-green-800 line-clamp-2">{{ message.reply }}</p>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-2 mt-3">
                                        <button
                                            @click="openReplyModal(message)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                        >
                                            <ChatBubbleLeftEllipsisIcon class="h-4 w-4" aria-hidden="true" />
                                            {{ message.reply ? 'Edit Reply' : 'Reply' }}
                                        </button>
                                        <button
                                            v-if="message.status === 'unread'"
                                            @click="updateStatus(message, 'read')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition"
                                        >
                                            <EnvelopeOpenIcon class="h-4 w-4" aria-hidden="true" />
                                            Mark Read
                                        </button>
                                        <button
                                            v-if="message.status !== 'archived'"
                                            @click="updateStatus(message, 'archived')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition"
                                        >
                                            <ArchiveBoxIcon class="h-4 w-4" aria-hidden="true" />
                                            Archive
                                        </button>
                                        <button
                                            @click="deleteMessage(message)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-lg transition"
                                        >
                                            <TrashIcon class="h-4 w-4" aria-hidden="true" />
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="messages.last_page > 1" class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
                        <p class="text-sm text-gray-500">
                            Showing {{ (messages.current_page - 1) * messages.per_page + 1 }} to 
                            {{ Math.min(messages.current_page * messages.per_page, messages.total) }} of 
                            {{ messages.total }} messages
                        </p>
                        <div class="flex items-center gap-1">
                            <template v-for="link in messages.links" :key="link.label">
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    :class="[
                                        'px-3 py-1.5 text-sm rounded-lg transition',
                                        link.active 
                                            ? 'bg-blue-600 text-white' 
                                            : 'text-gray-600 hover:bg-gray-100'
                                    ]"
                                    v-html="link.label"
                                    preserve-scroll
                                />
                                <span
                                    v-else
                                    class="px-3 py-1.5 text-sm text-gray-400"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reply Modal -->
        <Teleport to="body">
            <div v-if="showReplyModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="fixed inset-0 bg-black/50" @click="showReplyModal = false"></div>
                    <div class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Reply to {{ selectedMessage?.name }}
                        </h3>
                        
                        <div v-if="selectedMessage" class="mb-4 p-3 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-500 mb-1">Original message:</p>
                            <p class="text-sm text-gray-700">{{ selectedMessage.message }}</p>
                        </div>

                        <form @submit.prevent="submitReply">
                            <textarea
                                v-model="replyForm.reply"
                                rows="5"
                                placeholder="Type your reply..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                required
                            ></textarea>
                            
                            <div class="flex items-center justify-end gap-3 mt-4">
                                <button
                                    type="button"
                                    @click="showReplyModal = false"
                                    class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="replyForm.processing"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50"
                                >
                                    {{ replyForm.processing ? 'Sending...' : 'Send Reply' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
