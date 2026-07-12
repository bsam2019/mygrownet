import { describe, it, expect, beforeEach, vi } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useMediaStore } from '../mediaStore';
import { useEditorStore } from '../editorStore';
import type { Site, Page } from '../../types';

vi.mock('axios', () => ({
    default: {
        get: vi.fn().mockResolvedValue({ data: { data: [] } }),
        post: vi.fn().mockResolvedValue({ data: { success: true, media: { id: 1, url: 'https://example.com/img.jpg', originalName: 'test.jpg' } } }),
        delete: vi.fn().mockResolvedValue({ data: { success: true } }),
    },
}));

function createSite(): Site {
    return { id: 1, name: 'Test', status: 'draft', url: '', settings: {} } as Site;
}

function createPage(): Page {
    return { id: 1, title: 'Home', slug: '/', isHomepage: true, showInNav: true, navOrder: 0, content: { sections: [] } } as Page;
}

describe('mediaStore', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        const editor = useEditorStore();
        editor.initialize(createSite(), [createPage()], createPage());
    });

    it('starts with default state', () => {
        const store = useMediaStore();
        expect(store.showMediaLibrary).toBe(false);
        expect(store.uploadingImage).toBe(false);
        expect(store.imageUploadError).toBeNull();
        expect(store.mediaLibrary).toEqual([]);
    });

    it('openMediaLibrary sets target and opens modal', async () => {
        const store = useMediaStore();
        await store.openMediaLibrary(1, 'navigation', 'logo');
        expect(store.showMediaLibrary).toBe(true);
        expect(store.mediaLibraryTarget?.target).toBe('navigation');
        expect(store.mediaLibraryTarget?.field).toBe('logo');
    });

    it('openMediaLibrary with section sets requirements', async () => {
        const store = useMediaStore();
        const editor = useEditorStore();
        editor.addSection('hero');
        const sectionId = editor.sections[0].id;

        await store.openMediaLibrary(1, 'section', sectionId, 'backgroundImage');

        expect(store.showMediaLibrary).toBe(true);
        expect(store.mediaLibraryTarget?.sectionId).toBe(sectionId);
    });

    it('closeMediaLibrary resets state', () => {
        const store = useMediaStore();
        store.showMediaLibrary = true;
        store.mediaLibraryTarget = { target: 'navigation', field: 'logo' };

        store.closeMediaLibrary();

        expect(store.showMediaLibrary).toBe(false);
        expect(store.mediaLibraryTarget).toBeNull();
    });

    it('selectMediaImage sets navigation logo', () => {
        const store = useMediaStore();
        const editor = useEditorStore();
        store.mediaLibraryTarget = { target: 'navigation', field: 'logo' };

        store.selectMediaImage({ id: 1, url: 'https://example.com/logo.png', originalName: 'logo.png' });

        expect(editor.siteNavigation.logo).toBe('https://example.com/logo.png');
        expect(store.showMediaLibrary).toBe(false);
    });

    it('selectMediaImage sets footer logo', () => {
        const store = useMediaStore();
        const editor = useEditorStore();
        store.mediaLibraryTarget = { target: 'footer', field: 'logo' };

        store.selectMediaImage({ id: 1, url: 'https://example.com/footer-logo.png', originalName: 'footer.png' });

        expect(editor.siteFooter.logo).toBe('https://example.com/footer-logo.png');
    });

    it('selectMediaImage sets section content field', () => {
        const store = useMediaStore();
        const editor = useEditorStore();
        editor.addSection('hero');
        const sectionId = editor.sections[0].id;
        store.mediaLibraryTarget = { target: 'section', sectionId, field: 'backgroundImage' };

        store.selectMediaImage({ id: 2, url: 'https://example.com/bg.jpg', originalName: 'bg.jpg' });

        expect(editor.sections[0].content.backgroundImage).toBe('https://example.com/bg.jpg');
    });

    it('selectMediaImage adds to gallery', () => {
        const store = useMediaStore();
        const editor = useEditorStore();
        editor.addSection('gallery');
        const sectionId = editor.sections[0].id;
        store.mediaLibraryTarget = { target: 'section', sectionId, field: 'images' };

        store.selectMediaImage({ id: 3, url: 'https://example.com/gallery.jpg', originalName: 'gallery.jpg' });

        expect(editor.sections[0].content.images).toHaveLength(1);
        expect(editor.sections[0].content.images[0].url).toBe('https://example.com/gallery.jpg');
    });

    it('removeGalleryImage removes from gallery', () => {
        const store = useMediaStore();
        const editor = useEditorStore();
        editor.addSection('gallery');
        const sectionId = editor.sections[0].id;
        editor.sections[0].content.images = [
            { id: 1, url: 'img1.jpg', alt: '1' },
            { id: 2, url: 'img2.jpg', alt: '2' },
        ];

        store.removeGalleryImage(sectionId, 0);

        expect(editor.sections[0].content.images).toHaveLength(1);
        expect(editor.sections[0].content.images[0].id).toBe(2);
    });
});
