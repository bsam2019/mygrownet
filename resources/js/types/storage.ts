export interface StoragePlan {
    id: number;
    name: string;
    slug: string;
    quota_bytes: number;
    max_file_size_bytes: number;
    allowed_mime_types: string[] | null;
    allow_sharing: boolean;
    allow_public_profile_files: boolean;
    is_active: boolean;
    formatted_quota: string;
    formatted_max_file_size: string;
}

export interface StorageFolder {
    id: string;
    user_id: number;
    parent_id: string | null;
    name: string;
    path_cache: string | null;
    created_at: string;
    updated_at: string;
}

export interface StorageFile {
    id: string;
    user_id: number;
    folder_id: string | null;
    original_name: string;
    extension: string;
    mime_type: string;
    size_bytes: number;
    formatted_size: string;
    created_at: string;
    updated_at: string;
}

export interface StorageUsage {
    used_bytes: number;
    quota_bytes: number;
    remaining_bytes: number;
    percent_used: number;
    files_count: number;
    plan_name: string;
    plan_slug: string;
    near_limit: boolean;
    formatted_used: string;
    formatted_quota: string;
}

export interface UploadProgress {
    file_id: string;
    filename: string;
    size: number;
    progress: number;
    status: 'pending' | 'uploading' | 'completing' | 'completed' | 'error';
    error?: string;
}

export interface BreadcrumbItem {
    id: string | null;
    name: string;
}
