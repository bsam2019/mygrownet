<script setup lang="ts">
/**
 * Pricing Section Preview Component
 */
import { computed } from 'vue';
import { getBackgroundStyle } from '../../composables/useBackgroundStyle';
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

const bgStyle = computed(() => getBackgroundStyle(style.value, '#ffffff', '#111827'));
</script>

<template>
    <div
        class="h-full flex flex-col justify-center overflow-hidden"
        :class="spacing.section"
        :style="bgStyle"
    >
        <div :style="{ transform: getSectionContentTransform(section) }">
            <h2 :class="[textSize.h2, 'font-bold text-center mb-8']">
                {{ content.title || 'Pricing' }}
            </h2>
            <div class="grid" :class="[gridCols3, spacing.gap]">
                <div
                    v-for="(plan, idx) in content.plans || []"
                    :key="idx"
                    class="p-4 bg-white border border-gray-200 rounded-xl text-center"
                >
                    <h3 class="font-semibold mb-2" :class="textSize.h3">{{ plan.name }}</h3>
                    <p
                        class="font-bold text-blue-600 mb-4"
                        :class="isMobile ? 'text-2xl' : 'text-3xl'"
                    >
                        {{ plan.price }}
                    </p>
                    <ul class="space-y-1 mb-4">
                        <li
                            v-for="(feature, fIdx) in plan.features || []"
                            :key="fIdx"
                            class="text-gray-600"
                            :class="textSize.p"
                        >
                            {{ feature }}
                        </li>
                    </ul>
                    <button
                        class="w-full py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                        :class="textSize.p"
                    >
                        Choose Plan
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
