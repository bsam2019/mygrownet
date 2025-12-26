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
                :class="[textSize.h2, 'font-bold text-center mb-8']"
            >
                {{ content.title }}
            </h2>
            <div class="grid text-center" :class="[gridCols4, spacing.gap]">
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
