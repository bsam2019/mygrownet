import { describe, it, expect } from 'vitest';
import { useHistory } from '../useHistory';
import type { Section, NavigationSettings, FooterSettings } from '../../types';

function createMockNav(overrides: Partial<NavigationSettings> = {}): NavigationSettings {
    return {
        logoText: 'Test Site',
        logo: '',
        navItems: [],
        showCta: true,
        ctaText: 'Contact',
        ctaLink: '#contact',
        sticky: true,
        style: 'default',
        ...overrides,
    };
}

function createMockFooter(overrides: Partial<FooterSettings> = {}): FooterSettings {
    return {
        logo: '',
        copyrightText: '© Test',
        showSocialLinks: false,
        socialLinks: [],
        columns: [],
        showNewsletter: false,
        newsletterTitle: '',
        backgroundColor: '#1f2937',
        textColor: '#ffffff',
        layout: 'columns',
        ...overrides,
    };
}

function createMockSections(count = 2): Section[] {
    return Array.from({ length: count }, (_, i) => ({
        id: `section-${i}`,
        type: 'hero' as const,
        content: { title: `Section ${i}` },
        style: {},
    }));
}

describe('useHistory', () => {
    it('starts with empty undo/redo stacks', () => {
        const history = useHistory();
        expect(history.canUndo.value).toBe(false);
        expect(history.canRedo.value).toBe(false);
        expect(history.undoCount.value).toBe(0);
        expect(history.redoCount.value).toBe(0);
    });

    it('initializes history with initial state', () => {
        const history = useHistory();
        const sections = createMockSections();
        const nav = createMockNav();
        const footer = createMockFooter();

        history.initHistory(sections, nav, footer);

        expect(history.canUndo.value).toBe(false);
        expect(history.undoCount.value).toBe(0);
    });

    it('pushes state to undo stack and clears redo', () => {
        const history = useHistory();
        const sections = createMockSections();
        const nav = createMockNav();
        const footer = createMockFooter();

        history.initHistory(sections, nav, footer);
        history.pushState(sections, nav, footer);

        expect(history.canUndo.value).toBe(true);
        expect(history.undoCount.value).toBe(1);
        expect(history.canRedo.value).toBe(false);

        history.pushState(sections, nav, footer);
        expect(history.undoCount.value).toBe(2);
    });

    it('undo restores previous state', () => {
        const history = useHistory();
        const sections = createMockSections(1);
        const nav = createMockNav();
        const footer = createMockFooter();

        history.initHistory(sections, nav, footer);

        const modifiedSections = createMockSections(3);
        history.pushState(modifiedSections, nav, footer);

        expect(history.canUndo.value).toBe(true);

        const result = history.undo();
        expect(result).not.toBeNull();
        expect(result!.sections).toHaveLength(1);
        expect(history.canRedo.value).toBe(true);
        expect(history.canUndo.value).toBe(false);
    });

    it('redo restores the state after undo', () => {
        const history = useHistory();
        const sections = createMockSections(1);
        const nav = createMockNav();
        const footer = createMockFooter();

        history.initHistory(sections, nav, footer);

        const modifiedSections = createMockSections(3);
        history.pushState(modifiedSections, nav, footer);

        history.undo();
        expect(history.canRedo.value).toBe(true);

        const result = history.redo();
        expect(result).not.toBeNull();
        expect(result!.sections).toHaveLength(3);
        expect(history.canUndo.value).toBe(true);
        expect(history.canRedo.value).toBe(false);
    });

    it('returns null when nothing to undo', () => {
        const history = useHistory();
        expect(history.undo()).toBeNull();
    });

    it('returns null when nothing to redo', () => {
        const history = useHistory();
        expect(history.redo()).toBeNull();
    });

    it('clearHistory resets all stacks', () => {
        const history = useHistory();
        const sections = createMockSections();
        const nav = createMockNav();
        const footer = createMockFooter();

        history.initHistory(sections, nav, footer);
        history.pushState(sections, nav, footer);
        expect(history.undoCount.value).toBe(1);

        history.clearHistory();
        expect(history.undoCount.value).toBe(0);
        expect(history.redoCount.value).toBe(0);
        expect(history.canUndo.value).toBe(false);
        expect(history.canRedo.value).toBe(false);
    });

    it('enforces maxHistory limit', () => {
        const history = useHistory({ maxHistory: 3 });
        const sections = createMockSections();
        const nav = createMockNav();
        const footer = createMockFooter();

        history.initHistory(sections, nav, footer);

        for (let i = 0; i < 5; i++) {
            history.pushState(createMockSections(i + 1), nav, footer);
        }

        expect(history.undoCount.value).toBe(3);
    });

    it('hasChanges reflects whether undo/redo stacks are non-empty', () => {
        const history = useHistory();
        expect(history.hasChanges.value).toBe(false);

        const sections = createMockSections();
        const nav = createMockNav();
        const footer = createMockFooter();

        history.initHistory(sections, nav, footer);
        history.pushState(sections, nav, footer);

        expect(history.hasChanges.value).toBe(true);
    });

    it('undo/redo preserves data integrity (deep clone)', () => {
        const history = useHistory();
        const sections = createMockSections(1);
        const nav = createMockNav();
        const footer = createMockFooter();

        history.initHistory(sections, nav, footer);

        sections.push({
            id: 'section-99',
            type: 'about',
            content: { title: 'Added' },
            style: {},
        });
        history.pushState(sections, nav, footer);

        const undone = history.undo();
        expect(undone!.sections).toHaveLength(1);

        const redone = history.redo();
        expect(redone!.sections).toHaveLength(2);
    });
});
