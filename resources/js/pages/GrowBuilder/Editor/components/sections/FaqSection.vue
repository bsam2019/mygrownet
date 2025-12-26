<script setup lang="ts">
/**
 * FAQ Section Preview Component
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
</script>

<template>
    <div
        :class="spacing.section"
        :style="{ backgroundColor: style.backgroundColor, color: style.textColor }"
    >
        <div class="max-w-3xl mx-auto">
            <h2 :class="[textSize.h2, 'font-bold text-center mb-8']">
                {{ content.title || 'Frequently Asked Questions' }}
            </h2>
            <div class="space-y-4">
                <div
                    v-for="(item, index) in content.items || []"
                    :key="index"
                    class="border border-gray-200 rounded-lg overflow-hidden"
                >
                    <div class="flex items-center justify-between p-4 bg-gray-50 cursor-pointer hover:bg-gray-100">
                        <span class="font-medium">{{ item.question }}</span>
                        <ChevronDownIcon class="w-5 h-5 text-gray-500" aria-hidden="true" />
                    </div>
                    <div class="p-4 border-t border-gray-200 bg-white">
                        <p :class="textSize.p" class="text-gray-600">{{ item.answer }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
