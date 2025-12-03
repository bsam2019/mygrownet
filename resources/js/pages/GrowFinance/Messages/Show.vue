<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import {
    ArrowLeftIcon,
    PaperAirplaneIcon,
    UserCircleIcon,
    ShieldCheckIcon,
} from '@heroicons/vue/24/outline';

interface Message {
    id: number;
    sender_id: number;
    recipient_id: number;
    sender_name: string;
    recipient_name: string;
    subject: string;
    body: string;
    is_read: boolean;
    created_at: string;
}

const props = defineProps<{
    messages: Message[];
    otherUserId: number;
}>();

const page = usePage();
const currentUserId = computed(() => (page.props.auth as any)?.user?.id);

const form = useForm({
    recipient_id: props.otherUserId,
    subject: props.messages[0]?.subject ? `Re: ${props.messages[0].subject.replace(/^Re:\s*/i, '')}` : '',
    body: '',
});

const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-GB', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const goBack = () => {
    router.get(route('growfinance.messages.index'));
};

const sendReply = () => {
    form.post(route('growfinance.messages.store'), {
        onSuccess: () => {
            form.body = '';
        },
        preserveScroll: true,
    });
};

const isOwnMessage = (message: Message) => {
    return message.sender_id === currentUserId.value;
};

const isFromSupport = (message: Message) => {
    return message.sender_name.toLowerCase().includes('support') || 
           message.sender_name.toLowerCase().includes('admin');
};

// Get conversation title
const conversationTitle = computed(() => {
    const firstMessage = props.messages[0];
    if (!firstMessage) return 'Conversation';
    
    // If the other user is support/admin, show "Support Conversation"
    const otherName = firstMessage.sender_id === currentUserId.value 
        ? firstMessage.recipient_name 
        : firstMessage.sender_name;
    
    if (otherName.toLowerCase().includes('support') || otherName.toLowerCase().includes('admin')) {
        return 'Support Conversation';
    }
    return otherName;
});
</script>

<template>
    <GrowFinanceLayout>
        <Head title="Conversation - GrowFinance" />

        <div class="p-4 lg:p-6 max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-6">
                <button
                    @click="goBack"
                    class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
                    aria-label="Go back to messages"
                >
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </button>
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center">
                        <ShieldCheckIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">{{ conversationTitle }}</h1>
                        <p class="text-sm text-gray-500">{{ messages.length }} message{{ messages.length !== 1 ? 's' : '' }}</p>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-4">
                <div class="divide-y divide-gray-100">
                    <div
                        v-for="message in messages"
                        :key="message.id"
                        :class="[
                            'p-4',
                            isOwnMessage(message) ? 'bg-emerald-50/50' : 'bg-white'
                        ]"
                    >
                        <div class="flex items-start gap-3">
                            <!-- Avatar -->
                            <div v-if="!isOwnMessage(message) && isFromSupport(message)" class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                <ShieldCheckIcon class="h-4 w-4 text-emerald-600" aria-hidden="true" />
                            </div>
                            <UserCircleIcon v-else class="h-8 w-8 text-gray-400 flex-shrink-0" aria-hidden="true" />
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ isOwnMessage(message) ? 'You' : message.sender_name }}
                                        </span>
                                        <span v-if="!isOwnMessage(message) && isFromSupport(message)" class="px-1.5 py-0.5 text-xs bg-emerald-100 text-emerald-700 rounded font-medium">
                                            Support
                                        </span>
                                    </div>
                                    <span class="text-xs text-gray-400">
                                        {{ formatDateTime(message.created_at) }}
                                    </span>
                                </div>
                                <p class="text-sm font-medium text-gray-700 mb-2">{{ message.subject }}</p>
                                <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ message.body }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reply Form -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <form @submit.prevent="sendReply" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reply to Support</label>
                        <textarea
                            v-model="form.body"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="Type your reply..."
                        />
                        <p v-if="form.errors.body" class="text-sm text-red-600 mt-1">{{ form.errors.body }}</p>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="form.processing || !form.body.trim()"
                            class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 transition-colors"
                        >
                            <PaperAirplaneIcon class="h-4 w-4" aria-hidden="true" />
                            Send Reply
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </GrowFinanceLayout>
</template>
