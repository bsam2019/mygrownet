<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import StockAuditLayout from '@/layouts/StockAuditLayout.vue';
import {
    EnvelopeIcon,
    PaperAirplaneIcon,
    PencilSquareIcon,
} from '@heroicons/vue/24/outline';

defineProps<{
    messages: any[];
}>();

const page = usePage();
const isSubdomain = computed(() => (page.props as any).routeName?.startsWith('stockflow.sub.'));

const baseUrl = computed(() => isSubdomain.value ? '' : '/stock-audit');

const formatDate = (dateStr: string) => {
    const d = new Date(dateStr);
    const now = new Date();
    const diff = now.getTime() - d.getTime();
    if (diff < 86400000) return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    if (diff < 604800000) return d.toLocaleDateString('en-US', { weekday: 'short' });
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};
</script>

<template>
    <StockAuditLayout title="Sent Messages">
        <div class="p-4 sm:p-6 lg:p-8 max-w-5xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Messages</h2>
                    <p class="text-sm text-gray-500 mt-1">Internal company conversations</p>
                </div>
                <Link
                    :href="`${baseUrl}/messages/compose`"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-colors shadow-sm"
                >
                    <PencilSquareIcon class="h-4 w-4" />
                    Compose
                </Link>
            </div>

            <div class="flex gap-2 mb-4">
                <Link
                    :href="`${baseUrl}/messages`"
                    class="px-4 py-2 text-sm font-medium rounded-lg text-gray-600 hover:bg-gray-100"
                >Inbox</Link>
                <Link
                    :href="`${baseUrl}/messages/sent`"
                    class="px-4 py-2 text-sm font-medium rounded-lg bg-emerald-600 text-white"
                >Sent</Link>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div v-if="messages.length === 0" class="text-center py-16">
                    <PaperAirplaneIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" />
                    <p class="text-gray-500 font-medium">No sent messages</p>
                    <p class="text-sm text-gray-400 mt-1">You haven't sent any messages yet</p>
                </div>

                <div v-else class="divide-y divide-gray-100">
                    <div v-for="msg in messages" :key="msg.id" class="flex items-start gap-4 px-5 py-4">
                        <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                            <span class="text-sm font-semibold text-gray-600">
                                {{ msg.recipient?.name?.charAt(0)?.toUpperCase() || '?' }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-gray-900">To: {{ msg.recipient?.name || 'Unknown' }}</span>
                                <span class="ml-auto text-xs text-gray-400">{{ formatDate(msg.created_at) }}</span>
                            </div>
                            <p class="text-sm font-medium text-gray-900 mt-0.5 truncate">{{ msg.subject }}</p>
                            <p class="text-xs text-gray-500 mt-0.5 truncate">{{ msg.body }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StockAuditLayout>
</template>
