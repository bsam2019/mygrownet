<script setup lang="ts">
/**
 * Features Section Preview Component
 * Supports layouts: grid, checklist, steps
 */
import { computed } from 'vue';
import { CheckBadgeIcon, CheckIcon } from '@heroicons/vue/24/outline';
import { getBackgroundStyle } from '../../composables/useBackgroundStyle';
import type { Section } from '../../types';

const props = defineProps<{
    section: Section;
    isMobile: boolean;
    textSize: { h1: string; h2: string; h3: string; p: string };
    spacing: { section: string; gap: string };
    gridCols2: string;
    getSectionContentTransform: (section: Section) => string;
}>();

const content = computed(() => props.section.content);
const style = computed(() => props.section.style);
const layout = computed(() => content.value.layout || 'grid');

const bgStyle = computed(() => getBackgroundStyle(style.value, '#ffffff', '#111827'));

// Compute text alignment class
const textAlignClass = computed(() => {
    const align = content.value?.textPosition || style.value?.textAlign || 'center';
    return {
        'text-left': align === 'left',
        'text-center': align === 'center',
        'text-right': align === 'right',
    };
});
</script>

<template>
    <div
        class="h-full flex flex-col justify-center overflow-hidden"
        :class="spacing.section"
        :style="bgStyle"
    >
        <div :style="{ transform: getSectionContentTransform(section) }">
            <!-- Header -->
            <div :class="textAlignClass" class="mb-8">
                <h2 :class="[textSize.h2, 'font-bold mb-4']">
                    {{ content.title || 'Features' }}
                </h2>
                <p v-if="content.subtitle" class="text-gray-600 max-w-2xl" :class="[textSize.p, { 'mx-auto': content.textPosition !== 'left' && content.textPosition !== 'right' }]">
                    {{ content.subtitle }}
                </p>
            </div>
            
            <!-- Checklist Layout -->
            <div v-if="layout === 'checklist'" class="max-w-3xl mx-auto space-y-4">
                <div v-for="(item, idx) in content.items || []" :key="idx" class="flex gap-3 items-start p-4 bg-white rounded-lg border border-gray-100">
                    <CheckIcon class="w-6 h-6 text-green-500 flex-shrink-0 mt-0.5" aria-hidden="true" />
                    <div>
                        <h3 class="font-semibold text-gray-900" :class="textSize.p">{{ item.title }}</h3>
                        <p v-if="item.description" class="text-gray-600 text-sm mt-1">{{ item.description }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Numbered Steps Layout -->
            <div v-else-if="layout === 'steps'" class="max-w-4xl mx-auto">
                <div class="relative">
                    <!-- Connecting Line -->
                    <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-blue-200 hidden sm:block"></div>
                    <div class="space-y-8">
                        <div v-for="(item, idx) in content.items || []" :key="idx" class="relative flex gap-4 sm:gap-6">
                            <div class="flex-shrink-0 w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-lg z-10">
                                {{ idx + 1 }}
                            </div>
                            <div class="flex-1 pt-2">
                                <h3 :class="[textSize.h3, 'font-semibold mb-2 text-gray-900']">{{ item.title }}</h3>
                                <p class="text-gray-600" :class="textSize.p">{{ item.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Default Grid Layout -->
            <div v-else class="grid" :class="[gridCols2, spacing.gap]">
                <div
                    v-for="(item, idx) in content.items || []"
                    :key="idx"
                    class="flex gap-3 p-3"
                >
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <CheckBadgeIcon class="w-4 h-4 text-blue-600" aria-hidden="true" />
                    </div>
                    <div>
                        <h3 class="font-semibold mb-1" :class="textSize.p">{{ item.title }}</h3>
                        <p class="text-gray-600" :class="textSize.p">{{ item.description }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
