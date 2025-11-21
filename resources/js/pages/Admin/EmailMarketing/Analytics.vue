<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface Campaign {
    id: number;
    name: string;
    type: string;
    status: string;
}

interface Stats {
    total_campaigns: number;
    active_campaigns: number;
    total_sent: number;
    total_opened: number;
    total_clicked: number;
    avg_open_rate: number;
    avg_click_rate: number;
}

interface Props {
    campaigns: Campaign[];
    stats: Stats;
}

const props = defineProps<Props>();

const formatNumber = (num: number) => {
    return new Intl.NumberFormat().format(num);
};

const formatPercentage = (num: number) => {
    return `${num.toFixed(1)}%`;
};
</script>

<template>
    <Head title="Email Analytics" />

    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Email Analytics</h1>
                        <p class="mt-1 text-sm text-gray-600">
                            Track performance of your email campaigns
                        </p>
                    </div>
                    <a
                        :href="route('admin.email-campaigns.index')"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Back to Campaigns
                    </a>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Campaigns -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Campaigns</p>
                                <p class="text-2xl font-bold text-gray-900">{{ formatNumber(stats.total_campaigns) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Active Campaigns -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Active Campaigns</p>
                                <p class="text-2xl font-bold text-gray-900">{{ formatNumber(stats.active_campaigns) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Total Sent -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                                <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Emails Sent</p>
                                <p class="text-2xl font-bold text-gray-900">{{ formatNumber(stats.total_sent) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Avg Open Rate -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Avg Open Rate</p>
                                <p class="text-2xl font-bold text-gray-900">{{ formatPercentage(stats.avg_open_rate) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Stats -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Engagement Metrics</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Total Opened</span>
                                <span class="text-sm font-semibold text-gray-900">{{ formatNumber(stats.total_opened) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Total Clicked</span>
                                <span class="text-sm font-semibold text-gray-900">{{ formatNumber(stats.total_clicked) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Avg Click Rate</span>
                                <span class="text-sm font-semibold text-gray-900">{{ formatPercentage(stats.avg_click_rate) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Campaign Overview</h3>
                        <div class="space-y-2">
                            <div
                                v-for="campaign in campaigns.slice(0, 5)"
                                :key="campaign.id"
                                class="flex justify-between items-center py-2 border-b border-gray-100 last:border-0"
                            >
                                <span class="text-sm text-gray-900">{{ campaign.name }}</span>
                                <span
                                    :class="{
                                        'bg-green-100 text-green-800': campaign.status === 'active',
                                        'bg-yellow-100 text-yellow-800': campaign.status === 'paused',
                                        'bg-gray-100 text-gray-800': campaign.status === 'draft',
                                    }"
                                    class="px-2 py-1 text-xs font-semibold rounded-full"
                                >
                                    {{ campaign.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="campaigns.length === 0" class="text-center py-12 bg-white rounded-lg shadow">
                    <svg
                        class="mx-auto h-12 w-12 text-gray-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                        />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No analytics data</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Analytics will appear once campaigns are active and sending emails.
                    </p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
