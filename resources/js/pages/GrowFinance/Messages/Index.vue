<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import GrowFinanceLayout from '@/Layouts/GrowFinanceLayout.vue';
import {
    EnvelopeIcon,
    PaperAirplaneIcon,
    InboxIcon,
    PlusIcon,
    MagnifyingGlassIcon,
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
    tab: string;
}>();

const activeTab = ref(props.tab);
const searchQuery = ref('');
const showComposeModal = ref(false);

const form = useForm({
    subject: '',
    body: '',
});

const filteredMessages = computed(() => {
    if (!searchQuery.value) return props.messages;
    const query = searchQuery.value.toLowerCase();
    return props.messages.filter(m =>
        m.subject.toLowerCase().includes(query) ||
        m.body.toLowerCase().includes(query)
    );
});

const switchTab = (tab: string) => {
    activeTab.value = tab;
    router.get(route('growfinance.messages.index'), { tab }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (hours < 24) {
        return date.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
    }
    if (days < 7) {
        return date.toLocaleDateString('en-GB', { weekday: 'short' });
    }
    return date.toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
};

const viewMessage = (id: number) => {
    router.get(route('growfinance.messages.show', id));
};

const sendMessage = () => {
    form.post(route('growfinance.messages.store'), {
        onSuccess: () => {
            showComposeModal.value = false;
            form.reset();
        },
    });
};

// Check if message is from support/admin
const isFromSupport = (message: Message) => {
    return message.sender_name.toLowerCase().includes('support') || 
           message.sender_name.toLowerCase().includes('admin');
};
</script>

<template>
    <GrowFinanceLayout>
        <Head title="Messages - GrowFinance" />

        <div class="p-4 lg:p-6 max-w-4xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl lg:text-2xl font-bold text-gray-900">Messages</h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Contact GrowFinance support team
                    </p>
                </div>
                <button
                    @click="showComposeModal = true"
                    class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors"
                >
                    <PlusIcon class="h-4 w-4" aria-hidden="true" />
                    Contact Support
                </button>
            </div>

            <!-- Tabs -->
            <div class="flex items-center gap-1 mb-4 bg-gray-100 p-1 rounded-lg w-fit">
                <button
                    @click="switchTab('inbox')"
                    :class="[
                        'flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-md transition-colors',
                        activeTab === 'inbox'
                            ? 'bg-white text-gray-900 shadow-sm'
                            : 'text-gray-600 hover:text-gray-900'
                    ]"
                >
                    <InboxIcon class="h-4 w-4" aria-hidden="true" />
                    Inbox
                </button>
                <button
                    @click="switchTab('sent')"
                    :class="[
                        'flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-md transition-colors',
                        activeTab === 'sent'
                            ? 'bg-white text-gray-900 shadow-sm'
                            : 'text-gray-600 hover:text-gray-900'
                    ]"
                >
                    <PaperAirplaneIcon class="h-4 w-4" aria-hidden="true" />
                    Sent
                </button>
            </div>

            <!-- Search -->
            <div class="relative mb-4">
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" aria-hidden="true" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search messages..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                />
            </div>

            <!-- Message List -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div v-if="filteredMessages.length === 0" class="p-8 text-center">
                    <EnvelopeIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" aria-hidden="true" />
                    <p class="text-gray-500">No messages yet</p>
                    <button
                        @click="showComposeModal = true"
                        class="mt-3 text-sm text-emerald-600 hover:text-emerald-700 font-medium"
                    >
                        Send your first message to support
                    </button>
                </div>

                <div v-else class="divide-y divide-gray-100">
                    <div
                        v-for="message in filteredMessages"
                        :key="message.id"
                        :class="[
                            'flex items-start gap-3 p-4 transition-colors cursor-pointer',
                            message.is_read ? 'bg-white hover:bg-gray-50' : 'bg-blue-50/30 hover:bg-blue-50/50'
                        ]"
                        @click="viewMessage(message.id)"
                    >
                        <!-- Avatar -->
                        <div class="flex-shrink-0">
                            <div v-if="activeTab === 'inbox' && isFromSupport(message)" class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center">
                                <ShieldCheckIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                            </div>
                            <UserCircleIcon v-else class="h-10 w-10 text-gray-400" aria-hidden="true" />
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <p :class="[
                                        'text-sm truncate',
                                        message.is_read ? 'text-gray-700' : 'text-gray-900 font-semibold'
                                    ]">
                                        {{ activeTab === 'inbox' ? message.sender_name : 'To: GrowFinance Support' }}
                                    </p>
                                    <span v-if="activeTab === 'inbox' && isFromSupport(message)" class="px-1.5 py-0.5 text-xs bg-emerald-100 text-emerald-700 rounded font-medium">
                                        Support
                                    </span>
                                </div>
                                <span class="text-xs text-gray-400 whitespace-nowrap">
                                    {{ formatTime(message.created_at) }}
                                </span>
                            </div>
                            <p :class="[
                                'text-sm truncate',
                                message.is_read ? 'text-gray-600' : 'text-gray-900 font-medium'
                            ]">
                                {{ message.subject }}
                            </p>
                            <p class="text-sm text-gray-500 truncate mt-0.5">
                                {{ message.body }}
                            </p>
                        </div>

                        <!-- Unread indicator -->
                        <div
                            v-if="!message.is_read && activeTab === 'inbox'"
                            class="w-2 h-2 bg-blue-600 rounded-full flex-shrink-0 mt-2"
                        />
                    </div>
                </div>
            </div>

            <!-- Info Card -->
            <div class="mt-6 bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                <div class="flex gap-3">
                    <ShieldCheckIcon class="h-5 w-5 text-emerald-600 flex-shrink-0 mt-0.5" aria-hidden="true" />
                    <div>
                        <p class="text-sm font-medium text-emerald-800">Need help with GrowFinance?</p>
                        <p class="text-sm text-emerald-700 mt-1">
                            Send us a message and our support team will respond within 24 hours. For urgent issues, 
                            please create a <a :href="route('growfinance.support.create')" class="underline font-medium">support ticket</a>.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compose Modal - Simplified for Support Contact -->
        <Teleport to="body">
            <div v-if="showComposeModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="fixed inset-0 bg-black/50" @click="showComposeModal = false" />
                    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center">
                                <ShieldCheckIcon class="h-5 w-5 text-emerald-600" aria-hidden="true" />
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">Contact Support</h2>
                                <p class="text-sm text-gray-500">Send a message to GrowFinance support team</p>
                            </div>
                        </div>
                        
                        <form @submit.prevent="sendMessage" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                                <input
                                    v-model="form.subject"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="What do you need help with?"
                                />
                                <p v-if="form.errors.subject" class="text-sm text-red-600 mt-1">{{ form.errors.subject }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                                <textarea
                                    v-model="form.body"
                                    rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
                                    placeholder="Describe your question or issue..."
                                />
                                <p v-if="form.errors.body" class="text-sm text-red-600 mt-1">{{ form.errors.body }}</p>
                            </div>

                            <div class="flex justify-end gap-3 pt-2">
                                <button
                                    type="button"
                                    @click="showComposeModal = false"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 transition-colors"
                                >
                                    <PaperAirplaneIcon class="h-4 w-4" aria-hidden="true" />
                                    Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Teleport>
    </GrowFinanceLayout>
</template>
