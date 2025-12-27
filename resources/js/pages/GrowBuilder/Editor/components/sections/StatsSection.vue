<script setup lang="ts">
/**
 * Stats Section Preview Component
 */
import { computed } from 'vue';
import { getBackgroundStyle } from '../../composables/useBackgroundStyle';
import type { Section } from '../../types';

const props = defineProps<{
    section: Section;
    isMobile: boolean;
    textSize: { h1: string; h2: string; h3: string; p: string };
    spacing: { section: string; gap: string };
    gridCols4: string;
    getSectionContentTransform: (section: Section) => string;
}>();

const content = computed(() => props.section.content);
const style = computed(() => props.section.style);

const bgStyle = computed(() => getBackgroundStyle(style.value, '#2563eb', '#ffffff'));

// Compute text alignment class
const textAlignClass = computed(() => {
    const align = content.value?.textPosition || style.value?.textAlign || 'center';
    return {
        'text-left': align === 'left',
        'text-center': align === 'center',
        'text-right': align === 'right',
    };
});

// Compute items alignment/justify class
const itemsAlignClass = computed(() => {
    const align = style.value?.itemsAlign || 'center';
    return {
        'justify-start': align === 'start',
        'justify-center': align === 'center',
        'justify-end': align === 'end',
        'justify-stretch': align === 'stretch',
    };
});

// Dynamic grid columns based on number of items
const dynamicGridCols = computed(() => {
    const itemCount = content.value?.items?.length || 0;
    
    if (props.isMobile) {
        // Mobile: max 2 columns
        return itemCount === 1 ? 'grid-cols-1' : 'grid-cols-2';
    }
    
    // Desktop: match columns to item count (max 6)
    switch (itemCount) {
        case 1: return 'grid-cols-1';
        case 2: return 'grid-cols-2';
        case 3: return 'grid-cols-3';
        case 4: return 'grid-cols-4';
        case 5: return 'grid-cols-5';
        case 6: return 'grid-cols-6';
        default: return itemCount > 6 ? 'grid-cols-4' : 'grid-cols-4'; // Fallback to 4 for many items
    }
});
</script>

<template>
    <div
        class="h-full flex flex-col justify-center overflow-hidden"
        :class="spacing.section"
        :style="bgStyle"
    >
        <div :style="{ transform: getSectionContentTransform(section) }">
            <h2
                v-if="content.title"
                :class="[textSize.h2, 'font-bold mb-8', textAlignClass]"
            >
                {{ content.title }}
            </h2>
            <div 
                class="grid" 
                :class="[
                    dynamicGridCols, 
                    spacing.gap, 
                    textAlignClass,
                    itemsAlignClass
                ]"
            >
                <div v-for="(stat, idx) in content.items || []" :key="idx">
                    <p
                        class="font-bold mb-1"
                        :class="isMobile ? 'text-2xl' : 'text-4xl'"
                    >
                        {{ stat.value }}
                    </p>
                    <p class="opacity-80" :class="textSize.p">{{ stat.label }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
