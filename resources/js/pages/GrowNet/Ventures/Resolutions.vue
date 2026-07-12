<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { CheckBadgeIcon, HandThumbUpIcon, ClockIcon } from '@heroicons/vue/24/outline';

interface Resolution {
    id: number;
    title: string;
    description: string;
    type: string;
    status: string;
    voting_starts_at: string | null;
    voting_ends_at: string | null;
    votes_for: number;
    votes_against: number;
    votes_abstain: number;
    total_voted_equity: number;
    pass_threshold_percentage: number;
    result_notes: string | null;
    created_at: string;
    creator: { name: string };
}

interface Venture { id: number; title: string; slug: string; status: string; }
interface Shareholder { id: number; total_investment: number; shares_owned: number; equity_percentage: number; }

interface Props {
    venture: Venture;
    resolutions: Resolution[];
    myShareholder: Shareholder | null;
    myVotes: Record<number, any>;
}

const props = defineProps<Props>();

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-800', voting: 'bg-green-100 text-green-800',
        passed: 'bg-blue-100 text-blue-800', rejected: 'bg-red-100 text-red-800', cancelled: 'bg-gray-100 text-gray-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getTypeColor = (type: string) => {
    const colors: Record<string, string> = { ordinary: 'bg-blue-100 text-blue-800', special: 'bg-purple-100 text-purple-800', board: 'bg-orange-100 text-orange-800' };
    return colors[type] || 'bg-gray-100 text-gray-800';
};

const resultLabel = (resolution: Resolution) => {
    if (resolution.status === 'passed') return 'Passed';
    if (resolution.status === 'rejected') return 'Rejected';
    if (resolution.status === 'voting') return 'Voting in progress';
    return resolution.status;
};
</script>

<template>
    <Head :title="`Resolutions - ${venture.title}`" />
    <AppLayout>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('mygrownet.ventures.show', venture.slug)" class="text-sm text-blue-600 hover:text-blue-800">&larr; Back to {{ venture.title }}</Link>
                    <h1 class="text-2xl font-bold text-gray-900">Shareholder Resolutions</h1>
                    <p class="mt-1 text-sm text-gray-600">Vote on company resolutions for {{ venture.title }}</p>
                </div>

                <div v-if="!resolutions.length" class="rounded-lg bg-white p-12 text-center shadow">
                    <CheckBadgeIcon class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No resolutions yet</h3>
                    <p class="mt-1 text-sm text-gray-500">This venture has no shareholder resolutions.</p>
                </div>

                <div v-else class="space-y-4">
                    <div v-for="resolution in resolutions" :key="resolution.id" class="rounded-lg bg-white p-6 shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span :class="getTypeColor(resolution.type)" class="rounded-full px-2.5 py-0.5 text-xs font-medium capitalize">{{ resolution.type }}</span>
                                    <span :class="getStatusColor(resolution.status)" class="rounded-full px-2.5 py-0.5 text-xs font-medium capitalize">{{ resultLabel(resolution) }}</span>
                                </div>
                                <Link :href="route('mygrownet.ventures.resolutions.show', [venture.slug, resolution.id])" class="mt-2 block text-lg font-semibold text-gray-900 hover:text-blue-600">{{ resolution.title }}</Link>
                                <p class="mt-1 text-sm text-gray-600 line-clamp-2">{{ resolution.description }}</p>
                                <div class="mt-3 flex items-center gap-4 text-xs text-gray-500">
                                    <span>By {{ resolution.creator.name }}</span>
                                    <span>{{ new Date(resolution.created_at).toLocaleDateString() }}</span>
                                    <span v-if="resolution.voting_ends_at">Ends {{ new Date(resolution.voting_ends_at).toLocaleDateString() }}</span>
                                </div>
                            </div>
                            <div v-if="resolution.status === 'voting'" class="ml-4 text-right">
                                <div class="text-sm text-gray-600">
                                    <span class="font-semibold text-green-600">{{ resolution.votes_for.toFixed(1) }}%</span> For
                                </div>
                                <div class="text-sm text-gray-600">
                                    <span class="font-semibold text-red-600">{{ resolution.votes_against.toFixed(1) }}%</span> Against
                                </div>
                                <div v-if="props.myVotes[resolution.id]" class="mt-1 text-xs text-gray-500">
                                    <HandThumbUpIcon class="inline h-4 w-4" /> You voted
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
