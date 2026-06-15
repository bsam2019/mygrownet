import { describe, it, expect } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useHistoryStore } from '../historyStore';
import type { Section, NavigationSettings, FooterSettings } from '../../types';

function mockNav(): NavigationSettings {
    return { logoText: 'Test', logo: '', navItems: [], showCta: true, ctaText: '', ctaLink: '', sticky: true, style: 'default' };
}

function mockFooter(): FooterSettings {
    return { logo: '', copyrightText: '', showSocialLinks: false, socialLinks: [], columns: [], showNewsletter: false, newsletterTitle: '', backgroundColor: '', textColor: '', layout: 'columns' };
}

function mockSections(n = 2): Section[] {
    return Array.from({ length: n }, (_, i) => ({ id: `s-${i}`, type: 'hero' as const, content: { title: `S${i}` }, style: {} }));
}

describe('historyStore', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
    });

    it('starts empty', () => {
        const store = useHistoryStore();
        expect(store.canUndo).toBe(false);
        expect(store.canRedo).toBe(false);
    });

    it('pushes and undoes', () => {
        const store = useHistoryStore();
        store.initHistory(mockSections(1), mockNav(), mockFooter());
        store.pushState(mockSections(3), mockNav(), mockFooter());
        expect(store.canUndo).toBe(true);
        const state = store.undo();
        expect(state).not.toBeNull();
        expect(state!.sections).toHaveLength(1);
    });

    it('redoes after undo', () => {
        const store = useHistoryStore();
        store.initHistory(mockSections(1), mockNav(), mockFooter());
        store.pushState(mockSections(3), mockNav(), mockFooter());
        store.undo();
        const state = store.redo();
        expect(state).not.toBeNull();
        expect(state!.sections).toHaveLength(3);
    });
});
