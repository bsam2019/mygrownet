<script setup lang="ts">
/**
 * Blog Section Preview Component
 */
import { computed } from 'vue';
import { NewspaperIcon } from '@heroicons/vue/24/outline';
import type { Section } from '../../types';

const props = defineProps<{
    section: Section;
    isMobile: boolean;
    textSize: { h1: string; h2: string; h3: string; p: string };
    spacing: { section: string; gap: string };
    gridCols3: string;
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
            <h2 :class="[textSize.h2, 'font-bold text-center mb-8']">
                {{ content.title || 'Latest News' }}
            </h2>
            <div class="grid" :class="[gridCols3, spacing.gap]">
                <div
                    v-for="(post, idx) in content.posts || []"
                    :key="idx"
                    class="bg-white border border-gray-200 rounded-xl overflow-hidden"
                >
                    <div class="aspect-video bg-gray-100 flex items-center justify-center">
                        <img
                            v-if="post.image"
                            :src="post.image"
                            class="w-full h-full object-cover"
                            :alt="post.title"
                        />
                        <NewspaperIcon v-else class="w-8 h-8 text-gray-300" aria-hidden="true" />
                    </div>
                    <div class="p-3">
                        <p class="text-xs text-gray-500 mb-1">{{ post.date }}</p>
                        <h3 class="font-semibold mb-1" :class="textSize.p">{{ post.title }}</h3>
                        <p class="text-gray-600 text-xs line-clamp-2">{{ post.excerpt }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
