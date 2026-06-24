<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { PaperAirplaneIcon, CheckBadgeIcon, XCircleIcon } from '@heroicons/vue/24/outline';

interface OmnichannelLog {
    id: number;
    recipient_phone: string;
    channel_type: string;
    delivery_status: string;
    message_content: string | null;
    net_platform_profit: number;
    created_at: string;
}

interface Props {
    logs: {
        data: OmnichannelLog[];
        current_page: number;
        last_page: number;
        total: number;
    };
    stats: {
        total: number;
        delivered: number;
        failed: number;
        whatsapp: number;
        sms: number;
        total_profit: number;
    };
    channels: { value: string; label: string }[];
}

const props = defineProps<Props>();

const form = useForm({
    recipient: '',
    message: '',
    channel: '',
});

const send = () => {
    form.post(route('bizboost.omnichannel.send'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const statusIcon = (status: string) => {
    if (status === 'delivered') return CheckBadgeIcon;
    return XCircleIcon;
};

const statusColor = (status: string) => {
    if (status === 'delivered') return 'text-green-600';
    if (status === 'failed') return 'text-red-600';
    return 'text-yellow-600';
};
</script>

<template>
    <Head title="Omnichannel - BizBoost" />
    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">Omnichannel Messaging</h1>

                <div class="grid grid-cols-3 md:grid-cols-6 gap-4 mb-8">
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
                        <p class="text-xs text-gray-500">Total</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <p class="text-2xl font-bold text-green-600">{{ stats.delivered }}</p>
                        <p class="text-xs text-gray-500">Delivered</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <p class="text-2xl font-bold text-red-600">{{ stats.failed }}</p>
                        <p class="text-xs text-gray-500">Failed</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <p class="text-2xl font-bold text-blue-600">{{ stats.whatsapp }}</p>
                        <p class="text-xs text-gray-500">WhatsApp</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <p class="text-2xl font-bold text-purple-600">{{ stats.sms }}</p>
                        <p class="text-xs text-gray-500">SMS</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <p class="text-2xl font-bold text-green-600">{{ stats.total_profit.toFixed(2) }}</p>
                        <p class="text-xs text-gray-500">Profit</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 mb-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Send Message</h2>
                    <form @submit.prevent="send" class="space-y-4 max-w-lg">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Recipient Phone</label>
                            <input v-model="form.recipient" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="+260XXXXXXXXX" />
                            <p v-if="form.errors.recipient" class="mt-1 text-sm text-red-600">{{ form.errors.recipient }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Channel</label>
                            <select v-model="form.channel" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Auto (WhatsApp first)</option>
                                <option v-for="ch in channels" :key="ch.value" :value="ch.value">{{ ch.label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                            <textarea v-model="form.message" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Type your message..."></textarea>
                            <p v-if="form.errors.message" class="mt-1 text-sm text-red-600">{{ form.errors.message }}</p>
                        </div>
                        <div>
                            <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">
                                <PaperAirplaneIcon class="h-4 w-4" />
                                {{ form.processing ? 'Sending...' : 'Send' }}
                            </button>
                        </div>
                    </form>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b">
                        <h2 class="text-lg font-semibold text-gray-900">Delivery Log</h2>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recipient</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Channel</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Message</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Profit</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">{{ log.recipient_phone }}</td>
                                <td class="px-6 py-4 text-sm capitalize text-gray-600">{{ log.channel_type }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">{{ log.message_content || '—' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <component :is="statusIcon(log.delivery_status)" :class="['h-5 w-5 inline-block', statusColor(log.delivery_status)]" />
                                </td>
                                <td class="px-6 py-4 text-sm text-right text-gray-900">{{ Number(log.net_platform_profit).toFixed(4) }}</td>
                                <td class="px-6 py-4 text-sm text-right text-gray-500">{{ log.created_at }}</td>
                            </tr>
                            <tr v-if="logs.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">No messages sent yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="logs.last_page > 1" class="mt-4 flex justify-center gap-2">
                    <Link v-for="page in logs.last_page" :key="page" :href="route('bizboost.omnichannel.index', { page })" :class="['px-3 py-1 rounded', page === logs.current_page ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']">
                        {{ page }}
                    </Link>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
