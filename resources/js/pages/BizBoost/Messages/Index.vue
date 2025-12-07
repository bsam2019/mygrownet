<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    EnvelopeIcon,
    PaperAirplaneIcon,
    InboxIcon,
    PlusIcon,
    MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline';

interface Message {
    id: number;
    subject: string;
    body: string;
    sender: { id: number; name: string; email: string } | null;
    recipient: { id: number; name: string; email: string } | null;
    isRead: boolean;
    readAt?: string;
    createdAt: string;
    timeAgo: string;
    replyCount: number;
}

interface Props {
    messages: {
        data: Message[];
        links: any;
    };
    filter: 'inbox' | 'sent';
    unreadCount: number;
}

const props = defineProps<Props>();

const searchQuery = ref('');

const changeFilter = (filter: 'inbox' | 'sent') => {
    router.get('/bizboost/messages', { filter }, { preserveState: true });
};

const getInitials = (name: string) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const getAvatarColor = (name: string) => {
    const colors = [
        'bg-violet-500', 'bg-blue-500', 'bg-emerald-500', 
        'bg-amber-500', 'bg-rose-500', 'bg-indigo-500'
    ];
    const index = name.charCodeAt(0) % colors.length;
    return colors[index];
};
</script>

<template>
    <Head title="Messages - BizBoost" />

    <BizBoostLayout>
        <div class="max-w-5xl mx-auto px-4 py-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Messages</h1>
                    <p class="text-sm text-slate-500 mt-1">
                        {{ unreadCount }} unread message{{ unreadCount !== 1 ? 's' : '' }}
                    </p>
                </div>
                <Link
                    href="/bizboost/messages/create"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-violet-600 hover:bg-violet-700 rounded-lg transition-colors"
                >
                    <PlusIcon class="h-4 w-4" aria-hidden="true" />
                    New Message
                </Link>
            </div>

            <div class="flex gap-6">
                <!-- Sidebar -->
                <div class="w-48 shrink-0">
                    <nav class="space-y-1">
                        <button
                            @click="changeFilter('inbox')"
                            :class="[
                                'w-full flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors',
                                filter === 'inbox'
                                    ? 'bg-violet-100 text-violet-700'
                                    : 'text-slate-600 hover:bg-slate-100'
                            ]"
                        >
                            <InboxIcon class="h-5 w-5" aria-hidden="true" />
                            Inbox
                            <span v-if="unreadCount > 0" class="ml-auto text-xs bg-violet-600 text-white px-2 py-0.5 rounded-full">
                                {{ unreadCount }}
                            </span>
                        </button>
                        <button
                            @click="changeFilter('sent')"
                            :class="[
                                'w-full flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors',
                                filter === 'sent'
                                    ? 'bg-violet-100 text-violet-700'
                                    : 'text-slate-600 hover:bg-slate-100'
                            ]"
                        >
                            <PaperAirplaneIcon class="h-5 w-5" aria-hidden="true" />
                            Sent
                        </button>
                    </nav>
                </div>

                <!-- Messages List -->
                <div class="flex-1">
                    <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
                        <!-- Empty State -->
                        <div v-if="messages.data.length === 0" class="px-6 py-12 text-center">
                            <EnvelopeIcon class="h-12 w-12 text-slate-300 mx-auto mb-3" aria-hidden="true" />
                            <p class="text-slate-500">No messages yet</p>
                            <Link
                                href="/bizboost/messages/create"
                                class="inline-flex items-center gap-2 mt-4 text-sm text-violet-600 hover:text-violet-700 font-medium"
                            >
                                <PlusIcon class="h-4 w-4" aria-hidden="true" />
                                Send your first message
                            </Link>
                        </div>

                        <!-- Messages -->
                        <div v-else class="divide-y divide-slate-100">
                            <Link
                                v-for="message in messages.data"
                                :key="message.id"
                                :href="`/bizboost/messages/${message.id}`"
                                :class="[
                                    'flex items-start gap-4 p-4 transition-colors hover:bg-slate-50',
                                    !message.isRead && filter === 'inbox' ? 'bg-violet-50/50' : 'bg-white'
                                ]"
                            >
                                <!-- Avatar -->
                                <div :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-medium shrink-0',
                                    getAvatarColor(filter === 'inbox' ? (message.sender?.name || 'U') : (message.recipient?.name || 'U'))
                                ]">
                                    {{ getInitials(filter === 'inbox' ? (message.sender?.name || 'Unknown') : (message.recipient?.name || 'Unknown')) }}
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <p :class="[
                                            'text-sm truncate',
                                            !message.isRead && filter === 'inbox' ? 'font-semibold text-slate-900' : 'text-slate-700'
                                        ]">
                                            {{ filter === 'inbox' ? (message.sender?.name || 'Unknown') : (message.recipient?.name || 'Unknown') }}
                                        </p>
                                        <span class="text-xs text-slate-400 shrink-0">{{ message.timeAgo }}</span>
                                    </div>
                                    <p :class="[
                                        'text-sm truncate mt-0.5',
                                        !message.isRead && filter === 'inbox' ? 'font-medium text-slate-800' : 'text-slate-600'
                                    ]">
                                        {{ message.subject }}
                                    </p>
                                    <p class="text-sm text-slate-500 truncate mt-1">
                                        {{ message.body.substring(0, 100) }}{{ message.body.length > 100 ? '...' : '' }}
                                    </p>
                                    <div v-if="message.replyCount > 0" class="mt-2">
                                        <span class="text-xs text-slate-400">
                                            {{ message.replyCount }} repl{{ message.replyCount === 1 ? 'y' : 'ies' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Unread indicator -->
                                <div v-if="!message.isRead && filter === 'inbox'" class="w-2 h-2 bg-violet-500 rounded-full shrink-0 mt-2"></div>
                            </Link>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="messages.links && messages.links.length > 3" class="mt-6 flex justify-center">
                        <nav class="flex items-center gap-1">
                            <template v-for="link in messages.links" :key="link.label">
                                <button
                                    v-if="link.url"
                                    @click="router.visit(link.url)"
                                    :class="[
                                        'px-3 py-1.5 text-sm rounded-lg transition-colors',
                                        link.active
                                            ? 'bg-violet-600 text-white'
                                            : 'text-slate-600 hover:bg-slate-100'
                                    ]"
                                    v-html="link.label"
                                />
                            </template>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
