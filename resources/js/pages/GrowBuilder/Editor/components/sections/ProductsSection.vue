<script setup lang="ts">
/**
 * Products Section Preview Component
 */
import { computed } from 'vue';
import { ShoppingBagIcon } from '@heroicons/vue/24/outline';
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
                {{ content.title || 'Products' }}
            </h2>
            <div
                class="grid gap-4"
                :class="isMobile ? 'grid-cols-2' : 'grid-cols-2 md:grid-cols-3'"
            >
                <div
                    v-for="n in 6"
                    :key="n"
                    class="bg-white border border-gray-200 rounded-xl overflow-hidden"
                >
                    <div class="aspect-square bg-gray-100 flex items-center justify-center">
                        <ShoppingBagIcon class="w-8 h-8 text-gray-300" aria-hidden="true" />
                    </div>
                    <div class="p-3">
                        <h3 class="font-semibold" :class="textSize.p">Product {{ n }}</h3>
                        <p class="text-blue-600 font-bold">K99.00</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
