<script setup lang="ts">
/**
 * Gallery Section Preview Component
 */
import { computed } from 'vue';
import { PhotoIcon } from '@heroicons/vue/24/outline';
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
            <h2 :class="[textSize.h2, 'font-bold text-center mb-8']">
                {{ content.title || 'Gallery' }}
            </h2>
            <div
                class="grid gap-3"
                :class="isMobile ? 'grid-cols-2' : 'grid-cols-2 md:grid-cols-3'"
            >
                <template v-if="content.images?.length">
                    <div
                        v-for="(img, idx) in content.images"
                        :key="img.id || idx"
                        class="aspect-square rounded-lg overflow-hidden"
                    >
                        <img
                            :src="img.url"
                            :alt="img.alt || ''"
                            class="w-full h-full object-cover hover:scale-105 transition-transform"
                        />
                    </div>
                </template>
                <template v-else>
                    <div
                        v-for="n in 6"
                        :key="n"
                        class="aspect-square bg-gray-200 rounded-lg flex items-center justify-center"
                    >
                        <PhotoIcon class="w-8 h-8 text-gray-400" aria-hidden="true" />
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>
