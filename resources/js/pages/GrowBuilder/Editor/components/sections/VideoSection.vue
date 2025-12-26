<script setup lang="ts">
/**
 * Video Section Preview Component
 */
import { computed } from 'vue';
import { PlayCircleIcon } from '@heroicons/vue/24/outline';
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
            <h2
                v-if="content.title"
                :class="[textSize.h2, 'font-bold text-center mb-3']"
            >
                {{ content.title }}
            </h2>
            <p
                v-if="content.description"
                class="text-center text-gray-600 mb-6"
                :class="textSize.p"
            >
                {{ content.description }}
            </p>
            <div
                class="aspect-video bg-gray-900 rounded-xl flex items-center justify-center max-w-3xl mx-auto w-full"
            >
                <iframe
                    v-if="content.videoUrl"
                    :src="content.videoUrl"
                    class="w-full h-full rounded-xl"
                    frameborder="0"
                    allowfullscreen
                ></iframe>
                <div v-else class="text-center">
                    <PlayCircleIcon
                        :class="isMobile ? 'w-12 h-12' : 'w-16 h-16'"
                        class="mx-auto text-gray-600 mb-3"
                        aria-hidden="true"
                    />
                    <p class="text-gray-400" :class="textSize.p">Add a video URL</p>
                </div>
            </div>
        </div>
    </div>
</template>
