<template>
    <CreatorStudioLayout title="Creator Dashboard - GrowStream">
        <div>
            <div class="mb-8 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-3xl font-bold text-[var(--gs-text)]">Creator Dashboard</h1>
                    <p class="mt-2 text-[var(--gs-muted)]">{{ profile.channel_name || profile.display_name }}</p>
                </div>
                <Link
                    :href="route('growstream.creator.videos.create')"
                    class="gs-btn gs-btn-accent"
                >
                    Upload Video
                </Link>
            </div>

            <!-- My Channel -->
            <div class="gs-card mb-6 flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-[var(--gs-primary-soft)]">
                        <svg class="h-7 w-7 text-[var(--gs-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold text-[var(--gs-text)]">My Channel</h2>
                        <p class="truncate text-sm text-[var(--gs-muted)]">
                            {{ channelUrl }}
                        </p>
                        <p class="mt-0.5 text-xs text-[var(--gs-muted)]">
                            Share this link on Facebook, TikTok, or WhatsApp to grow your audience.
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button class="gs-btn gs-btn-outline" @click="copyChannelLink">
                        {{ copied ? 'Copied!' : 'Copy Link' }}
                    </button>
                    <Link
                        :href="channelUrl"
                        class="gs-btn gs-btn-primary"
                    >
                        View Channel
                    </Link>
                </div>
            </div>

            <!-- Unverified banner -->
            <div
                v-if="!profile.is_verified"
                class="mb-6 flex items-center gap-3 rounded-[var(--gs-radius)] border border-[var(--gs-accent)]/30 bg-[var(--gs-accent-soft)] p-4 text-sm text-[var(--gs-accent)]"
            >
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>You're not verified yet. Verified creators get their uploads auto-approved.</span>
            </div>

            <!-- Empty state (first upload) -->
            <div v-if="(recentVideos || []).length === 0" class="gs-card mb-8 p-12 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[var(--gs-primary-soft)]">
                    <svg class="h-8 w-8 text-[var(--gs-primary)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0-12l-4 4m4-4l4 4M4 20h16" />
                    </svg>
                </div>
                <h2 class="mb-2 text-xl font-semibold text-[var(--gs-text)]">Upload your first video</h2>
                <p class="mx-auto mb-6 max-w-md text-sm text-[var(--gs-muted)]">
                    Your audience starts here. Upload your first video, and once approved it'll be
                    available to viewers across GrowStream.
                </p>
                <Link
                    :href="route('growstream.creator.videos.create')"
                    class="gs-btn gs-btn-primary"
                >
                    Start Uploading
                </Link>
            </div>

            <!-- Stats -->
            <div v-else class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="gs-card p-6">
                    <p class="text-sm font-medium text-[var(--gs-muted)]">Total Videos</p>
                    <p class="mt-2 text-3xl font-bold text-[var(--gs-text)]">{{ profile.total_videos }}</p>
                </div>
                <div class="gs-card p-6">
                    <p class="text-sm font-medium text-[var(--gs-muted)]">Total Views</p>
                    <p class="mt-2 text-3xl font-bold text-[var(--gs-text)]">{{ formatNumber(profile.total_views) }}</p>
                </div>
                <div class="gs-card p-6">
                    <p class="text-sm font-medium text-[var(--gs-muted)]">Watch Time (hrs)</p>
                    <p class="mt-2 text-3xl font-bold text-[var(--gs-text)]">{{ watchTimeHours ?? '—' }}</p>
                </div>
                <div class="gs-card p-6">
                    <p class="text-sm font-medium text-[var(--gs-muted)]">Earnings (ZMW)</p>
                    <p class="mt-2 text-3xl font-bold text-[var(--gs-accent)]">
                        {{ formatMoney(earningsSummary?.total_earnings) }}
                    </p>
                    <p v-if="earningsSummary?.pending_payout" class="mt-1 text-xs text-[var(--gs-muted)]">
                        {{ formatMoney(earningsSummary.pending_payout) }} pending
                    </p>
                </div>
            </div>

            <!-- Recent content list -->
            <div v-if="(recentVideos || []).length > 0">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-[var(--gs-text)]">Recent Content</h2>
                    <Link
                        :href="route('growstream.creator.videos.index')"
                        class="text-sm font-medium text-[var(--gs-accent)] hover:opacity-85"
                    >
                        View all
                    </Link>
                </div>
                <div class="gs-surface overflow-hidden">
                    <div class="hidden grid-cols-12 gap-4 border-b border-[var(--gs-border)] px-5 py-3 text-xs font-semibold uppercase tracking-wider text-[var(--gs-muted)] md:grid">
                        <div class="col-span-5">Title</div>
                        <div class="col-span-3">Upload Status</div>
                        <div class="col-span-2">Moderation</div>
                        <div class="col-span-2 text-right">Views</div>
                    </div>
                    <div
                        v-for="video in (recentVideos || [])"
                        :key="video.id"
                        class="grid grid-cols-1 gap-3 border-b border-[var(--gs-border)] px-5 py-4 last:border-b-0 md:grid-cols-12 md:items-center md:gap-4"
                    >
                        <div class="flex items-center gap-3 md:col-span-5">
                            <img
                                v-if="video.thumbnail_url"
                                :src="video.thumbnail_url"
                                :alt="video.title"
                                class="h-12 w-20 shrink-0 rounded object-cover"
                            />
                            <div v-else class="flex h-12 w-20 shrink-0 items-center justify-center rounded bg-[var(--gs-bg-elevated)]">
                                <svg class="h-6 w-6 text-[var(--gs-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-[var(--gs-text)]">{{ video.title }}</p>
                                <p class="text-xs text-[var(--gs-muted)]">{{ video.content_type }}</p>
                            </div>
                        </div>
                        <div class="md:col-span-3">
                            <span :class="uploadBadge(video.upload_status)" class="gs-chip">
                                {{ video.upload_status?.replace('_', ' ') }}
                            </span>
                        </div>
                        <div class="md:col-span-2">
                            <span :class="moderationBadge(video.moderation_status)" class="gs-chip">
                                {{ video.moderation_status?.replace('_', ' ') }}
                            </span>
                        </div>
                        <div class="text-sm text-[var(--gs-muted)] md:col-span-2 md:text-right">
                            {{ video.view_count }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CreatorStudioLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import CreatorStudioLayout from '@/Layouts/CreatorStudioLayout.vue';

interface VideoRow {
    id: number;
    title: string;
    thumbnail_url?: string | null;
    content_type: string;
    upload_status: string;
    moderation_status: string;
    view_count: number;
}

interface Props {
    profile: Record<string, any>;
    recentVideos?: VideoRow[];
    earningsSummary?: { total_earnings: number; pending_payout: number } | null;
    watchTimeHours?: number | null;
}

const props = withDefaults(defineProps<Props>(), {
    recentVideos: () => [],
    earningsSummary: () => ({ total_earnings: 0, pending_payout: 0 }),
    watchTimeHours: null,
});

const copied = ref(false);

const channelUrl = computed(() => {
    const slug = props.profile.channel_slug || props.profile.display_name || 'creator';
    return `${window.location.origin}/c/${encodeURIComponent(slug)}`;
});

const copyChannelLink = async () => {
    try {
        await navigator.clipboard.writeText(channelUrl.value);
        copied.value = true;
        setTimeout(() => (copied.value = false), 2000);
    } catch {
        copied.value = false;
    }
};

const formatNumber = (value: number | undefined): string => {
    return (value ?? 0).toLocaleString();
};

const formatMoney = (value: number | undefined): string => {
    return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2 });
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
