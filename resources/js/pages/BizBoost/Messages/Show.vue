<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    ArrowLeftIcon,
    PaperAirplaneIcon,
    TrashIcon,
} from '@heroicons/vue/24/outline';

interface Reply {
    id: number;
    body: string;
    sender: { id: number; name: string } | null;
    createdAt: string;
    timeAgo: string;
}

interface Message {
    id: number;
    subject: string;
    body: string;
    sender: { id: number; name: string; email: string } | null;
    recipient: { id: number; name: string; email: string } | null;
    isRead: boolean;
    createdAt: string;
    timeAgo: string;
    replies: Reply[];
}

interface Props {
    message: Message;
}

const props = defineProps<Props>();

const replyBody = ref('');
const sending = ref(false);

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

const sendReply = async () => {
    if (!replyBody.value.trim() || sending.value) return;
    
    sending.value = true;
    try {
        const response = await fetch(`/bizboost/messages/${props.message.id}/reply`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ body: replyBody.value }),
        });
        
        if (response.ok) {
            replyBody.value = '';
            router.reload({ only: ['message'] });
        }
    } catch (error) {
        console.error('Failed to send reply:', error);
    } finally {
        sending.value = false;
    }
};

const deleteMessage = async () => {
    if (!confirm('Are you sure you want to delete this message?')) return;
    
    try {
        await fetch(`/bizboost/messages/${props.message.id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        router.visit('/bizboost/messages');
    } catch (error) {
        console.error('Failed to delete message:', error);
    }
};
</script>

<template>
    <Head :title="`${message.subject} - Messages - BizBoost`" />

    <BizBoostLayout>
        <div class="max-w-4xl mx-auto px-4 py-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <Link
                        href="/bizboost/messages"
                        class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors"
                    >
                        <ArrowLeftIcon class="h-5 w-5" aria-hidden="true" />
                    </Link>
                    <h1 class="text-xl font-bold text-slate-900">{{ message.subject }}</h1>
                </div>
                <button
                    @click="deleteMessage"
                    class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                    title="Delete message"
                >
                    <TrashIcon class="h-5 w-5" aria-hidden="true" />
                </button>
            </div>

            <!-- Message Thread -->
            <div class="bg-white rounded-xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
                <!-- Original Message -->
                <div class="p-6 border-b border-slate-100">
                    <div class="flex items-start gap-4">
                        <div :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-medium shrink-0',
                            getAvatarColor(message.sender?.name || 'U')
                        ]">
                            {{ getInitials(message.sender?.name || 'Unknown') }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-slate-900">{{ message.sender?.name || 'Unknown' }}</p>
                                    <p class="text-sm text-slate-500">{{ message.sender?.email }}</p>
                                </div>
                                <span class="text-sm text-slate-400">{{ message.timeAgo }}</span>
                            </div>
                            <div class="mt-4 text-slate-700 whitespace-pre-wrap">{{ message.body }}</div>
                        </div>
                    </div>
                </div>

                <!-- Replies -->
                <div v-if="message.replies.length > 0" class="divide-y divide-slate-100">
                    <div v-for="reply in message.replies" :key="reply.id" class="p-6 bg-slate-50/50">
                        <div class="flex items-start gap-4">
                            <div :class="[
                                'w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-medium shrink-0',
                                getAvatarColor(reply.sender?.name || 'U')
                            ]">
                                {{ getInitials(reply.sender?.name || 'Unknown') }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="font-medium text-slate-900">{{ reply.sender?.name || 'Unknown' }}</p>
                                    <span class="text-sm text-slate-400">{{ reply.timeAgo }}</span>
                                </div>
                                <div class="mt-2 text-slate-700 whitespace-pre-wrap">{{ reply.body }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reply Form -->
                <div class="p-6 bg-slate-50 border-t border-slate-100">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-full bg-violet-500 flex items-center justify-center text-white text-sm font-medium shrink-0">
                            You
                        </div>
                        <div class="flex-1">
                            <textarea
                                v-model="replyBody"
                                rows="3"
                                placeholder="Write your reply..."
                                class="w-full px-4 py-3 border border-slate-200 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500 resize-none"
                            ></textarea>
                            <div class="flex justify-end mt-3">
                                <button
                                    @click="sendReply"
                                    :disabled="!replyBody.trim() || sending"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-violet-600 hover:bg-violet-700 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <PaperAirplaneIcon class="h-4 w-4" aria-hidden="true" />
                                    {{ sending ? 'Sending...' : 'Send Reply' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
