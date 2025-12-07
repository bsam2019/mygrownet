<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { ArrowLeftIcon, PencilIcon, TrashIcon, CalendarIcon, CheckCircleIcon, ClockIcon } from '@heroicons/vue/24/outline';

interface Media {
    id: number;
    path: string;
    type: string;
}

interface Post {
    id: number;
    title: string;
    caption: string;
    status: string;
    post_type: string;
    platform_targets: string[];
    scheduled_at: string | null;
    published_at: string | null;
    created_at: string;
    media: Media[];
    analytics: Record<string, number>;
}

interface Props {
    post: Post;
}

const props = defineProps<Props>();

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    scheduled: 'bg-blue-100 text-blue-800',
    published: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
};

const statusIcons: Record<string, typeof ClockIcon> = {
    draft: ClockIcon,
    scheduled: CalendarIcon,
    published: CheckCircleIcon,
    failed: ClockIcon,
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

const deletePost = () => {
    if (confirm('Are you sure you want to delete this post?')) {
        router.delete(route('bizboost.posts.destroy', props.post.id));
    }
};
</script>

<template>
    <Head :title="`${post.title} - BizBoost`" />

    <BizBoostLayout>
        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link :href="route('bizboost.posts.index')" class="inline-flex items-center text-gray-600 hover:text-gray-900">
                        <ArrowLeftIcon class="h-4 w-4 mr-1" aria-hidden="true" />
                        Back to Posts
                    </Link>
                </div>

                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <!-- Header -->
                    <div class="p-6 border-b">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-3">
                                    <h1 class="text-2xl font-bold text-gray-900">{{ post.title }}</h1>
                                    <span :class="['inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium', statusColors[post.status]]">
                                        <component :is="statusIcons[post.status]" class="h-3 w-3" aria-hidden="true" />
                                        {{ post.status }}
                                    </span>
                                </div>
                                <div class="mt-2 flex items-center gap-4 text-sm text-gray-500">
                                    <span v-if="post.platform_targets.length > 0">{{ post.platform_targets.join(', ') }}</span>
                                    <span>Created: {{ formatDate(post.created_at) }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <Link
                                    v-if="post.status === 'draft' || post.status === 'scheduled'"
                                    :href="route('bizboost.posts.edit', post.id)"
                                    class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center gap-1"
                                >
                                    <PencilIcon class="h-4 w-4" aria-hidden="true" />
                                    Edit
                                </Link>
                                <button
                                    @click="deletePost"
                                    class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg flex items-center gap-1"
                                >
                                    <TrashIcon class="h-4 w-4" aria-hidden="true" />
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Media -->
                    <div v-if="post.media.length > 0" class="bg-gray-100">
                        <div class="max-w-2xl mx-auto p-4">
                            <div v-if="post.media.length === 1" class="aspect-square">
                                <img :src="`/storage/${post.media[0].path}`" class="w-full h-full object-contain" />
                            </div>
                            <div v-else class="grid grid-cols-2 gap-2">
                                <div v-for="media in post.media" :key="media.id" class="aspect-square">
                                    <img :src="`/storage/${media.path}`" class="w-full h-full object-cover rounded" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Caption -->
                    <div class="p-6">
                        <h2 class="text-sm font-medium text-gray-700 mb-2">Caption</h2>
                        <div class="bg-gray-50 rounded-lg p-4 whitespace-pre-wrap text-gray-800">
                            {{ post.caption }}
                        </div>
                    </div>

                    <!-- Schedule Info -->
                    <div v-if="post.scheduled_at || post.published_at" class="px-6 pb-6">
                        <div class="bg-blue-50 rounded-lg p-4">
                            <div v-if="post.scheduled_at && post.status === 'scheduled'" class="flex items-center gap-2 text-blue-800">
                                <CalendarIcon class="h-5 w-5" aria-hidden="true" />
                                <span>Scheduled for: {{ formatDate(post.scheduled_at) }}</span>
                            </div>
                            <div v-if="post.published_at" class="flex items-center gap-2 text-green-800">
                                <CheckCircleIcon class="h-5 w-5" aria-hidden="true" />
                                <span>Published: {{ formatDate(post.published_at) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
