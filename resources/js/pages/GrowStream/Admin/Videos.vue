<template>
    <AppLayout title="Video Management - GrowStream Admin">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Video Management</h1>
                    <p class="mt-2 text-gray-600">Manage all videos on the platform</p>
                </div>
                <button
                    @click="showUploadModal = true"
                    class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Upload Video
                </button>
            </div>

            <!-- Filters -->
            <div class="mb-6 flex flex-wrap gap-4">
                <input
                    v-model="filters.search"
                    type="text"
                    placeholder="Search videos..."
                    class="flex-1 min-w-[300px] rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    @input="debouncedSearch"
                />
                <select
                    v-model="filters.status"
                    class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    @change="applyFilters"
                >
                    <option value="">All Status</option>
                    <option value="ready">Ready</option>
                    <option value="processing">Processing</option>
                    <option value="uploading">Uploading</option>
                    <option value="failed">Failed</option>
                </select>
                <select
                    v-model="filters.is_published"
                    class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    @change="applyFilters"
                >
                    <option value="">All Videos</option>
                    <option value="true">Published</option>
                    <option value="false">Unpublished</option>
                </select>
            </div>

            <!-- Bulk Actions -->
            <div v-if="selectedVideos.length > 0" class="mb-6 flex items-center gap-4 rounded-lg bg-blue-50 p-4">
                <span class="text-sm font-medium text-blue-900">{{ selectedVideos.length }} selected</span>
                <div class="flex gap-2">
                    <button
                        @click="handleBulkAction('publish')"
                        class="rounded-lg bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700"
                    >
                        Publish
                    </button>
                    <button
                        @click="handleBulkAction('unpublish')"
                        class="rounded-lg bg-yellow-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-yellow-700"
                    >
                        Unpublish
                    </button>
                    <button
                        @click="handleBulkAction('delete')"
                        class="rounded-lg bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700"
                    >
                        Delete
                    </button>
                </div>
                <button @click="selectedVideos = []" class="ml-auto text-sm text-blue-600 hover:text-blue-700">
                    Clear Selection
                </button>
            </div>

            <!-- Videos Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input
                                    type="checkbox"
                                    :checked="selectedVideos.length === videos.data.length"
                                    @change="toggleSelectAll"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Video
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Views
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Created
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="video in videos.data" :key="video.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <input
                                    type="checkbox"
                                    :checked="selectedVideos.includes(video.id)"
                                    @change="toggleSelect(video.id)"
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img
                                        :src="video.thumbnail_url || '/placeholder.jpg'"
                                        :alt="video.title"
                                        class="h-16 w-24 rounded object-cover"
                                    />
                                    <div>
                                        <div class="font-medium text-gray-900">{{ video.title }}</div>
                                        <div class="text-sm text-gray-500">{{ video.content_type }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    :class="[
                                        getStatusColor(video.upload_status),
                                        'inline-flex rounded-full px-2 py-1 text-xs font-semibold',
                                    ]"
                                >
                                    {{ video.upload_status }}
                                </span>
                                <span
                                    v-if="video.is_published"
                                    class="ml-2 inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800"
                                >
                                    Published
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ video.view_count.toLocaleString() }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ formatDate(video.created_at) }}
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <button
                                        v-if="!video.is_published"
                                        @click="publishVideo(video.id)"
                                        class="text-green-600 hover:text-green-900"
                                        title="Publish"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M5 13l4 4L19 7"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        @click="editVideo(video)"
                                        class="text-blue-600 hover:text-blue-900"
                                        title="Edit"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                            />
                                        </svg>
                                    </button>
                                    <button
                                        @click="deleteVideoHandler(video.id)"
                                        class="text-red-600 hover:text-red-900"
                                        title="Delete"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                            />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="flex items-center justify-between border-t border-gray-200 bg-white px-6 py-4">
                    <div class="text-sm text-gray-700">
                        Showing {{ (videos.meta.current_page - 1) * videos.meta.per_page + 1 }} to
                        {{ Math.min(videos.meta.current_page * videos.meta.per_page, videos.meta.total) }} of
                        {{ videos.meta.total }} results
                    </div>
                    <div class="flex gap-2">
                        <button
                            :disabled="videos.meta.current_page === 1"
                            @click="changePage(videos.meta.current_page - 1)"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Previous
                        </button>
                        <button
                            :disabled="videos.meta.current_page === videos.meta.last_page"
                            @click="changePage(videos.meta.current_page + 1)"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload Modal -->
        <VideoUploadModal :show="showUploadModal" @close="showUploadModal = false" @uploaded="handleVideoUploaded" />
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import VideoUploadModal from '@/Components/GrowStream/VideoUploadModal.vue';
import { useGrowStreamAdmin } from '@/composables/useGrowStreamAdmin';
import type { Video, PaginatedResponse } from '@/types/growstream';

interface Props {
    videos: PaginatedResponse<Video>;
}

const props = defineProps<Props>();

const { publishVideo: publishVideoApi, deleteVideo, bulkAction } = useGrowStreamAdmin();

const filters = reactive({
    search: '',
    status: '',
    is_published: '',
    page: 1,
});

const selectedVideos = ref<number[]>([]);
const showUploadModal = ref(false);

let searchTimeout: ReturnType<typeof setTimeout>;

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
};

const applyFilters = () => {
    router.get(
        route('growstream.admin.videos'),
        { ...filters, page: 1 },
        { preserveState: true, preserveScroll: true }
    );
};

const changePage = (page: number) => {
    router.get(
        route('growstream.admin.videos'),
        { ...filters, page },
        { preserveState: true, preserveScroll: false }
    );
};

const toggleSelect = (videoId: number) => {
    const index = selectedVideos.value.indexOf(videoId);
    if (index > -1) {
        selectedVideos.value.splice(index, 1);
    } else {
        selectedVideos.value.push(videoId);
    }
};

const toggleSelectAll = () => {
    if (selectedVideos.value.length === props.videos.data.length) {
        selectedVideos.value = [];
    } else {
        selectedVideos.value = props.videos.data.map((v) => v.id);
    }
};

const publishVideo = async (videoId: number) => {
    try {
        await publishVideoApi(videoId);
        router.reload({ only: ['videos'] });
    } catch (error) {
        console.error('Failed to publish video:', error);
    }
};

const editVideo = (video: Video) => {
    // Navigate to edit page
    router.visit(route('growstream.admin.videos.edit', video.id));
};

const deleteVideoHandler = async (videoId: number) => {
    if (!confirm('Are you sure you want to delete this video?')) return;

    try {
        await deleteVideo(videoId);
        router.reload({ only: ['videos'] });
    } catch (error) {
        console.error('Failed to delete video:', error);
    }
};

const handleBulkAction = async (action: string) => {
    if (!confirm(`Are you sure you want to ${action} ${selectedVideos.value.length} videos?`)) return;

    try {
        await bulkAction(action, selectedVideos.value);
        selectedVideos.value = [];
        router.reload({ only: ['videos'] });
    } catch (error) {
        console.error('Bulk action failed:', error);
    }
};

const getStatusColor = (status: string): string => {
    const colors: Record<string, string> = {
        ready: 'bg-green-100 text-green-800',
        processing: 'bg-yellow-100 text-yellow-800',
        uploading: 'bg-blue-100 text-blue-800',
        failed: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString();
};

const handleVideoUploaded = () => {
    router.reload({ only: ['videos'] });
};
</script>
