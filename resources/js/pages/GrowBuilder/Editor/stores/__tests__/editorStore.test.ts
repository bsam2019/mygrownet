import { describe, it, expect, beforeEach } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useEditorStore } from '../editorStore';
import type { Site, Page, NavigationSettings, FooterSettings } from '../../types';

function createSite(overrides: Partial<Site> = {}): Site {
    return {
        id: 1,
        name: 'Test Site',
        status: 'draft',
        url: 'https://testsite.com',
        settings: {},
        ...overrides,
    } as Site;
}

function createPage(overrides: Partial<Page> = {}): Page {
    return {
        id: 1,
        title: 'Home',
        slug: '/',
        isHomepage: true,
        showInNav: true,
        navOrder: 0,
        content: { sections: [] },
        ...overrides,
    } as Page;
}

describe('editorStore', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
    });

    it('initializes with default state', () => {
        const store = useEditorStore();
        expect(store.leftSidebarOpen).toBe(true);
        expect(store.previewMode).toBe('desktop');
        expect(store.selectedSectionId).toBeNull();
        expect(store.sections).toEqual([]);
    });

    it('initialize loads site data', () => {
        const store = useEditorStore();
        const site = createSite({ status: 'published' });
        const pages = [createPage({ content: { sections: [{ id: 's1', type: 'hero', content: { title: 'Hello' }, style: {} }] } })];

        store.initialize(site, pages, pages[0]);

        expect(store.activePage?.id).toBe(1);
        expect(store.sections).toHaveLength(1);
        expect(store.isPublished).toBe(true);
        expect(store.siteNavigation.logoText).toBe('Test Site');
    });

    it('addSection adds and selects a section', () => {
        const store = useEditorStore();
        store.initialize(createSite(), [createPage()], createPage());

        store.addSection('hero');

        expect(store.sections).toHaveLength(1);
        expect(store.sections[0].type).toBe('hero');
        expect(store.selectedSectionId).toBe(store.sections[0].id);
    });

    it('duplicateSection clones a section', () => {
        const store = useEditorStore();
        store.initialize(createSite(), [createPage()], createPage());
        store.addSection('hero');
        const originalId = store.sections[0].id;

        store.duplicateSection(originalId);

        expect(store.sections).toHaveLength(2);
        expect(store.sections[1].type).toBe('hero');
        expect(store.selectedSectionId).toBe(store.sections[1].id);
    });

    it('deleteSection removes a section', () => {
        const store = useEditorStore();
        store.initialize(createSite(), [createPage()], createPage());
        store.addSection('hero');
        store.addSection('about');
        expect(store.sections).toHaveLength(2);

        store.deleteSection(store.sections[0].id);

        expect(store.sections).toHaveLength(1);
        expect(store.sections[0].type).toBe('about');
    });

    it('moveSection reorders sections', () => {
        const store = useEditorStore();
        store.initialize(createSite(), [createPage()], createPage());
        store.addSection('hero');
        store.addSection('about');
        const heroId = store.sections[0].id;

        store.moveSection(heroId, 'down');

        expect(store.sections[1].id).toBe(heroId);
    });

    it('selectSection sets tab to inspector', () => {
        const store = useEditorStore();
        store.initialize(createSite(), [createPage()], createPage());
        store.addSection('hero');
        store.activeLeftTab = 'widgets';

        store.selectSection(store.sections[0].id);

        expect(store.selectedSectionId).toBe(store.sections[0].id);
        expect(store.activeLeftTab).toBe('inspector');
    });

    it('selectedSection computed returns the right section', () => {
        const store = useEditorStore();
        store.sections = [
            { id: 's-hero', type: 'hero', content: {}, style: {} },
            { id: 's-about', type: 'about', content: {}, style: {} },
        ] as any;

        store.selectedSectionId = 's-about';
        expect(store.selectedSection?.type).toBe('about');

        store.selectedSectionId = 's-hero';
        expect(store.selectedSection?.type).toBe('hero');

        store.selectedSectionId = 'nonexistent';
        expect(store.selectedSection).toBeNull();
    });

    it('updateSectionContent modifies section content', () => {
        const store = useEditorStore();
        store.initialize(createSite(), [createPage()], createPage());
        store.addSection('hero');
        store.selectedSectionId = store.sections[0].id;

        store.updateSectionContent('title', 'New Title');

        expect(store.selectedSection?.content.title).toBe('New Title');
    });

    it('updateSectionStyle modifies section style', () => {
        const store = useEditorStore();
        store.initialize(createSite(), [createPage()], createPage());
        store.addSection('hero');
        store.selectedSectionId = store.sections[0].id;

        store.updateSectionStyle('backgroundColor', '#ff0000');

        expect(store.selectedSection?.style.backgroundColor).toBe('#ff0000');
    });

    it('setPreviewMode updates width', () => {
        const store = useEditorStore();

        store.setPreviewMode('mobile');

        expect(store.previewMode).toBe('mobile');
        expect(store.previewWidth).toBe(375);
    });

    it('isMobilePreview computed works', () => {
        const store = useEditorStore();

        store.setPreviewMode('mobile');
        expect(store.isMobilePreview).toBe(true);

        store.setPreviewMode('desktop');
        expect(store.isMobilePreview).toBe(false);
    });

    it('context menu show/close', () => {
        const store = useEditorStore();
        const e = { preventDefault: () => {} } as MouseEvent;

        store.showContextMenu(e, 'section-1');

        expect(store.contextMenu.visible).toBe(true);
        expect(store.contextMenu.sectionId).toBe('section-1');

        store.closeContextMenu();
        expect(store.contextMenu.visible).toBe(false);
    });

    it('addItem adds to services section (starts with 2 defaults)', () => {
        const store = useEditorStore();
        store.initialize(createSite(), [createPage()], createPage());
        store.addSection('services');
        store.selectedSectionId = store.sections[0].id;

        expect(store.selectedSection?.content.items).toHaveLength(2);

        store.addItem();

        expect(store.selectedSection?.content.items).toHaveLength(3);
    });

    it('removeItem removes from section items', () => {
        const store = useEditorStore();
        store.initialize(createSite(), [createPage()], createPage());
        store.addSection('services');
        store.selectedSectionId = store.sections[0].id;

        store.removeItem(0);

        expect(store.selectedSection?.content.items).toHaveLength(1);
        expect(store.selectedSection?.content.items[0].title).toBe('Service 2');
    });

    it('switchPage changes active page', () => {
        const store = useEditorStore();
        const page1 = createPage({ id: 1, content: { sections: [{ id: 's1', type: 'hero', content: {}, style: {} }] } });
        const page2 = createPage({ id: 2, title: 'About', slug: 'about', isHomepage: false });
        store.initialize(createSite(), [page1, page2], page1);

        store.switchPage(page2);

        expect(store.activePage?.id).toBe(2);
        expect(store.sections).toHaveLength(0);
        expect(store.selectedSectionId).toBeNull();
    });
});
