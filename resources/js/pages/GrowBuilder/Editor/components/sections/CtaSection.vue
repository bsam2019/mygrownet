<script setup lang="ts">
/**
 * CTA Section Preview Component
 * Layouts: banner (default), split, minimal
 */
import { computed } from 'vue';
import { ArrowsUpDownIcon, PhotoIcon } from '@heroicons/vue/24/outline';
import { getBackgroundStyle } from '../../composables/useBackgroundStyle';
import type { Section } from '../../types';

const props = defineProps<{
    section: Section;
    isMobile: boolean;
    textSize: { h1: string; h2: string; h3: string; p: string };
    spacing: { section: string; gap: string };
    // Element drag
    draggingElement: { sectionId: string; elementKey: string } | null;
    getElementTransform: (section: Section, elementKey: string) => string;
    hasElementOffset: (section: Section, elementKey: string) => boolean;
    startElementDrag: (e: MouseEvent, sectionId: string, elementKey: string) => void;
    resetAllElementOffsets: (sectionId: string) => void;
    selectedSectionId: string | null;
}>();

const content = computed(() => props.section.content);
const style = computed(() => props.section.style);
const layout = computed(() => content.value?.layout || 'banner');

const isSelected = computed(() => props.selectedSectionId === props.section.id);

const hasAnyOffset = computed(() =>
    props.hasElementOffset(props.section, 'title') ||
    props.hasElementOffset(props.section, 'description') ||
    props.hasElementOffset(props.section, 'button')
);

// Background style with gradient support
const backgroundStyle = computed(() => getBackgroundStyle(
    style.value,
    '#2563eb',
    '#ffffff',
    props.isMobile ? '200px' : '250px'
));

