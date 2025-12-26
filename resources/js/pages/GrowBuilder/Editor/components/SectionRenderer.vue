<script setup lang="ts">
/**
 * Section Renderer Component
 * Dynamically renders the appropriate section component based on type
 * This replaces the massive v-if/v-else-if chain in Index.vue
 */
import { computed } from 'vue';
import { Squares2X2Icon } from '@heroicons/vue/24/outline';
import { sectionComponents } from './sections';
import type { Section } from '../types';

const props = defineProps<{
    section: Section;
    isMobile: boolean;
    textSize: { h1: string; h2: string; h3: string; p: string };
    spacing: { section: string; gap: string };
    gridCols2: string;
    gridCols3: string;
    gridCols4: string;
    getSectionContentTransform: (section: Section) => string;
    getElementTransform: (section: Section, elementKey: string) => string;
    hasElementOffset: (section: Section, elementKey: string) => boolean;
    isEditing: (sectionId: string, field: string, itemIndex?: number) => boolean;
    editingValue: string;
    startInlineEdit: (sectionId: string, field: string, value: string, itemIndex?: number) => void;
    saveInlineEdit: () => void;
    handleInlineKeydown: (e: KeyboardEvent) => void;
    startElementDrag: (e: MouseEvent, sectionId: string, elementKey: string) => void;
    resetAllElementOffsets: (sectionId: string) => void;
    selectedSectionId: string | null;
    draggingElement: { sectionId: string; elementKey: string } | null;
}>();

const emit = defineEmits<{
    (e: 'update:editingValue', value: string): void;
}>();

// Get the component for this section type
const SectionComponent = computed(() => {
    return sectionComponents[props.section.type as keyof typeof sectionComponents] || null;
});

// Check if this is a known section type
const isKnownType = computed(() => !!SectionComponent.value);

// Compute background style (solid or gradient)
const backgroundStyle = computed(() => {
    const style = props.section.style;
    
    if (style?.backgroundType === 'gradient' && style?.gradientFrom && style?.gradientTo) {
        const degMap: Record<string, string> = {
            'to-r': '90deg',
            'to-b': '180deg',
            'to-br': '135deg',
            'to-tr': '45deg',
        };
        const direction = degMap[style.gradientDirection || 'to-r'] || '90deg';
        return {
            background: `linear-gradient(${direction}, ${style.gradientFrom}, ${style.gradientTo})`,
        };
    }
    
    return {
        backgroundColor: style?.backgroundColor || '#ffffff',
    };
});
</script>

<template>
    <div
        class="section-preview h-full"
        :style="backgroundStyle"
    >
        <!-- Dynamic Section Component -->
        <component
            v-if="isKnownType"
            :is="SectionComponent"
            :section="section"
            :isMobile="isMobile"
            :textSize="textSize"
            :spacing="spacing"
            :gridCols2="gridCols2"
            :gridCols3="gridCols3"
            :gridCols4="gridCols4"
            :getSectionContentTransform="getSectionContentTransform"
            :getElementTransform="getElementTransform"
            :hasElementOffset="hasElementOffset"
            :isEditing="isEditing"
            :editingValue="editingValue"
            :startInlineEdit="startInlineEdit"
            :saveInlineEdit="saveInlineEdit"
            :handleInlineKeydown="handleInlineKeydown"
            :startElementDrag="startElementDrag"
            :resetAllElementOffsets="resetAllElementOffsets"
            :selectedSectionId="selectedSectionId"
            :draggingElement="draggingElement"
            @update:editingValue="emit('update:editingValue', $event)"
        />

        <!-- Fallback for Unknown Section Types -->
        <div
            v-else
            class="h-full flex flex-col justify-center items-center text-center text-gray-500"
            :class="spacing.section"
        >
            <Squares2X2Icon
                :class="isMobile ? 'w-8 h-8' : 'w-12 h-12'"
                class="mx-auto mb-3 text-gray-300"
                aria-hidden="true"
            />
            <p :class="textSize.p">{{ section.type }} section</p>
        </div>
    </div>
</template>
