<template>
    <AdminLayout title="Content Moderation - GrowStream Admin">
        <div class="mx-auto max-w-7xl">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-[var(--gs-text)]">Content Moderation</h1>
                <p class="mt-2 text-[var(--gs-muted)]">Review creator-submitted content before publishing</p>
            </div>

            <!-- Stats -->
            <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="gs-card p-6">
                    <p class="text-sm font-medium text-[var(--gs-muted)]">Pending Review</p>
                    <p class="mt-2 text-3xl font-bold text-[var(--gs-accent)]">{{ stats.pending }}</p>
                </div>
                <div class="gs-card p-6">
                    <p class="text-sm font-medium text-[var(--gs-muted)]">Approved</p>
                    <p class="mt-2 text-3xl font-bold text-[var(--gs-primary)]">{{ stats.approved }}</p>
                </div>
                <div class="gs-card p-6">
                    <p class="text-sm font-medium text-[var(--gs-muted)]">Rejected</p>
                    <p class="mt-2 text-3xl font-bold text-red-400">{{ stats.rejected }}</p>
                </div>
            </div>

            <div
                v-if="videos.data.length === 0"
                class="gs-card flex flex-col items-center border-2 border-dashed border-[var(--gs-border)] py-16 text-center"
            >
                <p class="text-lg font-medium text-[var(--gs-text)]">No videos pending review</p>
                <p class="mt-1 text-sm text-[var(--gs-muted)]">Creator uploads will appear here for moderation.</p>
            </div>

            <div v-else class="space-y-4">
                <div v-for="video in videos.data" :key="video.id" class="gs-card p-6">
                    <div class="flex items-start gap-4">
                        <img
                            v-if="video.thumbnail_url"
                            :src="video.thumbnail_url"
                            :alt="video.title"
                            class="h-20 w-32 rounded object-cover"
                        />
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-semibold text-[var(--gs-text)]">{{ video.title }}</h3>
                                <span class="gs-chip gs-chip-accent">Pending Review</span>
                            </div>
                            <p class="mt-1 line-clamp-2 text-sm text-[var(--gs-muted)]">{{ video.description }}</p>
                            <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-[var(--gs-muted)]">
                                <span>By {{ video.creator?.user?.name || 'Unknown' }}</span>
                                <span v-if="video.categories?.length">· {{ video.categories.map((c: any) => c.name).join(', ') }}</span>
                                <span>· {{ video.upload_status }}</span>
                                <span>· {{ formatDate(video.created_at) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-3 border-t border-[var(--gs-border)] pt-4">
                        <button
                            @click="approve(video.id)"
                            class="gs-btn gs-btn-primary"
                        >
                            Approve
                        </button>
                        <button
                            @click="publish(video.id)"
                            class="gs-btn gs-btn-accent"
                        >
                            Approve & Publish
                        </button>
                        <form @submit.prevent="reject(video.id)" class="flex flex-1 items-center gap-2">
                            <input
                                v-model="reasons[video.id]"
                                type="text"
                                placeholder="Rejection reason (required)"
                                class="gs-input flex-1"
                                required
                            />
                            <button
                                type="submit"
                                class="gs-btn bg-red-500/15 text-red-400"
                            >
                                Reject
                            </button>
                        </form>
                    </div>
                </div>

                <div class="flex items-center justify-between">
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
    </AdminLayout>
</template>

<script setup lang="ts">
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

interface ModerationVideo {
    id: number;
    title: string;
    description: string;
    thumbnail_url: string | null;
    upload_status: string;
    moderation_status: string;
    created_at: string;
    creator?: { user?: { name?: string } } | null;
    categories?: { id: number; name: string }[];
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
    videos: Paginated<ModerationVideo>;
    stats: { pending: number; approved: number; rejected: number };
    filters: Record<string, any>;
}

defineProps<Props>();

const reasons = reactive<Record<number, string>>({});

const formatDate = (dateString: string): string => new Date(dateString).toLocaleDateString();

const page = (pageNumber: number) => {
    router.get(route('growstream.admin.moderation'), { page: pageNumber }, { preserveState: true });
};

const approve = (id: number) => {
    router.post(route('growstream.admin.moderation.approve', id));
};

const publish = (id: number) => {
    router.post(route('growstream.admin.moderation.publish', id));
};

const reject = (id: number) => {
    router.post(route('growstream.admin.moderation.reject', id), { reason: reasons[id] });
};
</script>
