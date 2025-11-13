<template>
    <AppLayout title="Message Conversation">
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Back Button -->
                <div class="mb-6">
                    <Link
                        :href="route('mygrownet.messages.index')"
                        class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Messages
                    </Link>
                </div>

                <!-- Conversation -->
                <div class="bg-white rounded-lg shadow">
                    <div
                        v-for="message in messages"
                        :key="message.id"
                        class="p-6 border-b border-gray-200 last:border-b-0"
                    >
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-blue-600 font-medium text-sm">
                                        {{ message.senderName.charAt(0).toUpperCase() }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ message.senderName }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ formatDateTime(message.createdAt) }}
                                    </p>
                                </div>
                                <p class="mt-1 text-lg font-semibold text-gray-900">
                                    {{ message.subject }}
                                </p>
                                <div class="mt-2 text-sm text-gray-700 whitespace-pre-wrap">
                                    {{ message.body }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reply Form -->
                    <div class="p-6 bg-gray-50">
                        <form @submit.prevent="sendReply">
                            <div class="mb-4">
                                <label for="reply" class="block text-sm font-medium text-gray-700 mb-2">
                                    Reply
                                </label>
                                <textarea
                                    id="reply"
                                    v-model="form.body"
                                    rows="4"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Type your reply..."
                                    required
                                ></textarea>
                            </div>
                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                                >
                                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Send Reply
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

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
        },
    });
}

function formatDateTime(dateString: string): string {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}
</script>
