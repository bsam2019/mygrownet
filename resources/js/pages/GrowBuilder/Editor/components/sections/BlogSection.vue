<script setup lang="ts">
/**
 * Blog Section Preview Component
 * Shows preview of dynamic blog posts from dashboard
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
const postsCount = computed(() => Math.min(content.value.postsCount || 6, 6));
const layout = computed(() => content.value.layout || 'grid');
const columns = computed(() => content.value.columns || 3);
const cardStyle = computed(() => content.value.cardStyle || 'bordered');
const cardBg = computed(() => content.value.cardBackgroundColor || '#ffffff');
const cardText = computed(() => content.value.cardTextColor || '#111827');
const showImage = computed(() => content.value.showImage !== false);
const showDate = computed(() => content.value.showDate !== false);
const showExcerpt = computed(() => content.value.showExcerpt !== false);

const gridColsClass = computed(() => {
    if (props.isMobile) return 'grid-cols-1';
    if (columns.value === 2) return 'grid-cols-2';
    if (columns.value === 4) return 'grid-cols-4';
    return 'grid-cols-3';
});

const cardClasses = computed(() => {
    const classes = ['rounded-xl', 'overflow-hidden'];
    if (cardStyle.value === 'bordered') classes.push('border', 'border-gray-200');
    if (cardStyle.value === 'shadow') classes.push('shadow-lg');
    return classes.join(' ');
});
</script>

<template>
    <div
        class="h-full flex flex-col justify-center overflow-hidden"
        :class="spacing.section"
        :style="{ color: style?.textColor || '#111827' }"
    >
        <div :style="{ transform: getSectionContentTransform(section) }">
            <h2 :class="[textSize.h2, 'font-bold text-center mb-2']">
                {{ content.title || 'Latest News' }}
            </h2>
            <p v-if="content.description" class="text-center text-gray-500 mb-6 text-sm">
                {{ content.description }}
            </p>
            
            <!-- Featured Layout -->
            <template v-if="layout === 'featured'">
                <div class="space-y-4">
                    <!-- Featured post -->
                    <div :class="cardClasses" :style="{ backgroundColor: cardBg }">
                        <div class="flex">
                            <div v-if="showImage" class="w-1/2 aspect-video bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center">
                                <NewspaperIcon class="w-10 h-10 text-blue-300" aria-hidden="true" />
                            </div>
                            <div class="p-4 flex-1">
                                <p v-if="showDate" class="text-xs text-gray-400 mb-1">Dec 27, 2025</p>
                                <div class="h-5 bg-gray-200 rounded w-3/4 mb-2"></div>
                                <div v-if="showExcerpt" class="space-y-1">
                                    <div class="h-3 bg-gray-100 rounded w-full"></div>
                                    <div class="h-3 bg-gray-100 rounded w-4/5"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Smaller posts -->
                    <div class="grid grid-cols-3 gap-3">
                        <div v-for="idx in 3" :key="idx" :class="cardClasses" :style="{ backgroundColor: cardBg }">
                            <div v-if="showImage" class="aspect-video bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center">
                                <NewspaperIcon class="w-6 h-6 text-gray-300" aria-hidden="true" />
                            </div>
                            <div class="p-2">
                                <div class="h-3 bg-gray-200 rounded w-3/4 mb-1"></div>
                                <div class="h-2 bg-gray-100 rounded w-full"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            
            <!-- List Layout -->
            <template v-else-if="layout === 'list'">
                <div class="space-y-3">
                    <div v-for="idx in Math.min(postsCount, 4)" :key="idx" :class="cardClasses" :style="{ backgroundColor: cardBg }">
                        <div class="flex">
                            <div v-if="showImage" class="w-1/4 aspect-video bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center">
                                <NewspaperIcon class="w-6 h-6 text-blue-300" aria-hidden="true" />
                            </div>
                            <div class="p-3 flex-1">
                                <p v-if="showDate" class="text-xs text-gray-400 mb-1">Dec 27, 2025</p>
                                <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                                <div v-if="showExcerpt" class="h-3 bg-gray-100 rounded w-full"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            
            <!-- Grid Layout (default) -->
            <template v-else>
                <div class="grid gap-4" :class="gridColsClass">
                    <div v-for="idx in Math.min(postsCount, columns)" :key="idx" :class="cardClasses" :style="{ backgroundColor: cardBg }">
                        <div v-if="showImage" class="aspect-video bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center">
                            <NewspaperIcon class="w-8 h-8 text-blue-300" aria-hidden="true" />
                        </div>
                        <div class="p-3">
                            <p v-if="showDate" class="text-xs text-gray-400 mb-1">Dec 27, 2025</p>
                            <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                            <div v-if="showExcerpt" class="space-y-1">
                                <div class="h-3 bg-gray-100 rounded w-full"></div>
                                <div class="h-3 bg-gray-100 rounded w-2/3"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
            
            <!-- View All Button Preview -->
            <div v-if="content.showViewAll !== false" class="text-center mt-4">
                <span 
                    :class="[
                        'inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg',
                        content.buttonStyle === 'outline' ? 'border-2 border-blue-500 text-blue-600' : 
                        content.buttonStyle === 'text' ? 'text-blue-600' : 'bg-blue-500 text-white'
                    ]"
                >
                    {{ content.viewAllText || 'View All Posts' }} →
                </span>
            </div>
            
            <!-- Info badge -->
            <div class="text-center mt-3">
                <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-50 text-blue-600 text-xs rounded-full">
                    <NewspaperIcon class="w-3 h-3" aria-hidden="true" />
                    Dynamic posts from dashboard
                </span>
            </div>
        </div>
    </div>
</template>
