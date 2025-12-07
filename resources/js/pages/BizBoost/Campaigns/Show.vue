<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { 
    ArrowLeftIcon, 
    PlayIcon, 
    PauseIcon, 
    PencilIcon, 
    PhotoIcon,
    PlusIcon 
} from '@heroicons/vue/24/outline';

interface Post {
    id: number;
    title: string;
    caption: string;
    status: string;
    scheduled_at: string | null;
    media: { path: string }[] | null;
}

interface Campaign {
    id: number;
    name: string;
    description: string | null;
    objective: string;
    status: string;
    start_date: string;
    end_date: string;
    duration_days: number;
    target_platforms: string[] | null;
    posts: Post[];
}

interface Props {
    campaign: Campaign;
    posts: Post[];
    analytics: {
        total_posts: number;
        published_posts: number;
        scheduled_posts: number;
        total_engagement: number;
        total_reach: number;
    };
}

const props = defineProps<Props>();

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    active: 'bg-green-100 text-green-800',
    paused: 'bg-yellow-100 text-yellow-800',
    completed: 'bg-blue-100 text-blue-800',
};

// Check if campaign can have posts added
const canAddPosts = ['draft', 'active', 'paused'].includes(props.campaign.status);
const canEditPosts = props.campaign.status === 'draft';

const startCampaign = () => router.post(route('bizboost.campaigns.start', props.campaign.id));
const pauseCampaign = () => router.post(route('bizboost.campaigns.pause', props.campaign.id));
const resumeCampaign = () => router.post(route('bizboost.campaigns.resume', props.campaign.id));

const formatDate = (date: string) => new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
const getPlatforms = () => props.campaign.target_platforms?.join(', ') || 'No platforms';
</script>

<template>
    <Head :title="`${campaign.name} - BizBoost`" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('bizboost.campaigns.index')" class="inline-flex items-center text-gray-600 hover:text-gray-900">
                        <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                        Back to Campaigns
                    </Link>
                </div>

                <!-- Header -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-3">
                                <h1 class="text-2xl font-bold text-gray-900">{{ campaign.name }}</h1>
                                <span :class="['px-3 py-1 rounded-full text-sm font-medium', statusColors[campaign.status]]">
                                    {{ campaign.status }}
                                </span>
                            </div>
                            <p v-if="campaign.description" class="mt-2 text-gray-600">{{ campaign.description }}</p>
                            <div class="mt-3 flex items-center gap-4 text-sm text-gray-500">
                                <span>{{ formatDate(campaign.start_date) }} - {{ formatDate(campaign.end_date) }}</span>
                                <span>{{ campaign.duration_days }} days</span>
                                <span>{{ getPlatforms() }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button v-if="campaign.status === 'draft'" @click="startCampaign" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                                <PlayIcon class="h-5 w-5" aria-hidden="true" />
                                Start
                            </button>
                            <button v-if="campaign.status === 'active'" @click="pauseCampaign" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 flex items-center gap-2">
                                <PauseIcon class="h-5 w-5" aria-hidden="true" />
                                Pause
                            </button>
                            <button v-if="campaign.status === 'paused'" @click="resumeCampaign" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                                <PlayIcon class="h-5 w-5" aria-hidden="true" />
                                Resume
                            </button>
                            <Link v-if="campaign.status === 'draft'" :href="route('bizboost.campaigns.edit', campaign.id)" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center gap-2">
                                <PencilIcon class="h-5 w-5" aria-hidden="true" />
                                Edit
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Analytics -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="text-2xl font-bold text-gray-900">{{ analytics.total_posts }}</div>
                        <div class="text-sm text-gray-500">Total Posts</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="text-2xl font-bold text-green-600">{{ analytics.published_posts }}</div>
                        <div class="text-sm text-gray-500">Published</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="text-2xl font-bold text-blue-600">{{ analytics.scheduled_posts }}</div>
                        <div class="text-sm text-gray-500">Scheduled</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="text-2xl font-bold text-purple-600">{{ analytics.total_engagement }}</div>
                        <div class="text-sm text-gray-500">Engagement</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="text-2xl font-bold text-indigo-600">{{ analytics.total_reach }}</div>
                        <div class="text-sm text-gray-500">Reach</div>
                    </div>
                </div>

                <!-- Posts -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Campaign Posts</h2>
                        <Link
                            v-if="canAddPosts"
                            :href="route('bizboost.posts.create', { campaign_id: campaign.id })"
                            class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 inline-flex items-center gap-1"
                        >
                            <PlusIcon class="h-4 w-4" aria-hidden="true" />
                            Add Post
                        </Link>
                    </div>
                    <div v-if="!posts || posts.length === 0" class="p-8 text-center text-gray-500">
                        <PhotoIcon class="h-12 w-12 mx-auto text-gray-300 mb-3" aria-hidden="true" />
                        <p>No posts in this campaign yet.</p>
                        <Link
                            v-if="canAddPosts"
                            :href="route('bizboost.posts.create', { campaign_id: campaign.id })"
                            class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                        >
                            <PlusIcon class="h-4 w-4" aria-hidden="true" />
                            Create First Post
                        </Link>
                    </div>
                    <div v-else class="divide-y">
                        <div v-for="post in posts" :key="post.id" class="p-4 hover:bg-gray-50">
                            <div class="flex items-center gap-4">
                                <div v-if="post.media?.length > 0" class="w-16 h-16 bg-gray-100 rounded overflow-hidden flex-shrink-0">
                                    <img :src="`/storage/${post.media[0].path}`" class="w-full h-full object-cover" />
                                </div>
                                <div v-else class="w-16 h-16 bg-gray-100 rounded flex items-center justify-center flex-shrink-0">
                                    <PhotoIcon class="h-8 w-8 text-gray-400" aria-hidden="true" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-gray-900">{{ post.title || 'Untitled Post' }}</div>
                                    <div class="text-sm text-gray-500 line-clamp-1">{{ post.caption }}</div>
                                    <div v-if="post.scheduled_at" class="text-xs text-gray-400 mt-1">
                                        Scheduled: {{ new Date(post.scheduled_at).toLocaleString() }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span :class="[
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        post.status === 'draft' ? 'bg-gray-100 text-gray-700' :
                                        post.status === 'scheduled' ? 'bg-blue-100 text-blue-700' :
                                        post.status === 'published' ? 'bg-green-100 text-green-700' :
                                        post.status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700'
                                    ]">
                                        {{ post.status }}
                                    </span>
                                    <Link 
                                        v-if="post.status === 'draft' && canEditPosts"
                                        :href="route('bizboost.posts.edit', post.id)" 
                                        class="text-blue-600 hover:text-blue-800 text-sm"
                                    >
                                        Edit
                                    </Link>
                                    <Link 
                                        :href="route('bizboost.posts.show', post.id)" 
                                        class="text-gray-600 hover:text-gray-800 text-sm"
                                    >
                                        View
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
