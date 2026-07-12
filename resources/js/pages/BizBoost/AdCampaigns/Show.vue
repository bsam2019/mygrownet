<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { ArrowLeftIcon, PlayIcon, PauseIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';

interface AdCampaign {
    id: number;
    name: string;
    objective: string;
    status: string;
    client_budget: number;
    meta_budget: number;
    platform_markup: number;
    meta_campaign_id: string | null;
    meta_adset_id: string | null;
    meta_ad_id: string | null;
    created_at: string;
    updated_at: string;
    wallet: {
        balance: number;
        available_balance: number;
    } | null;
}

interface AdInsight {
    impressions: number;
    clicks: number;
    spend: number;
    ctr: number;
    cpc: number;
    reach: number;
    frequency: number;
    date_start: string;
    date_stop: string;
}

interface Props {
    campaign: AdCampaign;
    insights: AdInsight | null;
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

const launch = () => {
    router.post(route('bizboost.ad-campaigns.launch', campaign.id));
};

const pause = () => {
    router.post(route('bizboost.ad-campaigns.pause', campaign.id));
};

const resume = () => {
    router.post(route('bizboost.ad-campaigns.resume', campaign.id));
};
</script>

<template>
    <Head :title="`${campaign.name} - BizBoost`" />
    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('bizboost.ad-campaigns.index')" class="inline-flex items-center text-gray-600 hover:text-gray-900">
                        <ArrowLeftIcon class="h-4 w-4 mr-1" /> Back to Campaigns
                    </Link>
                </div>

                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-3">
                                <h1 class="text-2xl font-bold text-gray-900">{{ campaign.name }}</h1>
                                <span :class="['px-2 py-1 text-xs rounded-full', statusBadge(campaign.status)]">{{ campaign.status }}</span>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Objective: <span class="capitalize font-medium">{{ campaign.objective }}</span></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button v-if="campaign.status === 'draft'" @click="launch" class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <PlayIcon class="h-4 w-4" /> Launch
                            </button>
                            <button v-if="campaign.status === 'active'" @click="pause" class="inline-flex items-center gap-1.5 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                                <PauseIcon class="h-4 w-4" /> Pause
                            </button>
                            <button v-if="campaign.status === 'paused'" @click="resume" class="inline-flex items-center gap-1.5 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <PlayIcon class="h-4 w-4" /> Resume
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-sm text-gray-500">Your Budget</p>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(campaign.client_budget) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-sm text-gray-500">Meta Ad Spend</p>
                        <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(campaign.meta_budget) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-sm text-gray-500">Platform Markup</p>
                        <p class="text-2xl font-bold text-green-600">{{ formatCurrency(campaign.platform_markup) }}</p>
                    </div>
                </div>

                <div v-if="insights" class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Performance Insights</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500">Impressions</p>
                            <p class="text-lg font-bold text-gray-900">{{ insights.impressions.toLocaleString() }}</p>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500">Clicks</p>
                            <p class="text-lg font-bold text-gray-900">{{ insights.clicks.toLocaleString() }}</p>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500">CTR</p>
                            <p class="text-lg font-bold text-gray-900">{{ (insights.ctr * 100).toFixed(2) }}%</p>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500">CPC</p>
                            <p class="text-lg font-bold text-gray-900">{{ formatCurrency(insights.cpc) }}</p>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500">Reach</p>
                            <p class="text-lg font-bold text-gray-900">{{ insights.reach.toLocaleString() }}</p>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500">Frequency</p>
                            <p class="text-lg font-bold text-gray-900">{{ insights.frequency.toFixed(2) }}</p>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500">Total Spend</p>
                            <p class="text-lg font-bold text-gray-900">{{ formatCurrency(insights.spend) }}</p>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500">Period</p>
                            <p class="text-sm font-bold text-gray-900">{{ insights.date_start }} — {{ insights.date_stop }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="campaign.meta_campaign_id" class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Meta IDs</h2>
                    <dl class="space-y-2 text-sm">
                        <div class="flex"><dt class="w-32 text-gray-500">Campaign ID:</dt><dd class="text-gray-900 font-mono">{{ campaign.meta_campaign_id }}</dd></div>
                        <div v-if="campaign.meta_adset_id" class="flex"><dt class="w-32 text-gray-500">Ad Set ID:</dt><dd class="text-gray-900 font-mono">{{ campaign.meta_adset_id }}</dd></div>
                        <div v-if="campaign.meta_ad_id" class="flex"><dt class="w-32 text-gray-500">Ad ID:</dt><dd class="text-gray-900 font-mono">{{ campaign.meta_ad_id }}</dd></div>
                    </dl>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
