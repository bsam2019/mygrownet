<script setup lang="ts">
/**
 * Testimonials Section Preview Component
 * Supports layouts: grid, carousel, single, photos
 */
import { computed, ref } from 'vue';
import { ChevronLeftIcon, ChevronRightIcon, StarIcon } from '@heroicons/vue/24/solid';
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
const layout = computed(() => content.value.layout || 'grid');

const bgStyle = computed(() => getBackgroundStyle(style.value, '#f8fafc', '#111827'));

// Carousel state
const currentIndex = ref(0);
const items = computed(() => content.value.items || []);

const nextItem = () => {
    if (items.value.length > 0) {
        currentIndex.value = (currentIndex.value + 1) % items.value.length;
    }
};
const prevItem = () => {
    if (items.value.length > 0) {
        currentIndex.value = (currentIndex.value - 1 + items.value.length) % items.value.length;
    }
};

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
        class="h-full flex flex-col justify-center py-16 px-8 overflow-hidden"
        :style="bgStyle"
    >
        <div :style="{ transform: getSectionContentTransform(section) }">
            <!-- Header -->
            <div :class="textAlignClass" class="mb-8">
                <h2 :class="[textSize.h2, 'font-bold mb-4']">
                    {{ content.title || 'Testimonials' }}
                </h2>
                <p v-if="content.subtitle" class="text-gray-600 max-w-2xl" :class="[textSize.p, { 'mx-auto': content.textPosition !== 'left' && content.textPosition !== 'right' }]">
                    {{ content.subtitle }}
                </p>
            </div>
            
            <!-- Carousel Layout -->
            <div v-if="layout === 'carousel'" class="relative max-w-3xl mx-auto">
                <div class="overflow-hidden">
                    <div v-for="(item, idx) in items" :key="idx" :class="idx === currentIndex ? 'block' : 'hidden'">
                        <div class="text-center">
                            <div v-if="item.rating" class="flex gap-1 justify-center mb-4">
                                <StarIcon v-for="n in item.rating" :key="n" class="w-5 h-5 text-yellow-400" />
                            </div>
                            <p :class="[textSize.h3, 'text-gray-700 mb-6 italic']">"{{ item.text }}"</p>
                            <div class="flex items-center justify-center gap-4">
                                <div v-if="item.image" class="w-14 h-14 rounded-full overflow-hidden">
                                    <img :src="item.image" class="w-full h-full object-cover" :alt="item.name" />
                                </div>
                                <div v-else class="w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-blue-600 font-bold text-xl">{{ item.name?.charAt(0) }}</span>
                                </div>
                                <div class="text-left">
                                    <p class="font-semibold text-lg">{{ item.name }}</p>
                                    <p class="text-gray-500">{{ item.role }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Navigation -->
                <div class="flex justify-center gap-2 mt-6">
                    <button v-for="(_, idx) in items" :key="idx" @click="currentIndex = idx" class="w-2.5 h-2.5 rounded-full transition-colors" :class="idx === currentIndex ? 'bg-blue-600' : 'bg-gray-300'"></button>
                </div>
                <button @click="prevItem" class="absolute left-0 top-1/2 -translate-y-1/2 w-10 h-10 bg-white shadow-md rounded-full flex items-center justify-center hover:bg-gray-50">
                    <ChevronLeftIcon class="w-5 h-5 text-gray-600" />
                </button>
                <button @click="nextItem" class="absolute right-0 top-1/2 -translate-y-1/2 w-10 h-10 bg-white shadow-md rounded-full flex items-center justify-center hover:bg-gray-50">
                    <ChevronRightIcon class="w-5 h-5 text-gray-600" />
                </button>
            </div>
            
            <!-- Single Large Quote Layout -->
            <div v-else-if="layout === 'single'" class="max-w-4xl mx-auto text-center">
                <div v-if="items[0]" class="relative">
                    <svg class="w-12 h-12 text-blue-200 mx-auto mb-6" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                    <p :class="[textSize.h2, 'text-gray-700 mb-8 italic leading-relaxed']">"{{ items[0].text }}"</p>
                    <div class="flex items-center justify-center gap-4">
                        <div v-if="items[0].image" class="w-16 h-16 rounded-full overflow-hidden">
                            <img :src="items[0].image" class="w-full h-full object-cover" :alt="items[0].name" />
                        </div>
                        <div v-else class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center">
                            <span class="text-blue-600 font-bold text-2xl">{{ items[0].name?.charAt(0) }}</span>
                        </div>
                        <div class="text-left">
                            <p class="font-semibold text-xl">{{ items[0].name }}</p>
                            <p class="text-gray-500">{{ items[0].role }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- With Photos Layout -->
            <div v-else-if="layout === 'photos'" class="grid gap-6" :class="gridCols2">
                <div v-for="(item, idx) in items" :key="idx" class="p-6 bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-start gap-4 mb-4">
                        <div v-if="item.image" class="w-16 h-16 rounded-full overflow-hidden flex-shrink-0">
                            <img :src="item.image" class="w-full h-full object-cover" :alt="item.name" />
                        </div>
                        <div v-else class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-blue-600 font-bold text-xl">{{ item.name?.charAt(0) }}</span>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ item.name }}</p>
                            <p class="text-gray-500 text-sm">{{ item.role }}</p>
                            <div v-if="item.rating" class="flex gap-0.5 mt-1">
                                <StarIcon v-for="n in item.rating" :key="n" class="w-4 h-4 text-yellow-400" />
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-600 italic" :class="textSize.p">"{{ item.text }}"</p>
                </div>
            </div>
            
            <!-- Default Grid Layout -->
            <div v-else class="grid" :class="[gridCols2, spacing.gap]">
                <div
                    v-for="(item, idx) in items"
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
