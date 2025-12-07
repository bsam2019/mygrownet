<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { PlusIcon, RocketLaunchIcon, PauseIcon, PlayIcon, ChartBarIcon } from '@heroicons/vue/24/outline';

interface Campaign {
    id: number;
    name: string;
    description: string | null;
    objective: string;
    status: 'draft' | 'active' | 'paused' | 'completed';
    start_date: string;
    end_date: string;
    duration_days: number;
    target_platforms: string[];
    posts_count: number;
    created_at: string;
}

interface Props {
    campaigns: {
        data: Campaign[];
        current_page: number;
        last_page: number;
        total: number;
    };
    stats: {
        total: number;
        active: number;
        completed: number;
        draft: number;
    };
}

const props = defineProps<Props>();

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    active: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    paused: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
    completed: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
};

const objectiveLabels: Record<string, string> = {
    increase_sales: 'Increase Sales',
    promote_stock: 'Promote New Stock',
    announce_discount: 'Announce Discount',
    bring_back_customers: 'Re-engage Customers',
    grow_followers: 'Grow Followers',
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const startCampaign = (id: number) => {
    router.post(route('bizboost.campaigns.start', id));
};

const pauseCampaign = (id: number) => {
    router.post(route('bizboost.campaigns.pause', id));
};

const resumeCampaign = (id: number) => {
    router.post(route('bizboost.campaigns.resume', id));
};
</script>

<template>
    <Head title="Campaigns - BizBoost" />

    <BizBoostLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Marketing Campaigns</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Create and manage automated marketing campaigns</p>
                </div>
                <Link
                    :href="route('bizboost.campaigns.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    New Campaign
                </Link>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="rounded-xl bg-white dark:bg-gray-800 p-4 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Campaigns</div>
                </div>
                <div class="rounded-xl bg-white dark:bg-gray-800 p-4 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.active }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Active</div>
                </div>
                <div class="rounded-xl bg-white dark:bg-gray-800 p-4 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.completed }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Completed</div>
                </div>
                <div class="rounded-xl bg-white dark:bg-gray-800 p-4 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
                    <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ stats.draft }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Drafts</div>
                </div>
            </div>

            <!-- Campaigns List -->
            <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 overflow-hidden">
                <div v-if="campaigns.data.length === 0" class="text-center py-12">
                    <RocketLaunchIcon class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" aria-hidden="true" />
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No campaigns yet</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Create your first marketing campaign to get started.</p>
                    <div class="mt-6">
                        <Link
                            :href="route('bizboost.campaigns.create')"
                            class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700"
                        >
                            <PlusIcon class="h-5 w-5" aria-hidden="true" />
                            Create Campaign
                        </Link>
                    </div>
                </div>

                <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div
                        v-for="campaign in campaigns.data"
                        :key="campaign.id"
                        class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <Link
                                        :href="route('bizboost.campaigns.show', campaign.id)"
                                        class="text-lg font-semibold text-gray-900 dark:text-white hover:text-violet-600 dark:hover:text-violet-400"
                                    >
                                        {{ campaign.name }}
                                    </Link>
                                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', statusColors[campaign.status]]">
                                        {{ campaign.status }}
                                    </span>
                                </div>
                                <p v-if="campaign.description" class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ campaign.description }}</p>
                                <div class="flex items-center flex-wrap gap-4 text-sm text-gray-500 dark:text-gray-400">
                                    <span>{{ objectiveLabels[campaign.objective] || campaign.objective }}</span>
                                    <span>{{ campaign.posts_count }} posts</span>
                                    <span>{{ formatDate(campaign.start_date) }} - {{ formatDate(campaign.end_date) }}</span>
                                    <span>{{ campaign.target_platforms.join(', ') }}</span>
                                </div>
                            </div>
                            <div class="ml-4 flex items-center gap-2">
                                <button
                                    v-if="campaign.status === 'draft'"
                                    @click="startCampaign(campaign.id)"
                                    class="p-2 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/30 rounded-lg"
                                    aria-label="Start campaign"
                                >
                                    <PlayIcon class="h-5 w-5" aria-hidden="true" />
                                </button>
                                <button
                                    v-if="campaign.status === 'active'"
                                    @click="pauseCampaign(campaign.id)"
                                    class="p-2 text-yellow-600 hover:bg-yellow-50 dark:hover:bg-yellow-900/30 rounded-lg"
                                    aria-label="Pause campaign"
                                >
                                    <PauseIcon class="h-5 w-5" aria-hidden="true" />
                                </button>
                                <button
                                    v-if="campaign.status === 'paused'"
                                    @click="resumeCampaign(campaign.id)"
                                    class="p-2 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/30 rounded-lg"
                                    aria-label="Resume campaign"
                                >
                                    <PlayIcon class="h-5 w-5" aria-hidden="true" />
                                </button>
                                <Link
                                    :href="route('bizboost.campaigns.show', campaign.id)"
                                    class="p-2 text-violet-600 hover:bg-violet-50 dark:hover:bg-violet-900/30 rounded-lg"
                                    aria-label="View analytics"
                                >
                                    <ChartBarIcon class="h-5 w-5" aria-hidden="true" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
