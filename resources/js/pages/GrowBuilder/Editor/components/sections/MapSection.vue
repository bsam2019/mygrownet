<script setup lang="ts">
/**
 * Map Section Preview Component
 */
import { computed } from 'vue';
import { MapPinIcon } from '@heroicons/vue/24/outline';
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
                :class="[textSize.h2, 'font-bold text-center mb-6']"
            >
                {{ content.title }}
            </h2>
            <div class="aspect-video bg-gray-200 rounded-xl flex items-center justify-center">
                <iframe
                    v-if="content.embedUrl"
                    :src="content.embedUrl"
                    class="w-full h-full rounded-xl"
                    frameborder="0"
                    allowfullscreen
                ></iframe>
                <div v-else class="text-center">
                    <MapPinIcon
                        :class="isMobile ? 'w-10 h-10' : 'w-14 h-14'"
                        class="mx-auto text-gray-400 mb-3"
                        aria-hidden="true"
                    />
                    <p class="text-gray-500" :class="textSize.p">Add a Google Maps embed URL</p>
                </div>
            </div>
            <p
                v-if="content.showAddress && content.address"
                class="text-center mt-3 text-gray-600"
                :class="textSize.p"
            >
                <MapPinIcon class="w-4 h-4 inline mr-1" aria-hidden="true" />
                {{ content.address }}
            </p>
        </div>
    </div>
</template>
