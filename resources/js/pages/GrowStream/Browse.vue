<template>
    <AppLayout title="Browse Videos - GrowStream">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Hero Section -->
            <div class="mb-8 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 p-8 text-white">
                <h1 class="mb-2 text-4xl font-bold">GrowStream</h1>
                <p class="text-lg text-blue-100">Discover educational content to grow your skills and knowledge</p>
            </div>

            <!-- Filters -->
            <div class="mb-8 flex flex-wrap gap-4">
                <!-- Search -->
                <div class="flex-1 min-w-[300px]">
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Search videos..."
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        @input="debouncedSearch"
                    />
                </div>

                <!-- Category Filter -->
                <select
                    v-model="filters.category"
                    class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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
                    class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    @change="applyFilters"
                >
                    <option value="">All Types</option>
                    <option value="lesson">Lessons</option>
                    <option value="workshop">Workshops</option>
                    <option value="webinar">Webinars</option>
                    <option value="movie">Movies</option>
                    <option value="short">Shorts</option>
                </select>

                <!-- Sort -->
                <select
                    v-model="filters.sort_by"
                    class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
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
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import VideoGrid from '@/Components/GrowStream/VideoGrid.vue';
import { useGrowStream } from '@/composables/useGrowStream';
import type { Video, VideoCategory, PaginatedResponse } from '@/types/growstream';

interface Props {
    videos: PaginatedResponse<Video>;
    featuredVideos: Video[];
    categories: VideoCategory[];
    watchProgress?: Record<number, number>;
}

const props = defineProps<Props>();

const { getVideos, getFeaturedVideos } = useGrowStream();

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
            page: 1, // Reset to first page on filter change
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
