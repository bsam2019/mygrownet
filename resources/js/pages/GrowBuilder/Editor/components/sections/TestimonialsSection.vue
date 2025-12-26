<script setup lang="ts">
/**
 * Testimonials Section Preview Component
 */
import { computed } from 'vue';
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

const bgStyle = computed(() => getBackgroundStyle(style.value, '#f8fafc', '#111827'));
</script>

<template>
    <div
        class="h-full flex flex-col justify-center py-16 px-8 overflow-hidden"
        :style="bgStyle"
    >
        <div :style="{ transform: getSectionContentTransform(section) }">
            <h2 :class="[textSize.h2, 'font-bold text-center mb-8']">
                {{ content.title || 'Testimonials' }}
            </h2>
            <div class="grid" :class="[gridCols2, spacing.gap]">
                <div
                    v-for="(item, idx) in content.items || []"
                    :key="idx"
                    class="p-4 bg-white rounded-xl shadow-sm border border-gray-100"
                >
                    <p class="text-gray-600 italic mb-3" :class="textSize.p">
                        "{{ item.text }}"
                    </p>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                            {{ item.name?.charAt(0) || 'A' }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900" :class="textSize.p">{{ item.name }}</p>
                            <p class="text-gray-500 text-xs">{{ item.role }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
