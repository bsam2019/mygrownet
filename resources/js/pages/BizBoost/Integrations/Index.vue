<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { LinkIcon, CheckCircleIcon, XCircleIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

interface Integration {
    id: number;
    provider: string;
    status: 'active' | 'inactive' | 'error';
    account_name: string | null;
    connected_at: string | null;
    last_sync_at: string | null;
}

interface AvailableProvider {
    id: string;
    name: string;
    description: string;
    icon: string;
    color: string;
    supports: string[];
}

interface Props {
    integrations: Integration[];
    canAutoPost: boolean;
    availableProviders: AvailableProvider[];
}

const props = defineProps<Props>();

const statusColors: Record<string, string> = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-gray-100 text-gray-600',
    error: 'bg-red-100 text-red-800',
};

const statusIcons: Record<string, typeof CheckCircleIcon> = {
    active: CheckCircleIcon,
    inactive: XCircleIcon,
    error: XCircleIcon,
};

// Provider brand colors
const providerColors: Record<string, { bg: string; text: string; button: string }> = {
    facebook: { bg: 'bg-blue-100', text: 'text-blue-600', button: 'bg-[#1877F2] hover:bg-[#166FE5]' },
    instagram: { bg: 'bg-pink-100', text: 'text-pink-600', button: 'bg-gradient-to-r from-purple-500 via-pink-500 to-orange-500 hover:from-purple-600 hover:via-pink-600 hover:to-orange-600' },
    whatsapp: { bg: 'bg-green-100', text: 'text-green-600', button: 'bg-[#25D366] hover:bg-[#20BD5A]' },
    tiktok: { bg: 'bg-gray-900', text: 'text-white', button: 'bg-black hover:bg-gray-800' },
};

const getProviderColor = (provider: string) => {
    return providerColors[provider] || { bg: 'bg-blue-100', text: 'text-blue-600', button: 'bg-blue-600 hover:bg-blue-700' };
};

const connectProvider = (provider: string) => {
    router.get(route('bizboost.integrations.connect', { provider }));
};

const disconnectIntegration = (provider: string) => {
    if (confirm('Disconnect this integration?')) {
        router.delete(route('bizboost.integrations.disconnect', { provider }));
    }
};

const formatDate = (date: string | null) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};
</script>

<template>
    <Head title="Integrations - BizBoost" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <LinkIcon class="h-7 w-7 text-blue-600" aria-hidden="true" />
                        Integrations
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">Connect your social media accounts</p>
                </div>

                <!-- Connected Integrations -->
                <div v-if="integrations.length > 0" class="bg-white rounded-lg shadow mb-6">
                    <div class="p-4 border-b">
                        <h2 class="text-lg font-semibold text-gray-900">Connected Accounts</h2>
                    </div>
                    <div class="divide-y">
                        <div v-for="integration in integrations" :key="integration.id" class="p-4 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div :class="['w-10 h-10 rounded-full flex items-center justify-center', getProviderColor(integration.provider).bg]">
                                    <span :class="['text-lg font-bold', getProviderColor(integration.provider).text]">
                                        {{ integration.provider[0].toUpperCase() }}
                                    </span>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 capitalize">{{ integration.provider }}</div>
                                    <div class="text-sm text-gray-500">{{ integration.account_name || 'Connected' }}</div>
                                    <div v-if="integration.connected_at" class="text-xs text-gray-400">
                                        Connected {{ formatDate(integration.connected_at) }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <span :class="['inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium', statusColors[integration.status]]">
                                    <component :is="statusIcons[integration.status]" class="h-3 w-3" aria-hidden="true" />
                                    {{ integration.status }}
                                </span>
                                <button
                                    @click="disconnectIntegration(integration.provider)"
                                    class="text-sm text-red-600 hover:text-red-800"
                                >
                                    Disconnect
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Providers -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b">
                        <h2 class="text-lg font-semibold text-gray-900">Available Integrations</h2>
                    </div>
                    <div class="divide-y">
                        <div v-for="provider in availableProviders" :key="provider.id" class="p-4 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div :class="['w-10 h-10 rounded-full flex items-center justify-center', getProviderColor(provider.id).bg]">
                                    <span :class="['text-lg font-bold', getProviderColor(provider.id).text]">
                                        {{ provider.name[0] }}
                                    </span>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ provider.name }}</div>
                                    <div class="text-sm text-gray-500">{{ provider.description }}</div>
                                    <div class="flex flex-wrap gap-1 mt-1">
                                        <span 
                                            v-for="feature in provider.supports.slice(0, 3)" 
                                            :key="feature"
                                            class="text-xs bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded"
                                        >
                                            {{ feature }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <button
                                @click="connectProvider(provider.id)"
                                :class="['px-4 py-2 text-white rounded-lg transition-colors', getProviderColor(provider.id).button]"
                            >
                                Connect
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="!canAutoPost" class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm text-yellow-800">
                        Auto-posting requires a Professional or Business plan.
                        <Link :href="route('bizboost.subscription.index')" class="font-medium underline">Upgrade now</Link>
                    </p>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
