<script setup lang="ts">
/**
 * FAQ Section Preview Component
 * Supports layouts: accordion, two-column, list
 */
import { computed } from 'vue';
import { ChevronDownIcon } from '@heroicons/vue/24/outline';
import type { Section } from '../../types';

const props = defineProps<{
    section: Section;
    isMobile: boolean;
    textSize: { h1: string; h2: string; h3: string; p: string };
    spacing: { section: string; gap: string };
}>();

const content = computed(() => props.section.content);
const style = computed(() => props.section.style);
const layout = computed(() => content.value.layout || 'accordion');

// Text alignment
const textAlignClass = computed(() => {
    const align = content.value?.textPosition || 'center';
    return {
        'text-left': align === 'left',
        'text-center': align === 'center',
        'text-right': align === 'right',
    };
});
</script>

<template>
    <div
        :class="spacing.section"
        :style="{ backgroundColor: style.backgroundColor, color: style.textColor }"
    >
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div :class="textAlignClass" class="mb-8">
                <h2 :class="[textSize.h2, 'font-bold']">
                    {{ content.title || 'Frequently Asked Questions' }}
                </h2>
            </div>
            
            <!-- Two Column Layout -->
            <div v-if="layout === 'two-column'" class="grid md:grid-cols-2 gap-6">
                <div v-for="(item, idx) in content.items || []" :key="idx" class="bg-white p-5 rounded-xl border border-gray-100">
                    <h3 class="font-semibold text-gray-900 mb-3">{{ item.question }}</h3>
                    <p class="text-gray-600" :class="textSize.p">{{ item.answer }}</p>
                </div>
            </div>
            
            <!-- Simple List Layout -->
            <div v-else-if="layout === 'list'" class="max-w-3xl mx-auto space-y-6">
                <div v-for="(item, idx) in content.items || []" :key="idx" class="border-b border-gray-200 pb-6 last:border-0">
                    <h3 class="font-semibold text-gray-900 mb-2 text-lg">{{ item.question }}</h3>
                    <p class="text-gray-600" :class="textSize.p">{{ item.answer }}</p>
                </div>
            </div>
            
            <!-- Default Accordion Layout -->
            <div v-else class="max-w-3xl mx-auto space-y-4">
                <details
                    v-for="(item, index) in content.items || []"
                    :key="index"
                    class="border border-gray-200 rounded-lg group"
                >
                    <summary class="p-4 cursor-pointer font-semibold hover:bg-gray-50 list-none flex justify-between items-center">
                        {{ item.question }}
                        <ChevronDownIcon class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform" aria-hidden="true" />
                    </summary>
                    <p class="px-4 pb-4 text-gray-600" :class="textSize.p">{{ item.answer }}</p>
                </details>
            </div>
        </div>
    </div>
</template>
