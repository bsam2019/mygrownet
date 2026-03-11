/**
 * GrowStream Type Definitions
 */

export interface Video {
    id: number;
    title: string;
    slug: string;
    description: string;
    long_description?: string;
    content_type: 'movie' | 'series' | 'episode' | 'lesson' | 'short' | 'workshop' | 'webinar';
    access_level: 'free' | 'basic' | 'premium' | 'institutional';
    duration: number;
    poster_url?: string;
    thumbnail_url?: string;
    banner_url?: string;
    trailer_url?: string;
    playback_url?: string;
    language: string;
    content_rating: string;
    skill_level?: string;
    release_date?: string;
    is_published: boolean;
    is_featured: boolean;
    is_downloadable: boolean;
    view_count: number;
    total_watch_time: number;
    avg_watch_duration: number;
    completion_rate: number;
    series_id?: number;
    season_number?: number;
    episode_number?: number;
    creator_id: number;
    creator?: User;
    series?: VideoSeries;
    categories?: VideoCategory[];
    tags?: VideoTag[];
    created_at: string;
    updated_at: string;
}

export interface VideoSeries {
    id: number;
    title: string;
    slug: string;
    description: string;
    long_description?: string;
    content_type: 'series' | 'course' | 'workshop_series';
    access_level: 'free' | 'basic' | 'premium' | 'institutional';
    total_seasons: number;
    total_episodes: number;
    poster_url?: string;
    trailer_url?: string;
    release_year?: number;
    language: string;
    content_rating: string;
    is_published: boolean;
    is_featured: boolean;
    creator_id: number;
    creator?: User;
    videos?: Video[];
    categories?: VideoCategory[];
    created_at: string;
    updated_at: string;
}

export interface VideoCategory {
    id: number;
    name: string;
    slug: string;
    description?: string;
    icon?: string;
    parent_id?: number;
    is_active: boolean;
    display_order: number;
    children?: VideoCategory[];
    videos_count?: number;
}

export interface VideoTag {
    id: number;
    name: string;
    slug: string;
    usage_count: number;
}

export interface WatchHistory {
    id: number;
    user_id: number;
    video_id: number;
    current_position: number;
    watch_duration: number;
    progress_percentage: number;
    is_completed: boolean;
    video?: Video;
    created_at: string;
    updated_at: string;
}

export interface Watchlist {
    id: number;
    user_id: number;
    watchable_type: string;
    watchable_id: number;
    watchable?: Video | VideoSeries;
    created_at: string;
}

export interface CreatorProfile {
    id: number;
    user_id: number;
    display_name: string;
    bio?: string;
    avatar_url?: string;
    is_verified: boolean;
    is_active: boolean;
    total_videos: number;
    total_views: number;
    total_revenue: number;
    user?: User;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
}

export interface PaginatedResponse<T> {
    data: T[];
    meta: {
        current_page: number;
        per_page: number;
        total: number;
        last_page: number;
    };
}

export interface ApiResponse<T> {
    success: boolean;
    data: T;
    message?: string;
}

export interface PlaybackAuthorization {
    signed_url: string;
    expires_at: string;
    video: Video;
}

export interface AnalyticsOverview {
    overview: {
        total_videos: number;
        published_videos: number;
        new_videos_this_period: number;
        total_views: number;
        views_this_period: number;
        total_watch_time_hours: number;
        watch_time_this_period_hours: number;
        unique_viewers: number;
        unique_viewers_this_period: number;
        completion_rate: number;
        avg_watch_duration_seconds: number;
    };
    top_categories: Array<{
        name: string;
        view_count: number;
    }>;
    daily_views: Array<{
        date: string;
        views: number;
        unique_viewers: number;
    }>;
}

export interface VideoFilters {
    search?: string;
    category?: string;
    content_type?: string;
    access_level?: string;
    sort_by?: string;
    sort_order?: 'asc' | 'desc';
    per_page?: number;
    page?: number;
}
