<script setup lang="ts">
/**
 * Gallery Section Preview Component
 * Layouts: grid (default), masonry, lightbox
 */
import { computed, ref } from 'vue';
import { PhotoIcon, XMarkIcon, ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';
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
const layout = computed(() => content.value?.layout || 'grid');
const columns = computed(() => content.value?.columns || 3);

// Lightbox state
const lightboxOpen = ref(false);
const lightboxIndex = ref(0);

const openLightbox = (index: number) => {
    lightboxIndex.value = index;
    lightboxOpen.value = true;
};

const closeLightbox = () => {
    lightboxOpen.value = false;
};

const nextImage = () => {
    const images = content.value?.images || [];
    if (images.length > 0) {
        lightboxIndex.value = (lightboxIndex.value + 1) % images.length;
    }
};

const prevImage = () => {
    const images = content.value?.images || [];
    if (images.length > 0) {
        lightboxIndex.value = (lightboxIndex.value - 1 + images.length) % images.length;
    }
};

// Text alignment class
const textAlignClass = computed(() => {
    const align = content.value?.textPosition || 'center';
    return {
        'text-left': align === 'left',
        'text-center': align === 'center',
        'text-right': align === 'right',
    };
});

// Grid columns class
const gridColsClass = computed(() => {
    if (props.isMobile) return 'grid-cols-2';
    switch (columns.value) {
        case 2: return 'grid-cols-2';
        case 4: return 'grid-cols-4';
        default: return 'grid-cols-3';
    }
});
</script>

<template>
    <div
        class="h-full flex flex-col justify-center overflow-hidden"
        :class="spacing.section"
        :style="{ color: style?.textColor || '#111827' }"
    >
        <div :style="{ transform: getSectionContentTransform(section) }">
            <h2 :class="[textSize.h2, 'font-bold mb-8', textAlignClass]">
                {{ content.title || 'Gallery' }}
            </h2>

            <!-- Masonry Layout -->
            <template v-if="layout === 'masonry'">
                <div class="columns-2 md:columns-3 gap-3 space-y-3">
                    <template v-if="content.images?.length">
                        <div
                            v-for="(img, idx) in content.images"
                            :key="img.id || idx"
                            class="break-inside-avoid rounded-lg overflow-hidden"
                        >
                            <img
                                :src="img.url"
                                :alt="img.alt || ''"
                                class="w-full object-cover hover:scale-105 transition-transform cursor-pointer"
                                @click="openLightbox(idx)"
                            />
                        </div>
                    </template>
                    <template v-else>
                        <div
                            v-for="n in 6"
                            :key="n"
                            class="break-inside-avoid bg-gray-200 rounded-lg flex items-center justify-center"
                            :class="n % 2 === 0 ? 'h-32' : 'h-48'"
                        >
                            <PhotoIcon class="w-8 h-8 text-gray-400" aria-hidden="true" />
                        </div>
                    </template>
                </div>
            </template>

            <!-- Lightbox Carousel Layout -->
            <template v-else-if="layout === 'lightbox'">
                <div
                    class="grid gap-3"
                    :class="gridColsClass"
                >
                    <template v-if="content.images?.length">
                        <div
                            v-for="(img, idx) in content.images"
                            :key="img.id || idx"
                            class="aspect-square rounded-lg overflow-hidden cursor-pointer group"
                            @click="openLightbox(idx)"
                        >
                            <img
                                :src="img.url"
                                :alt="img.alt || ''"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                            />
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center">
                                <span class="text-white opacity-0 group-hover:opacity-100 text-sm font-medium">View</span>
                            </div>
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

                <!-- Lightbox Modal (Preview Only) -->
                <div
                    v-if="lightboxOpen && content.images?.length"
                    class="fixed inset-0 z-50 bg-black/90 flex items-center justify-center"
                    @click="closeLightbox"
                >
                    <button
                        class="absolute top-4 right-4 text-white p-2 hover:bg-white/10 rounded-full"
                        @click.stop="closeLightbox"
                        aria-label="Close lightbox"
                    >
                        <XMarkIcon class="w-6 h-6" aria-hidden="true" />
                    </button>
                    <button
                        class="absolute left-4 top-1/2 -translate-y-1/2 text-white p-2 hover:bg-white/10 rounded-full"
                        @click.stop="prevImage"
                        aria-label="Previous image"
                    >
                        <ChevronLeftIcon class="w-8 h-8" aria-hidden="true" />
                    </button>
                    <img
                        :src="content.images[lightboxIndex]?.url"
                        :alt="content.images[lightboxIndex]?.alt || ''"
                        class="max-w-[90vw] max-h-[90vh] object-contain"
                        @click.stop
                    />
                    <button
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-white p-2 hover:bg-white/10 rounded-full"
                        @click.stop="nextImage"
                        aria-label="Next image"
                    >
                        <ChevronRightIcon class="w-8 h-8" aria-hidden="true" />
                    </button>
                </div>
            </template>

            <!-- Grid Layout (Default) -->
            <template v-else>
                <div
                    class="grid gap-3"
                    :class="gridColsClass"
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
            </template>
        </div>
    </div>
</template>
