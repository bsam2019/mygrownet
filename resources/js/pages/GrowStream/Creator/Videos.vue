<template>
    <CreatorStudioLayout title="My Videos - GrowStream Creator">
        <div>
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-[var(--gs-text)]">My Videos</h1>
                    <p class="mt-2 text-[var(--gs-muted)]">Manage your uploaded content</p>
                </div>
                <Link
                    :href="route('growstream.creator.videos.create')"
                    class="gs-btn gs-btn-accent"
                >
                    Upload Video
                </Link>
            </div>

            <!-- Empty state -->
            <div
                v-if="videos.data.length === 0"
                class="gs-card flex flex-col items-center border-2 border-dashed border-[var(--gs-border)] py-16 text-center"
            >
                <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[var(--gs-primary-soft)]">
                    <svg class="h-8 w-8 text-[var(--gs-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>
                <p class="text-lg font-medium text-[var(--gs-text)]">No videos yet</p>
                <p class="mt-1 text-sm text-[var(--gs-muted)]">Upload your first video to get started.</p>
                <Link
                    :href="route('growstream.creator.videos.create')"
                    class="gs-btn gs-btn-primary mt-6"
                >
                    Upload Video
                </Link>
            </div>

            <!-- List -->
            <div v-else class="gs-surface overflow-x-auto">
                <table class="min-w-full divide-y divide-[var(--gs-border)]">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[var(--gs-muted)]">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[var(--gs-muted)]">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[var(--gs-muted)]">Moderation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[var(--gs-muted)]">Views</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-[var(--gs-muted)]">Uploaded</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-[var(--gs-muted)]">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--gs-border)]">
                        <tr v-for="video in videos.data" :key="video.id" class="hover:bg-[var(--gs-card-hover)]">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <img
                                        v-if="video.thumbnail_url"
                                        :src="video.thumbnail_url"
                                        :alt="video.title"
                                        class="h-12 w-20 rounded object-cover"
                                    />
                                    <div v-else class="flex h-12 w-20 items-center justify-center rounded bg-[var(--gs-bg-elevated)]">
                                        <svg class="h-6 w-6 text-[var(--gs-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-[var(--gs-text)]">{{ video.title }}</p>
                                        <p class="text-xs text-[var(--gs-muted)]">{{ video.content_type }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="uploadBadge(video.upload_status)" class="gs-chip">
                                    {{ video.upload_status?.replace('_', ' ') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span :class="moderationBadge(video.moderation_status)" class="gs-chip">
                                    {{ video.moderation_status?.replace('_', ' ') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-[var(--gs-muted)]">{{ video.view_count }}</td>
                            <td class="px-6 py-4 text-sm text-[var(--gs-muted)]">{{ formatDate(video.created_at) }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-3">
                                    <Link
                                        :href="route('growstream.creator.videos.edit', video.id)"
                                        class="text-sm font-medium text-[var(--gs-primary)] hover:text-[var(--gs-primary-hover)]"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="destroy(video)"
                                        class="text-sm font-medium text-red-400 hover:text-red-300"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="videos.data.length > 0" class="flex items-center justify-between border-t border-[var(--gs-border)] px-6 py-3">
                    <p class="text-sm text-[var(--gs-muted)]">
                        Showing {{ videos.from }} - {{ videos.to }} of {{ videos.total }}
                    </p>
                    <div class="flex gap-2">
                        <button
                            :disabled="!videos.prev_page_url"
                            @click="page(videos.current_page - 1)"
                            class="gs-btn gs-btn-outline px-3 py-1 text-sm disabled:opacity-50"
                        >
                            Prev
                        </button>
                        <button
                            :disabled="!videos.next_page_url"
                            @click="page(videos.current_page + 1)"
                            class="gs-btn gs-btn-outline px-3 py-1 text-sm disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </CreatorStudioLayout>
</template>

<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import CreatorStudioLayout from '@/Layouts/CreatorStudioLayout.vue';

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

const uploadBadge = (status: string): string => {
    switch (status) {
        case 'ready':
            return 'gs-chip-primary';
        case 'processing':
            return 'gs-chip-accent';
        case 'failed':
            return 'bg-red-500/15 text-red-400';
        default:
            return 'gs-chip-primary';
    }
};

const moderationBadge = (status: string): string => {
    switch (status) {
        case 'approved':
            return 'gs-chip-primary';
        case 'rejected':
            return 'bg-red-500/15 text-red-400';
        default:
            return 'gs-chip-accent';
    }
};
</script>
