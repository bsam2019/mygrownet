<script setup lang="ts">
/**
 * Features Section Preview Component
 */
import { computed } from 'vue';
import { CheckBadgeIcon } from '@heroicons/vue/24/outline';
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
            <h2 :class="[textSize.h2, 'font-bold mb-4', textAlignClass]">
                {{ content.title || 'Features' }}
            </h2>
            <p
                v-if="content.subtitle"
                class="text-gray-600 mb-8 max-w-2xl"
                :class="[textSize.p, textAlignClass, { 'mx-auto': style?.textAlign !== 'left' && style?.textAlign !== 'right' }]"
            >
                {{ content.subtitle }}
            </p>
            <div class="grid" :class="[gridCols2, spacing.gap]">
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
