<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { PlusIcon, ChatBubbleLeftRightIcon, UserGroupIcon, ArrowDownTrayIcon, ClockIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';

interface Broadcast {
    id: number;
    name: string;
    message: string;
    recipient_type: string;
    recipient_count: number;
    status: 'pending' | 'sending' | 'sent' | 'failed';
    sent_count: number | null;
    failed_count: number | null;
    created_at: string;
}

interface Template {
    id: string;
    name: string;
    category: string;
    content: string;
    variables: string[];
}

interface Props {
    broadcasts: {
        data: Broadcast[];
        current_page: number;
        last_page: number;
        total: number;
    };
    customerCount: number;
    templates: Template[];
}

const props = defineProps<Props>();

const statusColors: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    sending: 'bg-blue-100 text-blue-800',
    sent: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
};

const statusIcons: Record<string, typeof ClockIcon> = {
    pending: ClockIcon,
    sending: ClockIcon,
    sent: CheckCircleIcon,
    failed: ClockIcon,
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const exportContacts = () => {
    window.location.href = route('bizboost.whatsapp.export-customers');
};
</script>

<template>
    <Head title="WhatsApp Broadcasts - BizBoost" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                            <ChatBubbleLeftRightIcon class="h-7 w-7 text-green-600" aria-hidden="true" />
                            WhatsApp Broadcasts
                        </h1>
                        <p class="mt-1 text-sm text-gray-600">Send bulk messages to your customers</p>
                    </div>
                    <div class="flex gap-3">
                        <button
                            @click="exportContacts"
                            class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                        >
                            <ArrowDownTrayIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                            Export Contacts
                        </button>
                        <Link
                            :href="route('bizboost.whatsapp.broadcasts.create')"
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                        >
                            <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                            New Broadcast
                        </Link>
                    </div>
                </div>

                <!-- Stats -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <div class="flex items-center gap-4">
                        <UserGroupIcon class="h-10 w-10 text-green-600" aria-hidden="true" />
                        <div>
                            <div class="text-2xl font-bold text-gray-900">{{ customerCount }}</div>
                            <div class="text-sm text-gray-500">Customers with WhatsApp</div>
                        </div>
                    </div>
                </div>

                <!-- Broadcasts List -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div v-if="broadcasts.data.length === 0" class="text-center py-12">
                        <ChatBubbleLeftRightIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No broadcasts yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Create your first WhatsApp broadcast to reach your customers.</p>
                        <div class="mt-6">
                            <Link
                                :href="route('bizboost.whatsapp.broadcasts.create')"
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                            >
                                <PlusIcon class="h-5 w-5 mr-2" aria-hidden="true" />
                                Create Broadcast
                            </Link>
                        </div>
                    </div>

                    <div v-else class="divide-y divide-gray-200">
                        <div
                            v-for="broadcast in broadcasts.data"
                            :key="broadcast.id"
                            class="p-6 hover:bg-gray-50 transition-colors"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ broadcast.name }}</h3>
                                        <span :class="['inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium', statusColors[broadcast.status]]">
                                            <component :is="statusIcons[broadcast.status]" class="h-3 w-3" aria-hidden="true" />
                                            {{ broadcast.status }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2 line-clamp-2">{{ broadcast.message }}</p>
                                    <div class="flex items-center gap-4 text-sm text-gray-500">
                                        <span>{{ broadcast.recipient_count }} recipients</span>
                                        <span v-if="broadcast.sent_count !== null">{{ broadcast.sent_count }} sent</span>
                                        <span>{{ formatDate(broadcast.created_at) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
