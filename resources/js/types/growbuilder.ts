/**
 * GrowBuilder Type Definitions
 */

export interface MediaItem {
    id: number;
    url: string;
    webpUrl?: string;
    thumbnailUrl?: string;
    filename: string;
    originalName: string;
    size: string;
    sizeBytes: number;
    width: number;
    height: number;
    aspectRatio: string;           // "16:9", "3:2", etc.
    aspectRatioDecimal: number;    // 1.78, 1.5, etc.
    mimeType: string;
    fileTypeBadge: {
        bg: string;
        text: string;
        label: string;
    };
    variants?: Record<string, string>;
}

export interface ImageRequirements {
    width: number;
    height: number;
    aspectRatio: number;
    minWidth?: number;
    maxWidth?: number;
    minHeight?: number;
    maxHeight?: number;
    maxSize?: number; // bytes
    formats?: string[];
    description?: string;
}

export interface ComponentImageConfig {
    [fieldName: string]: ImageRequirements;
}

export interface Section {
    id: string;
    type: string;
    content: Record<string, any>;
    style?: Record<string, any>;
}

export interface SiteNavigation {
    logo?: string;
    items: Array<{
        label: string;
        url: string;
        children?: Array<{
            label: string;
            url: string;
        }>;
    }>;
}

export interface SiteFooter {
    logo?: string;
    content?: string;
    columns?: Array<{
        title: string;
        links: Array<{
            label: string;
            url: string;
        }>;
    }>;
}
