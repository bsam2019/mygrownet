<script setup lang="ts">
/**
 * Text Section Preview Component
 */
import { computed } from 'vue';
import type { Section } from '../../types';

const props = defineProps<{
    section: Section;
    isMobile: boolean;
    textSize: { h1: string; h2: string; h3: string; p: string };
    spacing: { section: string; gap: string };
    getSectionContentTransform: (section: Section) => string;
}>();

const content = computed(() => props.section.content);
const style = computed(() => props.section.style);
</script>

<template>
    <div
        class="h-full flex flex-col justify-center overflow-hidden"
        :class="spacing.section"
        :style="{ color: style?.textColor || '#111827' }"
    >
        <div :style="{ transform: getSectionContentTransform(section) }">
            <div
                class="prose max-w-none"
                :class="isMobile ? 'prose-sm' : ''"
                v-html="content.content || '<p>Enter your text here...</p>'"
            ></div>
        </div>
    </div>
</template>