// Text alignment class
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
        class="h-full relative overflow-hidden"
        :style="backgroundStyle"
    >
        <!-- Reset Button -->
        <div
            v-if="isSelected && hasAnyOffset"
            class="absolute top-2 left-2 z-20"
        >
            <button
                @click.stop="resetAllElementOffsets(section.id)"
                class="px-2 py-1 text-xs font-medium rounded bg-white/90 backdrop-blur shadow-lg border border-gray-200 text-gray-600 hover:bg-gray-100 transition-colors"
                title="Reset element positions"
            >
                Reset Positions
            </button>
        </div>

        <!-- Split Layout -->
        <template v-if="layout === 'split'">
            <div class="h-full flex items-center" :class="spacing.section">
                <div class="max-w-6xl mx-auto w-full">
                    <div class="grid lg:grid-cols-2 gap-8 items-center">
                        <!-- Text Content -->
                        <div :class="textAlignClass">
                            <h2 :class="[textSize.h2, 'font-bold mb-3']">
                                {{ content.title || 'Ready to Get Started?' }}
                            </h2>
                            <p class="opacity-90 mb-6" :class="textSize.p">
                                {{ content.description || 'Contact us today' }}
                            </p>
                            <button
                                class="bg-white text-gray-900 font-semibold rounded-lg hover:bg-gray-100 transition-colors"
                                :class="isMobile ? 'px-6 py-2 text-sm' : 'px-8 py-3'"
                            >
                                {{ content.buttonText || 'Contact Us' }}
                            </button>
                        </div>
                        <!-- Image -->
                        <div>
                            <img 
                                v-if="content.image" 
                                :src="content.image" 
                                class="rounded-xl shadow-lg w-full" 
                                :alt="content.title" 
                            />
                            <div v-else class="aspect-video bg-white/10 rounded-xl flex items-center justify-center">
                                <PhotoIcon class="w-12 h-12 text-white/30" aria-hidden="true" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Minimal Layout -->
        <template v-else-if="layout === 'minimal'">
            <div class="h-full flex items-center justify-center" :class="spacing.section">
                <div class="text-center">
                    <h2 :class="[textSize.h3, 'font-semibold mb-4']">
                        {{ content.title || 'Ready to Get Started?' }}
                    </h2>
                    <button
                        class="bg-white text-gray-900 font-semibold rounded-lg hover:bg-gray-100 transition-colors"
                        :class="isMobile ? 'px-6 py-2 text-sm' : 'px-8 py-3'"
                    >
                        {{ content.buttonText || 'Contact Us' }}
                    </button>
                </div>
            </div>
        </template>

        <!-- Banner Layout (Default) -->
        <template v-else>
            <div
                class="h-full flex flex-col justify-center items-center text-center max-w-xl mx-auto"
                :class="spacing.section"
            >
                <!-- Draggable Title -->
                <div
                    class="relative group/drag w-full"
                    :class="draggingElement?.sectionId === section.id && draggingElement?.elementKey === 'title' ? 'z-50' : ''"
                    :style="{ transform: getElementTransform(section, 'title') }"
                >
                    <span
                        class="absolute -left-8 top-1/2 -translate-y-1/2 opacity-0 group-hover/drag:opacity-100 transition-opacity cursor-ns-resize bg-white/90 text-gray-700 p-1 rounded shadow-lg z-10"
                        @click.stop
                        @mousedown.stop.prevent="startElementDrag($event, section.id, 'title')"
                        title="Drag to reposition vertically"
                    >
                        <ArrowsUpDownIcon class="w-3 h-3" />
                    </span>
                    <h2
                        :class="[textSize.h2, 'font-bold mb-3', hasElementOffset(section, 'title') ? 'ring-1 ring-white/50' : '']"
                    >
                        {{ content.title || 'Ready to Get Started?' }}
                    </h2>
                </div>

                <!-- Draggable Description -->
                <div
                    class="relative group/drag w-full"
                    :class="draggingElement?.sectionId === section.id && draggingElement?.elementKey === 'description' ? 'z-50' : ''"
                    :style="{ transform: getElementTransform(section, 'description') }"
                >
                    <span
                        class="absolute -left-8 top-1/2 -translate-y-1/2 opacity-0 group-hover/drag:opacity-100 transition-opacity cursor-ns-resize bg-white/90 text-gray-700 p-1 rounded shadow-lg z-10"
                        @click.stop
                        @mousedown.stop.prevent="startElementDrag($event, section.id, 'description')"
                        title="Drag to reposition vertically"
                    >
                        <ArrowsUpDownIcon class="w-3 h-3" />
                    </span>
                    <p
                        class="opacity-90 mb-6"
                        :class="[textSize.p, hasElementOffset(section, 'description') ? 'ring-1 ring-white/50' : '']"
                    >
                        {{ content.description || 'Contact us today' }}
                    </p>
                </div>

                <!-- Draggable Button -->
                <div
                    class="relative group/drag"
                    :class="draggingElement?.sectionId === section.id && draggingElement?.elementKey === 'button' ? 'z-50' : ''"
                    :style="{ transform: getElementTransform(section, 'button') }"
                >
                    <span
                        class="absolute -left-8 top-1/2 -translate-y-1/2 opacity-0 group-hover/drag:opacity-100 transition-opacity cursor-ns-resize bg-white/90 text-gray-700 p-1 rounded shadow-lg z-10"
                        @click.stop
                        @mousedown.stop.prevent="startElementDrag($event, section.id, 'button')"
                        title="Drag to reposition vertically"
                    >
                        <ArrowsUpDownIcon class="w-3 h-3" />
                    </span>
                    <button
                        class="bg-white text-gray-900 font-semibold rounded-lg hover:bg-gray-100 transition-colors"
                        :class="[
                            isMobile ? 'px-6 py-2 text-sm' : 'px-8 py-3',
                            hasElementOffset(section, 'button') ? 'ring-1 ring-white/50' : ''
                        ]"
                    >
                        {{ content.buttonText || 'Contact Us' }}
                    </button>
                </div>
            </div>
        </template>
    </div>
</template>
