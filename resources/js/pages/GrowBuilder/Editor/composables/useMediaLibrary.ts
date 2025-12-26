/**
 * Media Library Composable
 * Handles image upload and media selection
 */

import { ref, type Ref } from 'vue';
import axios from 'axios';
import type { Section, NavigationSettings, FooterSettings, MediaLibraryTarget } from '../types';

export interface MediaItem {
    id: number;
    url: string;
    thumbnailUrl?: string;
    originalName: string;
}

export interface UseMediaLibraryOptions {
    siteId: number;
    sections: Ref<Section[]>;
    siteNavigation: Ref<NavigationSettings>;
    siteFooter: Ref<FooterSettings>;
}

export function useMediaLibrary(options: UseMediaLibraryOptions) {
    const { siteId, sections, siteNavigation, siteFooter } = options;

    // State
    const uploadingImage = ref(false);
    const imageUploadError = ref<string | null>(null);
    const mediaLibrary = ref<MediaItem[]>([]);
    const showMediaLibrary = ref(false);
    const mediaLibraryTarget = ref<MediaLibraryTarget | null>(null);

    // Load media library
    const loadMediaLibrary = async () => {
        try {
            const response = await axios.get(`/growbuilder/media/${siteId}`);
            mediaLibrary.value = response.data.data || [];
        } catch (error) {
            console.error('Failed to load media library:', error);
        }
    };

    // Open media library
    const openMediaLibrary = async (
        target: 'navigation' | 'footer' | 'section',
        fieldOrSectionId: string,
        field?: string,
        itemIndex?: number
    ) => {
        if (target === 'navigation' || target === 'footer') {
            mediaLibraryTarget.value = { target, field: fieldOrSectionId };
        } else {
            mediaLibraryTarget.value = { target: 'section', sectionId: fieldOrSectionId, field: field!, itemIndex };
        }
        showMediaLibrary.value = true;
        await loadMediaLibrary();
    };

    // Upload image
    const uploadImage = async (event: Event) => {
        const input = event.target as HTMLInputElement;
        if (!input.files?.length) return;

        const file = input.files[0];
        uploadingImage.value = true;
        imageUploadError.value = null;

        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await axios.post(`/growbuilder/media/${siteId}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });

            if (response.data.success) {
                mediaLibrary.value.unshift(response.data.media);
                selectMediaImage(response.data.media);
            }
        } catch (error: any) {
            imageUploadError.value = error.response?.data?.message || 'Failed to upload image';
        } finally {
            uploadingImage.value = false;
            input.value = '';
        }
    };

    // Select media image
    const selectMediaImage = (media: MediaItem) => {
        if (!mediaLibraryTarget.value) return;

        const { target, sectionId, field, itemIndex } = mediaLibraryTarget.value;

        if (target === 'navigation') {
            siteNavigation.value.logo = media.url;
        } else if (target === 'footer') {
            siteFooter.value.logo = media.url;
        } else if (target === 'section' && sectionId) {
            const section = sections.value.find(s => s.id === sectionId);
            if (section && field) {
                if (itemIndex !== undefined && section.content.items) {
                    section.content.items[itemIndex][field] = media.url;
                } else if (field === 'images' && section.type === 'gallery') {
                    if (!section.content.images) section.content.images = [];
                    section.content.images.push({ id: media.id, url: media.url, alt: media.originalName });
                } else {
                    section.content[field] = media.url;
                }
            }
        }

        showMediaLibrary.value = false;
        mediaLibraryTarget.value = null;
    };

    // Remove gallery image
    const removeGalleryImage = (sectionId: string, imageIndex: number) => {
        const section = sections.value.find(s => s.id === sectionId);
        if (section?.content.images) {
            section.content.images.splice(imageIndex, 1);
        }
    };

    return {
        // State
        uploadingImage,
        imageUploadError,
        mediaLibrary,
        showMediaLibrary,
        mediaLibraryTarget,

        // Actions
        openMediaLibrary,
        uploadImage,
        selectMediaImage,
        removeGalleryImage,
    };
}
