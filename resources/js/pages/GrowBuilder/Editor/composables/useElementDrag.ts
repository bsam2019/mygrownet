/**
 * Element Drag Composable
 * Handles dragging elements within sections for repositioning
 */

import { ref, type Ref } from 'vue';
import type { Section, DraggingElement } from '../types';

export interface UseElementDragOptions {
    sections: Ref<Section[]>;
}

export function useElementDrag(options: UseElementDragOptions) {
    const { sections } = options;

    // ============================================
    // Element Drag State
    // ============================================
    const draggingElement = ref<DraggingElement | null>(null);
    const dragStartPos = ref({ x: 0, y: 0 });
    const dragStartElementPos = ref({ x: 0, y: 0 });

    // ============================================
    // Section Content Drag State
    // ============================================
    const draggingSectionContent = ref<string | null>(null);
    const sectionContentDragStart = ref({ y: 0 });
    const sectionContentOffsetStart = ref(0);

    // ============================================
    // Element Drag Functions
    // ============================================
    const startElementDrag = (e: MouseEvent, sectionId: string, elementKey: string) => {
        e.preventDefault();
        e.stopPropagation();

        const section = sections.value.find(s => s.id === sectionId);
        if (!section) return;

        // Initialize element offsets if not exists
        if (!section.content.elementOffsets) {
            section.content.elementOffsets = {};
        }

        // Get current offset or default to 0
        const currentOffset = section.content.elementOffsets[elementKey] || { x: 0, y: 0 };

        draggingElement.value = { sectionId, elementKey };
        dragStartPos.value = { x: e.clientX, y: e.clientY };
        dragStartElementPos.value = { x: currentOffset.x, y: currentOffset.y };

        document.addEventListener('mousemove', handleElementDrag);
        document.addEventListener('mouseup', stopElementDrag);
        document.body.style.cursor = 'grabbing';
        document.body.style.userSelect = 'none';
    };

    const handleElementDrag = (e: MouseEvent) => {
        if (!draggingElement.value) return;

        const section = sections.value.find(s => s.id === draggingElement.value?.sectionId);
        if (!section) return;

        // Calculate delta - ONLY vertical movement
        const deltaY = e.clientY - dragStartPos.value.y;

        // Update element offset (only Y, X stays at 0)
        if (!section.content.elementOffsets) {
            section.content.elementOffsets = {};
        }
        section.content.elementOffsets[draggingElement.value.elementKey] = {
            x: 0,
            y: dragStartElementPos.value.y + deltaY,
        };
    };

    const stopElementDrag = () => {
        draggingElement.value = null;
        document.removeEventListener('mousemove', handleElementDrag);
        document.removeEventListener('mouseup', stopElementDrag);
        document.body.style.cursor = '';
        document.body.style.userSelect = '';
    };

    // ============================================
    // Section Content Drag Functions
    // ============================================
    const startSectionContentDrag = (e: MouseEvent, sectionId: string) => {
        e.preventDefault();
        e.stopPropagation();

        const section = sections.value.find(s => s.id === sectionId);
        if (!section) return;

        // Initialize contentOffset if not exists
        if (section.content.contentOffset === undefined) {
            section.content.contentOffset = 0;
        }

        draggingSectionContent.value = sectionId;
        sectionContentDragStart.value = { y: e.clientY };
        sectionContentOffsetStart.value = section.content.contentOffset || 0;

        document.addEventListener('mousemove', handleSectionContentDrag);
        document.addEventListener('mouseup', stopSectionContentDrag);
        document.body.style.cursor = 'ns-resize';
        document.body.style.userSelect = 'none';
    };

    const handleSectionContentDrag = (e: MouseEvent) => {
        if (!draggingSectionContent.value) return;

        const section = sections.value.find(s => s.id === draggingSectionContent.value);
        if (!section) return;

        const deltaY = e.clientY - sectionContentDragStart.value.y;
        section.content.contentOffset = sectionContentOffsetStart.value + deltaY;
    };

    const stopSectionContentDrag = () => {
        draggingSectionContent.value = null;
        document.removeEventListener('mousemove', handleSectionContentDrag);
        document.removeEventListener('mouseup', stopSectionContentDrag);
        document.body.style.cursor = '';
        document.body.style.userSelect = '';
    };

    // ============================================
    // Transform Helpers
    // ============================================
    const getElementTransform = (section: Section, elementKey: string): string => {
        const offset = section.content?.elementOffsets?.[elementKey];
        if (!offset || offset.y === 0) return '';
        return `translateY(${offset.y}px)`;
    };

    const getSectionContentTransform = (section: Section): string => {
        const offset = section.content?.contentOffset;
        if (!offset || offset === 0) return '';
        return `translateY(${offset}px)`;
    };

    // ============================================
    // Offset Helpers
    // ============================================
    const hasElementOffset = (section: Section, elementKey: string): boolean => {
        const offset = section.content?.elementOffsets?.[elementKey];
        return offset && offset.y !== 0;
    };

    const hasSectionContentOffset = (section: Section): boolean => {
        return section.content?.contentOffset && section.content.contentOffset !== 0;
    };

    const resetElementOffset = (sectionId: string, elementKey: string) => {
        const section = sections.value.find(s => s.id === sectionId);
        if (!section || !section.content.elementOffsets) return;
        section.content.elementOffsets[elementKey] = { x: 0, y: 0 };
    };

    const resetAllElementOffsets = (sectionId: string) => {
        const section = sections.value.find(s => s.id === sectionId);
        if (!section) return;
        section.content.elementOffsets = {};
    };

    const resetSectionContentOffset = (sectionId: string) => {
        const section = sections.value.find(s => s.id === sectionId);
        if (!section) return;
        section.content.contentOffset = 0;
    };

    return {
        // State
        draggingElement,
        draggingSectionContent,

        // Element Drag
        startElementDrag,

        // Section Content Drag
        startSectionContentDrag,

        // Transform Helpers
        getElementTransform,
        getSectionContentTransform,

        // Offset Helpers
        hasElementOffset,
        hasSectionContentOffset,
        resetElementOffset,
        resetAllElementOffsets,
        resetSectionContentOffset,
    };
}
