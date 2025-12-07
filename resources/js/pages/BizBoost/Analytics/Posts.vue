<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import { ChartBarIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline';

interface Post {
    id: number;
    caption: string;
    status: string;
    published_at: string | null;
    analytics: { engagements?: number; reach?: number } | null;
    media: Array<{ url: string; type: string }>;
}

interface Props {
    posts: {
        data: Post[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}

defineProps<Props>();
</script>

<template>
    <Head title="Post Analytics - BizBoost" />
    <BizBoostLayout title="Post Analytics">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link href="/bizboost/analytics" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
                </Link>
                <h2 class="text-lg font-semibold text-gray-900">All Posts Performance</h2>
            </div>

            <!-- Posts Table -->
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Post</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Published</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Reach</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Engagements</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="post in posts.data" :key="post.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        v-if="post.media?.[0]"
                                        class="h-10 w-10 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0"
                                    >
                                        <img :src="post.media[0].url" class="h-full w-full object-cover" alt="" />
                                    </div>
                                    <p class="text-sm text-gray-900 truncate max-w-xs">{{ post.caption }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ post.published_at ? new Date(post.published_at).toLocaleDateString() : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-right">
                                {{ (post.analytics?.reach ?? 0).toLocaleString() }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-right">
                                {{ (post.analytics?.engagements ?? 0).toLocaleString() }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <Link
                                    :href="`/bizboost/analytics/posts/${post.id}`"
                                    class="text-violet-600 hover:text-violet-700 text-sm font-medium"
                                >
                                    View Details
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="!posts.data?.length">
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <ChartBarIcon class="h-12 w-12 mx-auto text-gray-300 mb-3" aria-hidden="true" />
                                <p>No published posts yet.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="posts.links?.length > 3" class="flex justify-center gap-1">
                <Link
                    v-for="link in posts.links"
                    :key="link.label"
                    :href="link.url ?? '#'"
                    :class="[
                        'px-3 py-1.5 text-sm rounded-lg',
                        link.active ? 'bg-violet-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
                        !link.url && 'opacity-50 cursor-not-allowed'
                    ]"
                    v-html="link.label"
                />
            </div>
        </div>
    </BizBoostLayout>
</template>
