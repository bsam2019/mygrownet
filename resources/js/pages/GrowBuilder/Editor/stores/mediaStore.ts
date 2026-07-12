import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';
import type { ImageRequirements } from '@/types/growbuilder';
import { getImageRequirements } from '../config/sectionImageRequirements';
import { useEditorStore } from './editorStore';

interface MediaItem {
    id: number;
    url: string;
    thumbnailUrl?: string;
    originalName: string;
}

interface MediaLibraryTarget {
    sectionId?: string;
    field: string;
    itemIndex?: number;
    target?: 'navigation' | 'footer' | 'section';
}

export const useMediaStore = defineStore('editor-media', () => {
    const uploadingImage = ref(false);
    const imageUploadError = ref<string | null>(null);
    const mediaLibrary = ref<MediaItem[]>([]);
    const showMediaLibrary = ref(false);
    const mediaLibraryTarget = ref<MediaLibraryTarget | null>(null);
    const mediaLibraryRequirements = ref<ImageRequirements | null>(null);
    const mediaLibrarySectionType = ref<string | null>(null);
    const mediaLibraryFieldName = ref<string | null>(null);

    async function loadMediaLibrary(siteId: number) {
        try {
            const response = await axios.get(`/growbuilder/media/${siteId}`);
            mediaLibrary.value = response.data.data || [];
        } catch (error) {
            console.error('Failed to load media library:', error);
        }
    }

    async function openMediaLibrary(
        siteId: number,
        target: 'navigation' | 'footer' | 'section',
        fieldOrSectionId: string,
        field?: string,
        itemIndex?: number,
    ) {
        const editor = useEditorStore();

        if (target === 'navigation' || target === 'footer') {
            mediaLibraryTarget.value = { target, field: fieldOrSectionId };
            mediaLibraryRequirements.value = null;
            mediaLibrarySectionType.value = null;
            mediaLibraryFieldName.value = null;
        } else {
            mediaLibraryTarget.value = { target: 'section', sectionId: fieldOrSectionId, field: field!, itemIndex };
            const section = editor.sections.find(s => s.id === fieldOrSectionId);
            if (section && field) {
                const requirements = getImageRequirements(section.type, field);
                mediaLibraryRequirements.value = requirements;
                mediaLibrarySectionType.value = section.type;
                mediaLibraryFieldName.value = field;
            } else {
                mediaLibraryRequirements.value = null;
                mediaLibrarySectionType.value = null;
                mediaLibraryFieldName.value = null;
            }
        }
        showMediaLibrary.value = true;
        await loadMediaLibrary(siteId);
    }

    function closeMediaLibrary() {
        showMediaLibrary.value = false;
        mediaLibraryTarget.value = null;
        mediaLibraryRequirements.value = null;
        mediaLibrarySectionType.value = null;
        mediaLibraryFieldName.value = null;
    }

    async function uploadImage(event: Event, siteId: number) {
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
    }

    function selectMediaImage(media: MediaItem) {
        if (!mediaLibraryTarget.value) return;
        const editor = useEditorStore();
        const { target, sectionId, field, itemIndex } = mediaLibraryTarget.value;

        if (target === 'navigation') {
            editor.siteNavigation.logo = media.url;
        } else if (target === 'footer') {
            editor.siteFooter.logo = media.url;
        } else if (target === 'section' && sectionId) {
            const section = editor.sections.find(s => s.id === sectionId);
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
    }

    function removeGalleryImage(sectionId: string, imageIndex: number) {
        const editor = useEditorStore();
        const section = editor.sections.find(s => s.id === sectionId);
        if (section?.content.images) {
            section.content.images.splice(imageIndex, 1);
        }
    }

    return {
        uploadingImage, imageUploadError, mediaLibrary, showMediaLibrary,
        mediaLibraryTarget, mediaLibraryRequirements, mediaLibrarySectionType, mediaLibraryFieldName,
        loadMediaLibrary, openMediaLibrary, closeMediaLibrary, uploadImage,
        selectMediaImage, removeGalleryImage,
    };
});
