import { describe, it, expect, beforeEach, vi } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { usePageStore } from '../pageStore';
import { useEditorStore } from '../editorStore';
import type { Site, Page } from '../../types';

vi.mock('@inertiajs/vue3', () => ({
    router: { visit: vi.fn(), reload: vi.fn(), get: vi.fn() },
}));

vi.mock('axios', () => ({
    default: {
        post: vi.fn().mockResolvedValue({ data: { success: true, page: { id: 2 } } }),
        put: vi.fn().mockResolvedValue({ data: { success: true } }),
        delete: vi.fn().mockResolvedValue({ data: { success: true } }),
    },
}));

function createSite(): Site {
    return { id: 1, name: 'Test', status: 'draft', url: '', settings: {} } as Site;
}

function createPage(overrides: Partial<Page> = {}): Page {
    return { id: 1, title: 'Home', slug: '/', isHomepage: true, showInNav: true, navOrder: 0, content: { sections: [] }, ...overrides } as Page;
}

vi.mock('../../config/pageTemplates', () => ({
    findTemplate: vi.fn().mockReturnValue(null),
}));

describe('pageStore', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
    });

    it('starts with modals closed', () => {
        const store = usePageStore();
        expect(store.showCreatePageModal).toBe(false);
        expect(store.showEditPageModal).toBe(false);
        expect(store.showApplyTemplateModal).toBe(false);
    });

    it('openCreatePageModal opens modal', () => {
        const store = usePageStore();
        store.openCreatePageModal();
        expect(store.showCreatePageModal).toBe(true);
        expect(store.pageError).toBeNull();
    });

    it('closeCreatePageModal closes modal', () => {
        const store = usePageStore();
        store.showCreatePageModal = true;
        store.closeCreatePageModal();
        expect(store.showCreatePageModal).toBe(false);
    });

    it('openEditPageModal sets editing page', () => {
        const store = usePageStore();
        const page = createPage();
        store.openEditPageModal(page);
        expect(store.showEditPageModal).toBe(true);
        expect(store.editingPage?.id).toBe(1);
    });

    it('savePage returns false when no active page', async () => {
        const store = usePageStore();
        const result = await store.savePage(1);
        expect(result).toBe(false);
    });

    it('savePage calls axios and updates lastSaved', async () => {
        const store = usePageStore();
        const editor = useEditorStore();
        const page = createPage({ content: { sections: [{ id: 's1', type: 'hero', content: {}, style: {} }] } });
        editor.initialize(createSite(), [page], page);

        const result = await store.savePage(1);

        expect(result).toBe(true);
        expect(editor.lastSaved).toBeInstanceOf(Date);
    });

    it('createPage validates title', async () => {
        const store = usePageStore();
        const form = { title: '', slug: '', templateId: '', showInNav: false };
        await store.createPage(form as any, 1, [createPage()]);
        expect(store.pageError).toBe('Please enter a page title');
    });

    it('createPage rejects duplicate slug', async () => {
        const store = usePageStore();
        const form = { title: 'Home', slug: '/', templateId: '', showInNav: false };
        await store.createPage(form as any, 1, [createPage()]);
        expect(store.pageError).toContain('already exists');
    });

    it('openApplyTemplateModal opens modal', () => {
        const store = usePageStore();
        store.openApplyTemplateModal();
        expect(store.showApplyTemplateModal).toBe(true);
    });
});
