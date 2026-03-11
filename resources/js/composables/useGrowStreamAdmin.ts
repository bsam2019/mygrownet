import { ref, computed } from 'vue';
import axios from 'axios';
import type {
    Video,
    VideoSeries,
    CreatorProfile,
    AnalyticsOverview,
    PaginatedResponse,
    ApiResponse,
    VideoFilters,
} from '@/types/growstream';

export function useGrowStreamAdmin() {
    const loading = ref(false);
    const error = ref<string | null>(null);

    const baseUrl = '/api/v1/growstream/admin';

    // Helper function for API calls
    const apiCall = async <T>(
        method: 'get' | 'post' | 'put' | 'delete',
        endpoint: string,
        data?: any
    ): Promise<T> => {
        loading.value = true;
        error.value = null;

        try {
            const config = method === 'get' ? { params: data } : {};
            const response = await axios[method](`${baseUrl}${endpoint}`, method === 'get' ? config : data);
            return response.data;
        } catch (err: any) {
            error.value = err.response?.data?.error || err.message || 'An error occurred';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Video Management APIs
    const getVideos = (filters?: VideoFilters) => {
        return apiCall<PaginatedResponse<Video>>('get', '/videos', filters);
    };

    const getVideo = (videoId: number) => {
        return apiCall<ApiResponse<Video>>('get', `/videos/${videoId}`);
    };

    const uploadVideo = (formData: FormData) => {
        return axios.post(`${baseUrl}/videos/upload`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
    };

    const updateVideo = (videoId: number, data: Partial<Video>) => {
        return apiCall<ApiResponse<Video>>('put', `/videos/${videoId}`, data);
    };

    const deleteVideo = (videoId: number) => {
        return apiCall<ApiResponse<void>>('delete', `/videos/${videoId}`);
    };

    const publishVideo = (videoId: number) => {
        return apiCall<ApiResponse<Video>>('post', `/videos/${videoId}/publish`);
    };

    const unpublishVideo = (videoId: number) => {
        return apiCall<ApiResponse<Video>>('post', `/videos/${videoId}/unpublish`);
    };

    const bulkAction = (action: string, videoIds: number[]) => {
        return apiCall<ApiResponse<void>>('post', '/videos/bulk-action', { action, video_ids: videoIds });
    };

    const getFormData = () => {
        return apiCall<ApiResponse<any>>('get', '/videos/form-data');
    };

    // Series Management APIs
    const getSeries = (filters?: VideoFilters) => {
        return apiCall<PaginatedResponse<VideoSeries>>('get', '/series', filters);
    };

    const getSeriesDetails = (seriesId: number) => {
        return apiCall<ApiResponse<VideoSeries>>('get', `/series/${seriesId}`);
    };

    const createSeries = (data: Partial<VideoSeries>) => {
        return apiCall<ApiResponse<VideoSeries>>('post', '/series', data);
    };

    const updateSeries = (seriesId: number, data: Partial<VideoSeries>) => {
        return apiCall<ApiResponse<VideoSeries>>('put', `/series/${seriesId}`, data);
    };

    const deleteSeries = (seriesId: number) => {
        return apiCall<ApiResponse<void>>('delete', `/series/${seriesId}`);
    };

    const publishSeries = (seriesId: number) => {
        return apiCall<ApiResponse<VideoSeries>>('post', `/series/${seriesId}/publish`);
    };

    const unpublishSeries = (seriesId: number) => {
        return apiCall<ApiResponse<VideoSeries>>('post', `/series/${seriesId}/unpublish`);
    };

    const reorderEpisodes = (seriesId: number, episodes: any[]) => {
        return apiCall<ApiResponse<void>>('post', `/series/${seriesId}/reorder-episodes`, { episodes });
    };

    // Analytics APIs
    const getAnalyticsOverview = (period: number = 30) => {
        return apiCall<ApiResponse<AnalyticsOverview>>('get', '/analytics/overview', { period });
    };

    const getVideoAnalytics = (filters?: VideoFilters & { period?: number }) => {
        return apiCall<PaginatedResponse<Video>>('get', '/analytics/videos', filters);
    };

    const getVideoDetails = (videoId: number, period: number = 30) => {
        return apiCall<ApiResponse<any>>('get', `/analytics/videos/${videoId}`, { period });
    };

    const getCreatorAnalytics = (period: number = 30) => {
        return apiCall<ApiResponse<any>>('get', '/analytics/creators', { period });
    };

    const getEngagementAnalytics = (period: number = 30) => {
        return apiCall<ApiResponse<any>>('get', '/analytics/engagement', { period });
    };

    // Creator Management APIs
    const getCreators = (filters?: VideoFilters) => {
        return apiCall<PaginatedResponse<CreatorProfile>>('get', '/creators', filters);
    };

    const getCreator = (creatorId: number) => {
        return apiCall<ApiResponse<any>>('get', `/creators/${creatorId}`);
    };

    const verifyCreator = (creatorId: number) => {
        return apiCall<ApiResponse<CreatorProfile>>('post', `/creators/${creatorId}/verify`);
    };

    const unverifyCreator = (creatorId: number) => {
        return apiCall<ApiResponse<CreatorProfile>>('post', `/creators/${creatorId}/unverify`);
    };

    const suspendCreator = (creatorId: number, reason: string, unpublishVideos: boolean = false) => {
        return apiCall<ApiResponse<CreatorProfile>>('post', `/creators/${creatorId}/suspend`, {
            reason,
            unpublish_videos: unpublishVideos,
        });
    };

    const unsuspendCreator = (creatorId: number) => {
        return apiCall<ApiResponse<CreatorProfile>>('post', `/creators/${creatorId}/unsuspend`);
    };

    const updateCreatorLimits = (creatorId: number, limits: any) => {
        return apiCall<ApiResponse<CreatorProfile>>('put', `/creators/${creatorId}/limits`, limits);
    };

    return {
        // State
        loading: computed(() => loading.value),
        error: computed(() => error.value),

        // Video Management
        getVideos,
        getVideo,
        uploadVideo,
        updateVideo,
        deleteVideo,
        publishVideo,
        unpublishVideo,
        bulkAction,
        getFormData,

        // Series Management
        getSeries,
        getSeriesDetails,
        createSeries,
        updateSeries,
        deleteSeries,
        publishSeries,
        unpublishSeries,
        reorderEpisodes,

        // Analytics
        getAnalyticsOverview,
        getVideoAnalytics,
        getVideoDetails,
        getCreatorAnalytics,
        getEngagementAnalytics,

        // Creator Management
        getCreators,
        getCreator,
        verifyCreator,
        unverifyCreator,
        suspendCreator,
        unsuspendCreator,
        updateCreatorLimits,
    };
}
