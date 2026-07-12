<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { CheckBadgeIcon, HandThumbUpIcon, HandThumbDownIcon, MinusCircleIcon, ClockIcon } from '@heroicons/vue/24/outline';

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
    votes: { id: number; vote: string; equity_at_vote: number; comment: string | null; voted_at: string; shareholder: { user: { name: string } } }[];
}

interface Venture { id: number; title: string; slug: string; }
interface Shareholder { id: number; equity_percentage: number; }
interface MyVote { id: number; vote: string; comment: string | null; }

interface Props {
    venture: Venture;
    resolution: Resolution;
    myShareholder: Shareholder | null;
    myVote: MyVote | null;
}

const props = defineProps<Props>();
const selectedVote = ref<string | null>(null);
const comment = ref('');
const submitting = ref(false);

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-800', voting: 'bg-green-100 text-green-800',
        passed: 'bg-blue-100 text-blue-800', rejected: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const submitVote = () => {
    if (!selectedVote.value) return;
    submitting.value = true;
    router.post(route('mygrownet.ventures.resolutions.vote', props.resolution.id), {
        vote: selectedVote.value,
        comment: comment.value || null,
    }, { onFinish: () => { submitting.value = false; } });
};

const totalVotes = props.resolution.votes_for + props.resolution.votes_against + props.resolution.votes_abstain;
const forPercentage = totalVotes > 0 ? (props.resolution.votes_for / totalVotes) * 100 : 0;
const againstPercentage = totalVotes > 0 ? (props.resolution.votes_against / totalVotes) * 100 : 0;
</script>

<template>
    <Head :title="resolution.title" />
    <AppLayout>
        <div class="py-6">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('mygrownet.ventures.resolutions', venture.slug)" class="text-sm text-blue-600 hover:text-blue-800">&larr; Back to resolutions</Link>
                    <h1 class="text-2xl font-bold text-gray-900">{{ resolution.title }}</h1>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="flex items-center gap-2">
                        <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium capitalize text-blue-800">{{ resolution.type }}</span>
                        <span :class="getStatusColor(resolution.status)" class="rounded-full px-2.5 py-0.5 text-xs font-medium capitalize">{{ resolution.status }}</span>
                    </div>

                    <p class="mt-4 text-gray-700">{{ resolution.description }}</p>

                    <div class="mt-4 flex items-center gap-4 text-sm text-gray-500">
                        <span>Proposed by {{ resolution.creator.name }}</span>
                        <span>{{ new Date(resolution.created_at).toLocaleDateString() }}</span>
                        <span v-if="resolution.voting_ends_at" class="flex items-center gap-1">
                            <ClockIcon class="h-4 w-4" /> Ends {{ new Date(resolution.voting_ends_at).toLocaleDateString() }}
                        </span>
                    </div>

                    <div v-if="resolution.status === 'voting' || resolution.status === 'passed' || resolution.status === 'rejected'" class="mt-6 border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-900">Results</h3>
                        <div class="mt-4 grid grid-cols-3 gap-4">
                            <div class="rounded-lg bg-green-50 p-4 text-center">
                                <HandThumbUpIcon class="mx-auto h-6 w-6 text-green-600" />
                                <div class="mt-1 text-2xl font-bold text-green-700">{{ resolution.votes_for.toFixed(1) }}%</div>
                                <div class="text-sm text-green-600">For</div>
                            </div>
                            <div class="rounded-lg bg-red-50 p-4 text-center">
                                <HandThumbDownIcon class="mx-auto h-6 w-6 text-red-600" />
                                <div class="mt-1 text-2xl font-bold text-red-700">{{ resolution.votes_against.toFixed(1) }}%</div>
                                <div class="text-sm text-red-600">Against</div>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-4 text-center">
                                <MinusCircleIcon class="mx-auto h-6 w-6 text-gray-600" />
                                <div class="mt-1 text-2xl font-bold text-gray-700">{{ resolution.votes_abstain.toFixed(1) }}%</div>
                                <div class="text-sm text-gray-600">Abstain</div>
                            </div>
                        </div>
                        <div v-if="resolution.result_notes" class="mt-4 rounded-lg bg-gray-50 p-4 text-sm text-gray-700">
                            {{ resolution.result_notes }}
                        </div>
                        <div class="mt-4 text-sm text-gray-500">Total equity voted: {{ resolution.total_voted_equity.toFixed(2) }}%</div>
                    </div>

                    <div v-if="resolution.status === 'voting' && myShareholder && !myVote" class="mt-6 border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-900">Cast Your Vote</h3>
                        <p class="mt-1 text-sm text-gray-600">Your equity: {{ myShareholder.equity_percentage.toFixed(2) }}%</p>

                        <div class="mt-4 space-y-3">
                            <label class="flex cursor-pointer items-center rounded-lg border p-4 hover:bg-green-50" :class="{ 'border-green-500 bg-green-50': selectedVote === 'for' }">
                                <input type="radio" v-model="selectedVote" value="for" class="h-4 w-4 text-green-600" />
                                <div class="ml-3"><div class="font-medium text-gray-900">For</div><div class="text-sm text-gray-500">Approve this resolution</div></div>
                            </label>
                            <label class="flex cursor-pointer items-center rounded-lg border p-4 hover:bg-red-50" :class="{ 'border-red-500 bg-red-50': selectedVote === 'against' }">
                                <input type="radio" v-model="selectedVote" value="against" class="h-4 w-4 text-red-600" />
                                <div class="ml-3"><div class="font-medium text-gray-900">Against</div><div class="text-sm text-gray-500">Reject this resolution</div></div>
                            </label>
                            <label class="flex cursor-pointer items-center rounded-lg border p-4 hover:bg-gray-50" :class="{ 'border-gray-500 bg-gray-50': selectedVote === 'abstain' }">
                                <input type="radio" v-model="selectedVote" value="abstain" class="h-4 w-4 text-gray-600" />
                                <div class="ml-3"><div class="font-medium text-gray-900">Abstain</div><div class="text-sm text-gray-500">Decline to vote on this resolution</div></div>
                            </label>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700">Comment (optional)</label>
                            <textarea v-model="comment" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" />
                        </div>

                        <button @click="submitVote" :disabled="!selectedVote || submitting" class="mt-4 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-500 disabled:opacity-50">
                            {{ submitting ? 'Submitting...' : 'Cast Vote' }}
                        </button>
                    </div>

                    <div v-if="myVote" class="mt-6 border-t pt-6">
                        <div class="rounded-lg bg-blue-50 p-4">
                            <div class="flex items-center gap-2">
                                <CheckBadgeIcon class="h-5 w-5 text-blue-600" />
                                <span class="font-medium text-blue-900">You voted <span class="capitalize">{{ myVote.vote }}</span></span>
                            </div>
                            <p v-if="myVote.comment" class="mt-1 text-sm text-blue-700">{{ myVote.comment }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="resolution.votes.length" class="mt-6 rounded-lg bg-white p-6 shadow">
                    <h3 class="text-lg font-semibold text-gray-900">All Votes ({{ resolution.votes.length }})</h3>
                    <div class="mt-4 space-y-3">
                        <div v-for="vote in resolution.votes" :key="vote.id" class="flex items-center justify-between rounded-lg border p-3">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-medium text-gray-900">{{ vote.shareholder?.user?.name || 'Unknown' }}</span>
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="vote.vote === 'for' ? 'bg-green-100 text-green-800' : vote.vote === 'against' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'">{{ vote.vote }}</span>
                            </div>
                            <span class="text-sm text-gray-500">{{ vote.equity_at_vote.toFixed(2) }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
