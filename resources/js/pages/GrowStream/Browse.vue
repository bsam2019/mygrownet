<template>
    <GrowStreamLayout title="Browse Videos - GrowStream">
        <div class="mx-auto max-w-7xl">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="mb-2 text-4xl font-bold text-[var(--gs-text)]">Discover</h1>
                <p class="text-lg text-[var(--gs-muted)]">Explore content across categories and creators</p>
            </div>

            <!-- Filters -->
            <div class="mb-8 flex flex-wrap gap-4">
                <!-- Search -->
                <div class="flex-1 min-w-[300px]">
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Search videos..."
                        class="gs-input"
                        @input="debouncedSearch"
                    />
                </div>

                <!-- Category Filter -->
                <select
                    v-model="filters.category"
                    class="gs-input w-auto"
                    @change="applyFilters"
                >
                    <option value="">All Categories</option>
                    <option v-for="category in categories" :key="category.id" :value="category.slug">
                        {{ category.name }}
                    </option>
                </select>

                <!-- Content Type Filter -->
                <select
                    v-model="filters.content_type"
                    class="gs-input w-auto"
                    @change="applyFilters"
                >
                    <option value="">All Types</option>
                    <option value="movie">Movies</option>
                    <option value="series">Series</option>
                    <option value="episode">Episodes</option>
                    <option value="short">Shorts</option>
                    <option value="comedy">Comedy</option>
                    <option value="skit">Skits</option>
                    <option value="soap">Soap Operas</option>
                    <option value="drama">Drama</option>
                    <option value="lesson">Lessons</option>
                    <option value="workshop">Workshops</option>
                    <option value="webinar">Webinars</option>
                </select>

                <!-- Sort -->
                <select
                    v-model="filters.sort_by"
                    class="gs-input w-auto"
                    @change="applyFilters"
                >
                    <option value="created_at">Newest</option>
                    <option value="view_count">Most Viewed</option>
                    <option value="title">Title A-Z</option>
                </select>
            </div>

            <!-- Featured Videos -->
            <div v-if="featuredVideos.length > 0 && !filters.search" class="mb-12">
                <VideoGrid
                    :videos="featuredVideos"
                    title="Featured Content"
                    :show-description="true"
                />
            </div>

            <!-- All Videos -->
            <VideoGrid
                :videos="videos.data"
                :title="filters.search ? `Search Results for '${filters.search}'` : 'All Videos'"
                :loading="loading"
                :show-pagination="true"
                :meta="videos.meta"
                :watch-progress="watchProgress"
                @page-change="handlePageChange"
            />
        </div>
    </GrowStreamLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import GrowStreamLayout from '@/Layouts/GrowStreamLayout.vue';
import VideoGrid from '@/Components/GrowStream/VideoGrid.vue';
import type { Video, VideoCategory, PaginatedResponse } from '@/types/growstream';

interface Props {
    videos: PaginatedResponse<Video>;
    featuredVideos?: Video[];
    categories?: VideoCategory[];
    watchProgress?: Record<number, number>;
}

const props = withDefaults(defineProps<Props>(), {
    featuredVideos: () => [],
    categories: () => [],
    watchProgress: () => ({}),
});

const loading = ref(false);
const filters = reactive({
    search: '',
    category: '',
    content_type: '',
    sort_by: 'created_at',
    page: 1,
});

let searchTimeout: ReturnType<typeof setTimeout>;

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
};

const applyFilters = () => {
    router.get(
        route('growstream.browse'),
        {
            ...filters,
            page: 1,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const handlePageChange = (page: number) => {
    router.get(
        route('growstream.browse'),
        {
            ...filters,
            page,
        },
        {
            preserveState: true,
            preserveScroll: false,
        }
    );
};
</script>
