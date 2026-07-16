<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import StockFlowLayout from '@/layouts/StockFlowLayout.vue';
import { useStockflowRoute } from '@/composables/useStockflowRoute';
import {
    EnvelopeIcon,
    PaperAirplaneIcon,
    ArrowLeftIcon,
} from '@heroicons/vue/24/outline';

const { route } = useStockflowRoute();

const props = defineProps<{
    conversation: any[];
    otherUser: { id: number; name: string; email: string } | null;
}>();

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user);
const isSubdomain = computed(() => (page.props as any).routeName?.startsWith('stockflow.sub.'));

const baseUrl = computed(() => isSubdomain.value ? '' : '/stockflow');
const csrfToken = computed(() => (page.props as any).csrf_token ?? '');

const newMessage = ref('');
const submitting = ref(false);
const messages = ref<any[]>(props.conversation);

const sendMessage = async () => {
    if (!newMessage.value.trim() || !props.otherUser || submitting.value) return;
    submitting.value = true;
    try {
        const res = await fetch(`${baseUrl.value}/messages`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.value,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                recipient_id: props.otherUser.id,
                subject: messages.value.length === 0 ? 'New conversation' : 'Re: ' + (messages.value[0]?.subject || ''),
                body: newMessage.value.trim(),
            }),
        });
        if (res.ok) {
            newMessage.value = '';
            const response = await res.json();
            router.reload({ only: ['conversation'] });
        }
    } finally {
        submitting.value = false;
    }
};

const formatDate = (dateStr: string) => {
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
};

const messagesEnd = ref<HTMLElement | null>(null);

onMounted(() => {
    messagesEnd.value?.scrollIntoView({ behavior: 'smooth' });
});
</script>

<template>
    <StockFlowLayout title="Conversation">
        <div class="p-4 sm:p-6 lg:p-8 max-w-4xl mx-auto">
            <div class="flex items-center gap-4 mb-6">
                <Link
                    :href="`${baseUrl}/messages`"
                    class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors"
                >
                    <ArrowLeftIcon class="h-5 w-5" />
                </Link>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ otherUser?.name || 'Unknown User' }}</h2>
                    <p class="text-sm text-gray-500">{{ otherUser?.email }}</p>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 max-h-[60vh] overflow-y-auto space-y-4">
                    <div v-if="messages.length === 0" class="text-center py-12">
                        <EnvelopeIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" />
                        <p class="text-gray-500">No messages yet in this conversation</p>
                        <p class="text-sm text-gray-400 mt-1">Send a message to start the conversation</p>
                    </div>

                    <div
                        v-for="msg in messages"
                        :key="msg.id"
                        :class="[
                            'flex gap-3',
                            msg.sender_id === user?.id ? 'justify-end' : 'justify-start',
                        ]"
                    >
                        <div
                            :class="[
                                'max-w-[75%] rounded-2xl px-4 py-3',
                                msg.sender_id === user?.id
                                    ? 'bg-emerald-600 text-white rounded-br-md'
                                    : 'bg-gray-100 text-gray-900 rounded-bl-md',
                            ]"
                        >
                            <p class="text-sm font-medium mb-1" :class="msg.sender_id === user?.id ? 'text-emerald-100' : 'text-gray-500'">
                                {{ msg.sender_id === user?.id ? 'You' : (otherUser?.name || 'Unknown') }}
                            </p>
                            <p class="text-sm whitespace-pre-wrap">{{ msg.body }}</p>
                            <p class="text-xs mt-1" :class="msg.sender_id === user?.id ? 'text-emerald-200' : 'text-gray-400'">
                                {{ formatDate(msg.created_at) }}
                            </p>
                        </div>
                    </div>
                    <div ref="messagesEnd"></div>
                </div>

                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50">
                    <form @submit.prevent="sendMessage" class="flex gap-2">
                        <input
                            v-model="newMessage"
                            type="text"
                            placeholder="Type your message..."
                            class="flex-1 rounded-lg border-gray-200 text-sm focus:ring-emerald-500 focus:border-emerald-500"
                        />
                        <button
                            type="submit"
                            :disabled="!newMessage.trim() || submitting"
                            class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <PaperAirplaneIcon class="h-4 w-4" />
                            Send
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </StockFlowLayout>
</template>
