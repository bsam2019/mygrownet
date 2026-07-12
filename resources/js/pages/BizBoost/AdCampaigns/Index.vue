<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { PlusIcon, PlayIcon, PauseIcon, ChartBarIcon } from '@heroicons/vue/24/outline';

interface AdCampaign {
    id: number;
    name: string;
    objective: string;
    status: string;
    client_budget: number;
    meta_budget: number;
    platform_markup: number;
    meta_campaign_id: string | null;
    created_at: string;
}

interface Props {
    campaigns: {
        data: AdCampaign[];
        current_page: number;
        last_page: number;
        total: number;
    };
    stats: {
        total: number;
        active: number;
        paused: number;
        draft: number;
        completed: number;
    };
    userCurrency: string;
}

const props = defineProps<Props>();

const formatCurrency = (amount: number) => {
    const locale = props.userCurrency === 'ZMW' ? 'en-ZM' : 'en-US';
    return new Intl.NumberFormat(locale, { style: 'currency', currency: props.userCurrency }).format(amount);
};

const statusBadge = (status: string) => {
    const map: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-700',
        active: 'bg-green-100 text-green-700',
        paused: 'bg-yellow-100 text-yellow-700',
        completed: 'bg-blue-100 text-blue-700',
        failed: 'bg-red-100 text-red-700',
    };
    return map[status] || 'bg-gray-100 text-gray-700';
};

const toggleCampaign = (campaign: AdCampaign) => {
    if (campaign.status === 'active') {
        router.post(route('bizboost.ad-campaigns.pause', campaign.id));
    } else if (campaign.status === 'paused') {
        router.post(route('bizboost.ad-campaigns.resume', campaign.id));
    }
};
</script>

<template>
    <Head title="Ad Campaigns - BizBoost" />
    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Ad Campaigns</h1>
                    <Link :href="route('bizboost.ad-campaigns.create')" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <PlusIcon class="h-5 w-5" />
                        New Campaign
                    </Link>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
                        <p class="text-xs text-gray-500">Total</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <p class="text-2xl font-bold text-green-600">{{ stats.active }}</p>
                        <p class="text-xs text-gray-500">Active</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <p class="text-2xl font-bold text-yellow-600">{{ stats.paused }}</p>
                        <p class="text-xs text-gray-500">Paused</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <p class="text-2xl font-bold text-gray-600">{{ stats.draft }}</p>
                        <p class="text-xs text-gray-500">Draft</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <p class="text-2xl font-bold text-blue-600">{{ stats.completed }}</p>
                        <p class="text-xs text-gray-500">Completed</p>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Campaign</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Objective</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Budget</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Markup</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="campaign in campaigns.data" :key="campaign.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <Link :href="route('bizboost.ad-campaigns.show', campaign.id)" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                        {{ campaign.name }}
                                    </Link>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 capitalize">{{ campaign.objective }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ formatCurrency(campaign.client_budget) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ formatCurrency(campaign.platform_markup) }}</td>
                                <td class="px-6 py-4">
                                    <span :class="['px-2 py-1 text-xs rounded-full', statusBadge(campaign.status)]">{{ campaign.status }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link :href="route('bizboost.ad-campaigns.show', campaign.id)" class="p-1.5 text-gray-400 hover:text-blue-600 rounded">
                                            <ChartBarIcon class="h-5 w-5" />
                                        </Link>
                                        <button v-if="campaign.status === 'active'" @click="toggleCampaign(campaign)" class="p-1.5 text-gray-400 hover:text-yellow-600 rounded">
                                            <PauseIcon class="h-5 w-5" />
                                        </button>
                                        <button v-if="campaign.status === 'paused'" @click="toggleCampaign(campaign)" class="p-1.5 text-gray-400 hover:text-green-600 rounded">
                                            <PlayIcon class="h-5 w-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="campaigns.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">No campaigns yet. Create your first ad campaign!</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="campaigns.last_page > 1" class="mt-4 flex justify-center gap-2">
                    <Link v-for="page in campaigns.last_page" :key="page" :href="route('bizboost.ad-campaigns.index', { page })" :class="['px-3 py-1 rounded', page === campaigns.current_page ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']">
                        {{ page }}
                    </Link>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
