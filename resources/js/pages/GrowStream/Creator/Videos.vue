<template>
    <AppLayout title="My Videos - GrowStream Creator">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">My Videos</h1>
                    <p class="mt-2 text-gray-600">Manage your uploaded content</p>
                </div>
                <Link
                    :href="route('growstream.creator.videos.create')"
                    class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                >
                    Upload Video
                </Link>
            </div>

            <div v-if="videos.data.length === 0" class="rounded-lg border-2 border-dashed border-gray-300 py-16 text-center">
                <p class="text-lg font-medium text-gray-900">No videos yet</p>
                <p class="mt-1 text-sm text-gray-600">Upload your first video to get started.</p>
                <Link
                    :href="route('growstream.creator.videos.create')"
                    class="mt-4 inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                >
                    Upload Video
                </Link>
            </div>

            <div v-else class="overflow-hidden rounded-lg bg-white shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Moderation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Views</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Uploaded</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="video in videos.data" :key="video.id">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <img
                                        v-if="video.thumbnail_url"
                                        :src="video.thumbnail_url"
                                        :alt="video.title"
                                        class="h-12 w-20 rounded object-cover"
                                    />
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-900">{{ video.title }}</p>
                                        <p class="text-xs text-gray-500">{{ video.content_type }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    :class="{
                                        'bg-green-100 text-green-800': video.upload_status === 'ready',
                                        'bg-blue-100 text-blue-800': video.upload_status === 'processing',
                                        'bg-gray-100 text-gray-800': video.upload_status === 'pending',
                                        'bg-red-100 text-red-800': video.upload_status === 'failed',
                                    }"
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                >
                                    {{ video.upload_status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    :class="{
                                        'bg-green-100 text-green-800': video.moderation_status === 'approved',
                                        'bg-amber-100 text-amber-800': video.moderation_status === 'pending_review',
                                        'bg-red-100 text-red-800': video.moderation_status === 'rejected',
                                    }"
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                >
                                    {{ video.moderation_status?.replace('_', ' ') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ video.view_count }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ formatDate(video.created_at) }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <Link
                                        :href="route('growstream.creator.videos.edit', video.id)"
                                        class="rounded text-sm font-medium text-blue-600 hover:text-blue-800"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="destroy(video)"
                                        class="rounded text-sm font-medium text-red-600 hover:text-red-800"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="videos.data.length > 0" class="flex items-center justify-between border-t border-gray-200 px-6 py-3">
                    <p class="text-sm text-gray-600">
                        Showing {{ videos.from }} - {{ videos.to }} of {{ videos.total }}
                    </p>
                    <div class="flex gap-2">
                        <button
                            :disabled="!videos.prev_page_url"
                            @click="page(videos.current_page - 1)"
                            class="rounded px-3 py-1 text-sm disabled:opacity-50"
                        >
                            Prev
                        </button>
                        <button
                            :disabled="!videos.next_page_url"
                            @click="page(videos.current_page + 1)"
                            class="rounded px-3 py-1 text-sm disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface Video {
    id: number;
    title: string;
    thumbnail_url: string | null;
    content_type: string;
    upload_status: string;
    moderation_status: string;
    view_count: number;
    created_at: string;
}

interface Paginated<T> {
    data: T[];
    total: number;
    from: number | null;
    to: number | null;
    current_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
}

interface Props {
    videos: Paginated<Video>;
    filters: Record<string, any>;
}

defineProps<Props>();

const formatDate = (dateString: string): string => new Date(dateString).toLocaleDateString();

const page = (pageNumber: number) => {
    router.get(route('growstream.creator.videos.index'), { page: pageNumber }, { preserveState: true });
};

const destroy = (video: Video) => {
    if (confirm(`Delete "${video.title}"? This cannot be undone.`)) {
        router.delete(route('growstream.creator.videos.destroy', video.id));
    }
};
</script>
