<template>
    <Head title="Message" />
    
    <div class="min-h-screen bg-gray-50">
        <!-- Mobile Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg sticky top-0 z-10">
            <div class="px-4 py-4">
                <div class="flex items-center gap-3">
                    <button
                        @click="router.visit(route('mygrownet.messages.index', { mobile: 1 }))"
                        class="p-2 hover:bg-white/10 rounded-lg transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <div class="flex-1">
                        <h1 class="text-lg font-bold truncate">{{ messages[0]?.subject }}</h1>
                        <p class="text-sm text-white/80 truncate">{{ messages[0]?.senderName }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages Thread -->
        <div class="p-4 space-y-4 pb-32">
            <div
                v-for="message in messages"
                :key="message.id"
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-4"
            >
                <div class="flex items-start gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center flex-shrink-0">
                        <span class="text-white font-semibold text-sm">
                            {{ message.senderName.charAt(0).toUpperCase() }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-900">{{ message.senderName }}</p>
                        <p class="text-xs text-gray-500">{{ formatDateTime(message.createdAt) }}</p>
                    </div>
                </div>
                <div class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">
                    {{ message.body }}
                </div>
            </div>
        </div>

        <!-- Reply Input (Fixed at bottom) -->
        <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg">
            <form @submit.prevent="sendReply" class="p-4">
                <div class="flex gap-2">
                    <textarea
                        v-model="form.body"
                        rows="2"
                        class="flex-1 rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 resize-none"
                        placeholder="Type your reply..."
                        required
                    ></textarea>
                    <button
                        type="submit"
                        :disabled="form.processing || !form.body.trim()"
                        class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl font-semibold disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center min-w-[60px]"
                    >
                        <svg v-if="form.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        <!-- Bottom Navigation -->
        <div class="pb-16">
            <BottomNavigation :active-tab="'messages'" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
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
    otherUserId: number;
}>();

const form = useForm({
    recipient_id: props.otherUserId,
    subject: props.messages[0]?.subject.startsWith('Re:') ? props.messages[0].subject : `Re: ${props.messages[0]?.subject}`,
    body: '',
    parent_id: props.messages[0]?.id,
});

function sendReply() {
    form.post(route('mygrownet.messages.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('body');
            
            Swal.fire({
                icon: 'success',
                title: 'Reply Sent!',
                text: 'Your reply has been sent successfully.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
            });
        },
    });
}

function formatDateTime(dateString: string): string {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}
</script>
