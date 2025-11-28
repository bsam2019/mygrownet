<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import InvestorLayout from '@/layouts/InvestorLayout.vue';
import {
    CheckCircleIcon,
    XCircleIcon,
    MinusCircleIcon,
    ClockIcon,
    DocumentTextIcon,
    UserGroupIcon,
    ChartBarIcon,
} from '@heroicons/vue/24/outline';

interface Resolution {
    id: number;
    title: string;
    description: string;
    resolution_type: string;
    required_majority: number;
    voting_start: string;
    voting_end: string;
    status: string;
    has_voted?: boolean;
    results?: VoteResults;
}

interface VoteResults {
    for: { count: number; power: number; percentage: number };
    against: { count: number; power: number; percentage: number };
    abstain: { count: number; power: number; percentage: number };
    total_votes: number;
    total_voting_power: number;
    passed: boolean;
}

interface VoteHistory {
    id: number;
    vote: string;
    voting_power: number;
    voted_at: string;
    resolution: Resolution;
}

interface Investor {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    investor: Investor;
    activeResolutions: Resolution[];
    upcomingResolutions: Resolution[];
    pastResolutions: Resolution[];
    voteHistory: VoteHistory[];
    votingPower: number;
    activePage?: string;
    unreadMessages?: number;
    unreadAnnouncements?: number;
}>();

const activeTab = ref<'active' | 'upcoming' | 'history'>('active');
const selectedResolution = ref<Resolution | null>(null);
const showVoteModal = ref(false);

const voteForm = useForm({
    resolution_id: 0,
    vote: '' as 'for' | 'against' | 'abstain',
});

const openVoteModal = (resolution: Resolution) => {
    selectedResolution.value = resolution;
    voteForm.resolution_id = resolution.id;
    voteForm.vote = '' as any;
    showVoteModal.value = true;
};

