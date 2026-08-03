<template>
    <AppLayout title="Creator Dashboard - GrowStream">
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="mb-8 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Creator Dashboard</h1>
                    <p class="mt-2 text-gray-600">{{ profile.channel_name || profile.display_name }}</p>
                </div>
                <div class="flex gap-3">
                    <Link
                        :href="route('growstream.creator.videos.create')"
                        class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                    >
                        Upload Video
                    </Link>
                </div>
            </div>

            <!-- Status banner -->
            <div v-if="!profile.is_verified" class="mb-6 flex items-center gap-3 rounded-lg bg-amber-50 p-4 text-sm text-amber-800">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>You're not verified yet. Verified creators get their uploads auto-approved.</span>
            </div>

            <!-- Stats -->
            <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg bg-white p-6 shadow">
                    <p class="text-sm font-medium text-gray-600">Total Videos</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ profile.total_videos }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow">
                    <p class="text-sm font-medium text-gray-600">Total Views</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ profile.total_views.toLocaleString() }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow">
                    <p class="text-sm font-medium text-gray-600">Subscribers</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ profile.subscriber_count }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow">
                    <p class="text-sm font-medium text-gray-600">Pending Payout</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">
                        K{{ Number(profile.pending_payout || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                    </p>
                </div>
            </div>

            <!-- Quick links -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <Link
                    :href="route('growstream.creator.videos.index')"
                    class="group rounded-lg border border-gray-200 bg-white p-6 shadow-sm hover:border-blue-400"
                >
                    <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600">My Videos</h3>
                    <p class="mt-1 text-sm text-gray-600">Manage your uploaded content and check review status.</p>
                </Link>
                <Link
                    :href="route('growstream.creator.videos.create')"
                    class="group rounded-lg border border-gray-200 bg-white p-6 shadow-sm hover:border-blue-400"
                >
                    <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600">Upload New Video</h3>
                    <p class="mt-1 text-sm text-gray-600">Submit a new video for review and publishing.</p>
                </Link>
                <Link
                    :href="route('growstream.creator.analytics')"
                    class="group rounded-lg border border-gray-200 bg-white p-6 shadow-sm hover:border-blue-400"
                >
                    <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600">Analytics</h3>
                    <p class="mt-1 text-sm text-gray-600">Track views, watch time, and performance.</p>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface Props {
    profile: Record<string, any>;
}

defineProps<Props>();
</script>
