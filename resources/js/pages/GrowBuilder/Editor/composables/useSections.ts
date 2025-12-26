/**
 * Sections Composable
 * Handles section CRUD operations, drag/drop, and resize functionality
 */

import { ref, type Ref } from 'vue';
import type { Section, SectionType } from '../types';
import { getDefaultContent } from '../config/sectionDefaults';

export interface UseSectionsOptions {
    sections: Ref<Section[]>;
    selectedSectionId: Ref<string | null>;
    siteName: string;
    pages: Array<{ id: number; title: string; slug: string; isHomepage: boolean; showInNav: boolean }>;
}

export function useSections(options: UseSectionsOptions) {
    const { sections, selectedSectionId, siteName, pages } = options;

    // ============================================
    // Resize State
    // ============================================
    const resizingSection = ref<string | null>(null);
    const resizeStartY = ref(0);
    const resizeStartHeight = ref(0);

    // ============================================
    // Section CRUD
    // ============================================
    const selectSection = (id: string) => {
        selectedSectionId.value = id;
    };

    const addSection = (type: SectionType) => {
        const newSection: Section = {
            id: `section-${Date.now()}`,
            type,
            content: getDefaultContent(type, siteName, pages),
            style: { backgroundColor: '#ffffff', textColor: '#111827' },
        };
        sections.value.push(newSection);
        selectedSectionId.value = newSection.id;
        return newSection;
    };

    const duplicateSection = (id: string) => {
        const index = sections.value.findIndex(s => s.id === id);
        if (index === -1) return null;
        
        const original = sections.value[index];
        const duplicate: Section = {
            ...JSON.parse(JSON.stringify(original)),
            id: `section-${Date.now()}`,
        };
        sections.value.splice(index + 1, 0, duplicate);
        selectedSectionId.value = duplicate.id;
        return duplicate;
    };

    const deleteSection = (id: string) => {
        const index = sections.value.findIndex(s => s.id === id);
        if (index === -1) return false;
        
        sections.value.splice(index, 1);
        if (selectedSectionId.value === id) {
            selectedSectionId.value = null;
        }
        return true;
    };

    const moveSection = (id: string, direction: 'up' | 'down') => {
        const index = sections.value.findIndex(s => s.id === id);
        if (index === -1) return false;
        
        const newIndex = direction === 'up' ? index - 1 : index + 1;
        if (newIndex < 0 || newIndex >= sections.value.length) return false;
        
        const [section] = sections.value.splice(index, 1);
        sections.value.splice(newIndex, 0, section);
        return true;
    };

    // ============================================
    // Section Content/Style Updates
    // ============================================
    const updateSectionContent = (sectionId: string, key: string, value: any) => {
        const section = sections.value.find(s => s.id === sectionId);
        if (section) {
            section.content[key] = value;
        }
    };

    const updateSectionStyle = (sectionId: string, key: string, value: any) => {
        const section = sections.value.find(s => s.id === sectionId);
        if (section) {
            section.style[key] = value;
        }
    };

    // ============================================
    // Section Resize
    // ============================================
    const startResize = (e: MouseEvent, sectionId: string) => {
        e.preventDefault();
        e.stopPropagation();
        resizingSection.value = sectionId;
        resizeStartY.value = e.clientY;

        const section = sections.value.find(s => s.id === sectionId);
        const sectionEl = document.querySelector(`[data-section-id="${sectionId}"]`) as HTMLElement;
        resizeStartHeight.value = section?.style?.minHeight || sectionEl?.offsetHeight || 200;

        document.addEventListener('mousemove', handleResize);
        document.addEventListener('mouseup', stopResize);
        document.body.style.cursor = 'ns-resize';
        document.body.style.userSelect = 'none';
    };

    const handleResize = (e: MouseEvent) => {
        if (!resizingSection.value) return;

        const deltaY = e.clientY - resizeStartY.value;
        const newHeight = Math.max(100, resizeStartHeight.value + deltaY);

        const section = sections.value.find(s => s.id === resizingSection.value);
        if (section) {
            if (!section.style) section.style = {};
            section.style.minHeight = newHeight;
        }
    };

    const stopResize = () => {
        resizingSection.value = null;
        document.removeEventListener('mousemove', handleResize);
        document.removeEventListener('mouseup', stopResize);
        document.body.style.cursor = '';
        document.body.style.userSelect = '';
    };

    // ============================================
    // Clone for Drag & Drop
    // ============================================
    const cloneSection = (type: SectionType): Section => {
        return {
            id: `section-${Date.now()}`,
            type,
            content: getDefaultContent(type, siteName, pages),
            style: { backgroundColor: '#ffffff', textColor: '#111827' },
        };
    };

    return {
        // State
        resizingSection,
        
        // CRUD
        selectSection,
        addSection,
        duplicateSection,
        deleteSection,
        moveSection,
        
        // Updates
        updateSectionContent,
        updateSectionStyle,
        
        // Resize
        startResize,
        
        // Drag & Drop
        cloneSection,
    };
}
