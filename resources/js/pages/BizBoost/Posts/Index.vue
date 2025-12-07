<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { PlusIcon, CalendarIcon, PhotoIcon, CheckCircleIcon, ClockIcon, XCircleIcon, RocketLaunchIcon, MagnifyingGlassIcon, PencilSquareIcon } from '@heroicons/vue/24/outline';

interface PostMedia {
    id: number;
    path: string;
    type: string;
}

interface Post {
    id: number;
    title: string | null;
    caption: string;
    status: 'draft' | 'scheduled' | 'published' | 'failed';
    platform_targets: string[] | null;
    scheduled_at: string | null;
    published_at: string | null;
    media: PostMedia[];
    campaign_id: number | null;
}

interface Props {
    posts: {
        data: Post[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    stats: {
        draft: number;
        scheduled: number;
        published: number;
        failed: number;
    };
    filters: {
        status?: string;
        platform?: string;
        search?: string;
    };
}

const props = defineProps<Props>();

const statusColors = {
    draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
    scheduled: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    published: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    failed: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
};

const statusIcons = {
    draft: ClockIcon,
    scheduled: CalendarIcon,
    published: CheckCircleIcon,
    failed: XCircleIcon,
};

const filterStatus = ref(props.filters.status || '');
const filterPlatform = ref(props.filters.platform || '');
const searchQuery = ref(props.filters.search || '');

const applyFilters = () => {
    router.get('/bizboost/posts', {
        status: filterStatus.value || undefined,
        platform: filterPlatform.value || undefined,
        search: searchQuery.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const formatDate = (date: string | null) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Posts - BizBoost" />

    <BizBoostLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Social Media Posts</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Manage your social media content</p>
                </div>
                <Link
                    :href="route('bizboost.posts.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700"
                >
                    <PlusIcon class="h-5 w-5" aria-hidden="true" />
                    Create Post
                </Link>
            </div>

            <!-- Filters -->
            <div class="rounded-xl bg-white dark:bg-gray-800 p-4 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                        <MagnifyingGlassIcon class="absolute left-3 top-[2.1rem] h-5 w-5 text-gray-400" aria-hidden="true" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search posts..."
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white pl-10 focus:border-violet-500 focus:ring-violet-500"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select 
                            v-model="filterStatus" 
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-violet-500 focus:ring-violet-500" 
                            @change="applyFilters"
                        >
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="published">Published</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Platform</label>
                        <select 
                            v-model="filterPlatform" 
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-violet-500 focus:ring-violet-500" 
                            @change="applyFilters"
                        >
                            <option value="">All Platforms</option>
                            <option value="facebook">Facebook</option>
                            <option value="instagram">Instagram</option>
                            <option value="whatsapp">WhatsApp</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button
                            @click="applyFilters"
                            class="w-full px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
                        >
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Posts List -->
            <div class="rounded-xl bg-white dark:bg-gray-800 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 overflow-hidden">
                <div v-if="posts.data.length === 0" class="text-center py-12">
                    <PhotoIcon class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" aria-hidden="true" />
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No posts</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new post.</p>
                    <div class="mt-6">
                        <Link
                            :href="route('bizboost.posts.create')"
                            class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700"
                        >
                            <PlusIcon class="h-5 w-5" aria-hidden="true" />
                            Create Post
                        </Link>
                    </div>
                </div>

                <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div
                        v-for="post in posts.data"
                        :key="post.id"
                        class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                    >
                        <div class="flex items-start gap-4">
                            <!-- Media thumbnail -->
                            <div v-if="post.media && post.media.length > 0" class="flex-shrink-0 w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden">
                                <img :src="`/storage/${post.media[0].path}`" :alt="post.title || 'Post media'" class="w-full h-full object-cover" />
                            </div>
                            <div v-else class="flex-shrink-0 w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                <PhotoIcon class="h-6 w-6 text-gray-400 dark:text-gray-500" aria-hidden="true" />
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 mb-2">
                                    <Link
                                        :href="route('bizboost.posts.show', post.id)"
                                        class="text-lg font-semibold text-gray-900 dark:text-white hover:text-violet-600 dark:hover:text-violet-400 truncate"
                                    >
                                        {{ post.title || 'Untitled Post' }}
                                    </Link>
                                    <span
                                        :class="[
                                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium flex-shrink-0',
                                            statusColors[post.status]
                                        ]"
                                    >
                                        <component
                                            :is="statusIcons[post.status]"
                                            class="h-4 w-4 mr-1"
                                            aria-hidden="true"
                                        />
                                        {{ post.status }}
                                    </span>
                                    <span v-if="post.campaign_id" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400 flex-shrink-0">
                                        <RocketLaunchIcon class="h-3 w-3 mr-1" aria-hidden="true" />
                                        Campaign
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">{{ post.caption }}</p>
                                <div class="flex items-center flex-wrap gap-4 text-sm text-gray-500 dark:text-gray-400">
                                    <span v-if="post.platform_targets && post.platform_targets.length > 0">
                                        Platforms: {{ post.platform_targets.join(', ') }}
                                    </span>
                                    <span v-if="post.media && post.media.length > 0">
                                        <PhotoIcon class="inline h-4 w-4" aria-hidden="true" />
                                        {{ post.media.length }} media
                                    </span>
                                    <span v-if="post.scheduled_at">
                                        Scheduled: {{ formatDate(post.scheduled_at) }}
                                    </span>
                                    <span v-else-if="post.published_at">
                                        Published: {{ formatDate(post.published_at) }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-4 flex-shrink-0">
                                <Link
                                    :href="route('bizboost.posts.edit', post.id)"
                                    class="inline-flex items-center gap-1 text-sm text-violet-600 hover:text-violet-700 dark:text-violet-400 dark:hover:text-violet-300"
                                >
                                    <PencilSquareIcon class="h-4 w-4" aria-hidden="true" />
                                    Edit
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="posts.last_page > 1" class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Showing {{ (posts.current_page - 1) * posts.per_page + 1 }} to
                            {{ Math.min(posts.current_page * posts.per_page, posts.total) }} of
                            {{ posts.total }} results
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-if="posts.current_page > 1"
                                :href="route('bizboost.posts.index', { ...filters, page: posts.current_page - 1 })"
                                class="px-3 py-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200"
                            >
                                Previous
                            </Link>
                            <Link
                                v-if="posts.current_page < posts.last_page"
                                :href="route('bizboost.posts.index', { ...filters, page: posts.current_page + 1 })"
                                class="px-3 py-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md text-sm hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200"
                            >
                                Next
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