const submitVote = () => {
    voteForm.post(route('investor.voting.cast'), {
        preserveScroll: true,
        onSuccess: () => {
            showVoteModal.value = false;
            selectedResolution.value = null;
        },
    });
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getTimeRemaining = (endDate: string) => {
    const end = new Date(endDate);
    const now = new Date();
    const diff = end.getTime() - now.getTime();
    
    if (diff <= 0) return 'Voting closed';
    
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    
    if (days > 0) return `${days}d ${hours}h remaining`;
    return `${hours}h remaining`;
};

const getVoteIcon = (vote: string) => {
    switch (vote) {
        case 'for': return CheckCircleIcon;
        case 'against': return XCircleIcon;
        case 'abstain': return MinusCircleIcon;
        default: return MinusCircleIcon;
    }
};

const getVoteColor = (vote: string) => {
    switch (vote) {
        case 'for': return 'text-green-600';
        case 'against': return 'text-red-600';
        case 'abstain': return 'text-gray-600';
        default: return 'text-gray-600';
    }
};
</script>

<template>
    <InvestorLayout 
        :investor="investor" 
        page-title="Voting" 
        :active-page="activePage || 'voting'"
        :unread-messages="unreadMessages"
        :unread-announcements="unreadAnnouncements"
    >
        <Head title="Shareholder Voting" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Shareholder Voting</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Exercise your voting rights on company resolutions
                </p>
                <div class="mt-4 inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-sm">
                    <ChartBarIcon class="h-4 w-4 mr-2" aria-hidden="true" />
                    Your voting power: {{ votingPower }}%
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8">
                    <button
                        v-for="tab in [
                            { id: 'active', label: 'Active Votes', count: activeResolutions.length },
                            { id: 'upcoming', label: 'Upcoming', count: upcomingResolutions.length },
                            { id: 'history', label: 'Vote History', count: voteHistory.length },
                        ]"
                        :key="tab.id"
                        @click="activeTab = tab.id as any"
                        :class="[
                            activeTab === tab.id
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm',
                        ]"
                    >
                        {{ tab.label }}
                        <span
                            v-if="tab.count > 0"
                            :class="[
                                activeTab === tab.id ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-900',
                                'ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium',
                            ]"
                        >
                            {{ tab.count }}
                        </span>
                    </button>
                </nav>
            </div>

            <!-- Active Resolutions -->
            <div v-if="activeTab === 'active'" class="space-y-6">
                <div
                    v-for="resolution in activeResolutions"
                    :key="resolution.id"
                    class="bg-white rounded-lg shadow overflow-hidden"
                >
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 capitalize">
                                        {{ resolution.resolution_type.replace('_', ' ') }}
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        Requires {{ resolution.required_majority }}% majority
                                    </span>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">{{ resolution.title }}</h3>
                                <p class="mt-1 text-sm text-gray-500">{{ resolution.description }}</p>
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <ClockIcon class="h-3 w-3 mr-1" aria-hidden="true" />
                                    {{ getTimeRemaining(resolution.voting_end) }}
                                </span>
                            </div>
                        </div>

                        <!-- Results Bar -->
                        <div v-if="resolution.results" class="mt-4">
                            <div class="flex items-center justify-between text-sm mb-2">
                                <span class="text-green-600">For: {{ resolution.results.for.percentage }}%</span>
                                <span class="text-red-600">Against: {{ resolution.results.against.percentage }}%</span>
                                <span class="text-gray-500">Abstain: {{ resolution.results.abstain.percentage }}%</span>
                            </div>
                            <div class="w-full h-3 bg-gray-200 rounded-full overflow-hidden flex">
                                <div
                                    class="bg-green-500 h-full"
                                    :style="{ width: `${resolution.results.for.percentage}%` }"
                                ></div>
                                <div
                                    class="bg-red-500 h-full"
                                    :style="{ width: `${resolution.results.against.percentage}%` }"
                                ></div>
                                <div
                                    class="bg-gray-400 h-full"
                                    :style="{ width: `${resolution.results.abstain.percentage}%` }"
                                ></div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">
                                {{ resolution.results.total_votes }} votes cast ({{ resolution.results.total_voting_power.toFixed(2) }}% voting power)
                            </p>
                        </div>

                        <!-- Vote Button -->
                        <div class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-gray-500">
                                Voting ends: {{ formatDate(resolution.voting_end) }}
                            </div>
                            <button
                                v-if="!resolution.has_voted"
                                @click="openVoteModal(resolution)"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Cast Your Vote
                            </button>
                            <span
                                v-else
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800"
                            >
                                <CheckCircleIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                                Vote Submitted
                            </span>
                        </div>
                    </div>
                </div>

                <div v-if="activeResolutions.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
                    <DocumentTextIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No active resolutions</h3>
                    <p class="mt-1 text-sm text-gray-500">There are no resolutions currently open for voting.</p>
                </div>
            </div>

            <!-- Upcoming Resolutions -->
            <div v-if="activeTab === 'upcoming'" class="space-y-6">
                <div
                    v-for="resolution in upcomingResolutions"
                    :key="resolution.id"
                    class="bg-white rounded-lg shadow p-6"
                >
                    <div class="flex items-center gap-2 mb-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 capitalize">
                            {{ resolution.resolution_type.replace('_', ' ') }}
                        </span>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">{{ resolution.title }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ resolution.description }}</p>
                    <div class="mt-4 text-sm text-gray-500">
                        <ClockIcon class="inline h-4 w-4 mr-1" aria-hidden="true" />
                        Voting opens: {{ formatDate(resolution.voting_start) }}
                    </div>
                </div>

                <div v-if="upcomingResolutions.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
                    <ClockIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No upcoming resolutions</h3>
                    <p class="mt-1 text-sm text-gray-500">Check back later for new resolutions.</p>
                </div>
            </div>

            <!-- Vote History -->
            <div v-if="activeTab === 'history'" class="space-y-4">
                <div
                    v-for="vote in voteHistory"
                    :key="vote.id"
                    class="bg-white rounded-lg shadow p-4 flex items-center justify-between"
                >
                    <div class="flex items-center gap-4">
                        <component
                            :is="getVoteIcon(vote.vote)"
                            :class="['h-8 w-8', getVoteColor(vote.vote)]"
                            aria-hidden="true"
                        />
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">{{ vote.resolution?.title }}</h4>
                            <p class="text-xs text-gray-500">
                                Voted {{ vote.vote }} on {{ formatDate(vote.voted_at) }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900">{{ vote.voting_power }}%</p>
                        <p class="text-xs text-gray-500">Voting power</p>
                    </div>
                </div>

                <div v-if="voteHistory.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
                    <UserGroupIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No voting history</h3>
                    <p class="mt-1 text-sm text-gray-500">Your votes will appear here after you participate.</p>
                </div>
            </div>
        </div>

        <!-- Vote Modal -->
        <div v-if="showVoteModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showVoteModal = false"></div>
                
                <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Cast Your Vote</h3>
                    <p class="text-sm text-gray-500 mb-6">{{ selectedResolution?.title }}</p>

                    <div class="space-y-3">
                        <button
                            @click="voteForm.vote = 'for'"
                            :class="[
                                'w-full p-4 rounded-lg border-2 flex items-center gap-3 transition-colors',
                                voteForm.vote === 'for'
                                    ? 'border-green-500 bg-green-50'
                                    : 'border-gray-200 hover:border-green-300',
                            ]"
                        >
                            <CheckCircleIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
                            <span class="font-medium text-gray-900">Vote For</span>
                        </button>

                        <button
                            @click="voteForm.vote = 'against'"
                            :class="[
                                'w-full p-4 rounded-lg border-2 flex items-center gap-3 transition-colors',
                                voteForm.vote === 'against'
                                    ? 'border-red-500 bg-red-50'
                                    : 'border-gray-200 hover:border-red-300',
                            ]"
                        >
                            <XCircleIcon class="h-6 w-6 text-red-600" aria-hidden="true" />
                            <span class="font-medium text-gray-900">Vote Against</span>
                        </button>

                        <button
                            @click="voteForm.vote = 'abstain'"
                            :class="[
                                'w-full p-4 rounded-lg border-2 flex items-center gap-3 transition-colors',
                                voteForm.vote === 'abstain'
                                    ? 'border-gray-500 bg-gray-50'
                                    : 'border-gray-200 hover:border-gray-300',
                            ]"
                        >
                            <MinusCircleIcon class="h-6 w-6 text-gray-600" aria-hidden="true" />
                            <span class="font-medium text-gray-900">Abstain</span>
                        </button>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button
                            @click="showVoteModal = false"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            @click="submitVote"
                            :disabled="!voteForm.vote || voteForm.processing"
                            class="flex-1 px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ voteForm.processing ? 'Submitting...' : 'Submit Vote' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </InvestorLayout>
</template>
