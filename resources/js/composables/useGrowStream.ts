import { ref, computed } from 'vue';
import axios from 'axios';
import type {
    Video,
    VideoSeries,
    VideoCategory,
    WatchHistory,
    Watchlist,
    PaginatedResponse,
    ApiResponse,
    PlaybackAuthorization,
    VideoFilters,
} from '@/types/growstream';

export function useGrowStream() {
    const loading = ref(false);
    const error = ref<string | null>(null);

    const baseUrl = '/api/v1/growstream';

    // Helper function for API calls
    const apiCall = async <T>(
        method: 'get' | 'post' | 'put' | 'delete',
        endpoint: string,
        data?: any
    ): Promise<T> => {
        loading.value = true;
        error.value = null;

        try {
            const response = await axios[method](`${baseUrl}${endpoint}`, data);
            return response.data;
        } catch (err: any) {
            error.value = err.response?.data?.error || err.message || 'An error occurred';
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Video APIs
    const getVideos = (filters?: VideoFilters) => {
        const params = new URLSearchParams();
        if (filters) {
            Object.entries(filters).forEach(([key, value]) => {
                if (value !== undefined && value !== null) {
                    params.append(key, String(value));
                }
            });
        }
        return apiCall<PaginatedResponse<Video>>('get', `/videos?${params.toString()}`);
    };

    const getFeaturedVideos = () => {
        return apiCall<ApiResponse<Video[]>>('get', '/videos/featured');
    };

    const getTrendingVideos = () => {
        return apiCall<ApiResponse<Video[]>>('get', '/videos/trending');
    };

    const getVideo = (slug: string) => {
        return apiCall<ApiResponse<Video>>('get', `/videos/${slug}`);
    };

    // Series APIs
    const getSeries = (filters?: VideoFilters) => {
        const params = new URLSearchParams();
        if (filters) {
            Object.entries(filters).forEach(([key, value]) => {
                if (value !== undefined && value !== null) {
                    params.append(key, String(value));
                }
            });
        }
        return apiCall<PaginatedResponse<VideoSeries>>('get', `/series?${params.toString()}`);
    };

    const getSeriesDetails = (slug: string) => {
        return apiCall<ApiResponse<VideoSeries>>('get', `/series/${slug}`);
    };

    // Category APIs
    const getCategories = () => {
        return apiCall<ApiResponse<VideoCategory[]>>('get', '/categories');
    };

    const getCategoryVideos = (slug: string, filters?: VideoFilters) => {
        const params = new URLSearchParams();
        if (filters) {
            Object.entries(filters).forEach(([key, value]) => {
                if (value !== undefined && value !== null) {
                    params.append(key, String(value));
                }
            });
        }
        return apiCall<PaginatedResponse<Video>>('get', `/categories/${slug}/videos?${params.toString()}`);
    };

    // Watch APIs (authenticated)
    const authorizePlayback = (videoId: number) => {
        return apiCall<ApiResponse<PlaybackAuthorization>>('post', '/watch/authorize', {
            video_id: videoId,
        });
    };

    const updateWatchProgress = (videoId: number, currentPosition: number, watchDuration: number) => {
        return apiCall<ApiResponse<WatchHistory>>('post', '/watch/progress', {
            video_id: videoId,
            current_position: currentPosition,
            watch_duration: watchDuration,
        });
    };

    const getWatchHistory = () => {
        return apiCall<ApiResponse<WatchHistory[]>>('get', '/watch/history');
    };

    const getContinueWatching = () => {
        return apiCall<ApiResponse<WatchHistory[]>>('get', '/continue-watching');
    };

    // Watchlist APIs (authenticated)
    const getWatchlist = () => {
        return apiCall<ApiResponse<Watchlist[]>>('get', '/watchlist');
    };

    const addToWatchlist = (watchableType: string, watchableId: number) => {
        return apiCall<ApiResponse<Watchlist>>('post', '/watchlist', {
            watchlistable_type: watchableType,
            watchlistable_id: watchableId,
        });
    };

    const removeFromWatchlist = (watchlistId: number) => {
        return apiCall<ApiResponse<void>>('delete', `/watchlist/${watchlistId}`);
    };

    // Utility functions
    const formatDuration = (seconds: number): string => {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = Math.floor(seconds % 60);

        if (hours > 0) {
            return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }
        return `${minutes}:${secs.toString().padStart(2, '0')}`;
    };

    const getAccessLevelBadge = (accessLevel: string): { text: string; color: string } => {
        const badges = {
            free: { text: 'Free', color: 'bg-green-500' },
            basic: { text: 'Basic', color: 'bg-blue-500' },
            premium: { text: 'Premium', color: 'bg-purple-500' },
            institutional: { text: 'Institutional', color: 'bg-indigo-500' },
        };
        return badges[accessLevel as keyof typeof badges] || badges.free;
    };

    const getContentTypeLabel = (contentType: string): string => {
        const labels: Record<string, string> = {
            movie: 'Movie',
            series: 'Series',
            episode: 'Episode',
            short: 'Short',
            comedy: 'Comedy',
            skit: 'Skits',
            soap: 'Soap Opera',
            drama: 'Drama',
            lesson: 'Lesson',
            workshop: 'Workshop',
            webinar: 'Webinar',
        };
        return labels[contentType] || contentType;
    };

    /**
     * Get responsive thumbnail URL for a video
     * @param video - The video object
     * @param size - Size variant: 'thumb' (320x180), 'medium' (640x360), or 'large' (1280x720)
     * @param preferWebp - Whether to prefer WebP format over JPEG (default: true)
     * @returns The thumbnail URL or fallback
     */
    const getThumbnailUrl = (
        video: Video,
        size: 'thumb' | 'medium' | 'large' = 'medium',
        preferWebp = true
    ): string => {
        // Priority 1: Custom Wasabi thumbnails
        if (video.thumbnail_storage_disk === 'wasabi' && video.thumbnail_sizes) {
            const sizeVariant = video.thumbnail_sizes[size];
            if (sizeVariant) {
                // Prefer WebP if available and requested
                if (preferWebp && sizeVariant.webp) {
                    return sizeVariant.webp;
                }
                // Fallback to JPEG
                if (sizeVariant.jpeg) {
                    return sizeVariant.jpeg;
                }
            }
        }

        // Priority 2: Use the accessor value (handles Cloudflare auto-gen + placeholder)
        // The backend getThumbnailUrlAttribute() already implements the full waterfall
        return video.thumbnail_url || '/images/video-placeholder.jpg';
    };

    return {
        // State
        loading: computed(() => loading.value),
        error: computed(() => error.value),

        // Video APIs
        getVideos,
        getFeaturedVideos,
        getTrendingVideos,
        getVideo,

        // Series APIs
        getSeries,
        getSeriesDetails,

        // Category APIs
        getCategories,
        getCategoryVideos,

        // Watch APIs
        authorizePlayback,
        updateWatchProgress,
        getWatchHistory,
        getContinueWatching,

        // Watchlist APIs
        getWatchlist,
        addToWatchlist,
        removeFromWatchlist,

        // Utilities
        formatDuration,
        getAccessLevelBadge,
        getContentTypeLabel,
        getThumbnailUrl,
    };
}
