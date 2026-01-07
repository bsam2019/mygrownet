<script setup lang="ts">
/**
 * Hero Section Preview Component
 * Supports multiple layouts: centered, split-right, split-left, video, slideshow
 */
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { ArrowsUpDownIcon, PencilIcon, ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';
import type { Section } from '../../types';

const props = defineProps<{
    section: Section;
    isMobile: boolean;
    textSize: { h1: string; h2: string; h3: string; p: string };
    spacing: { section: string; gap: string };
    // Inline editing
    isEditing: (sectionId: string, field: string, itemIndex?: number) => boolean;
    editingValue: string;
    startInlineEdit: (sectionId: string, field: string, value: string, itemIndex?: number) => void;
    saveInlineEdit: () => void;
    handleInlineKeydown: (e: KeyboardEvent) => void;
    // Element drag
    draggingElement: { sectionId: string; elementKey: string } | null;
    getElementTransform: (section: Section, elementKey: string) => string;
    hasElementOffset: (section: Section, elementKey: string) => boolean;
    startElementDrag: (e: MouseEvent, sectionId: string, elementKey: string) => void;
    resetAllElementOffsets: (sectionId: string) => void;
    selectedSectionId: string | null;
}>();

const emit = defineEmits<{
    'update:editingValue': [value: string];
}>();

const content = computed(() => props.section.content);
const style = computed(() => props.section.style);
const layout = computed(() => content.value.layout || 'centered');

const isSelected = computed(() => props.selectedSectionId === props.section.id);

// Slideshow state
const currentSlide = ref(0);
let slideInterval: ReturnType<typeof setInterval> | null = null;

const minHeight = computed(() => {
    if (style.value?.minHeight) return `${style.value.minHeight}px`;
    return props.isMobile ? '350px' : '500px';
});

// Text color from style (defaults to white for hero sections)
const textColor = computed(() => style.value?.textColor || '#ffffff');
const textColorStyle = computed(() => ({ color: textColor.value }));
const subtitleColorStyle = computed(() => ({ color: textColor.value, opacity: 0.9 }));

// Compute background style (solid or gradient)
const backgroundStyle = computed(() => {
    const s = style.value;
    
    if (s?.backgroundType === 'gradient' && s?.gradientFrom && s?.gradientTo) {
        const degMap: Record<string, string> = {
            'to-r': '90deg',
            'to-b': '180deg',
            'to-br': '135deg',
            'to-tr': '45deg',
        };
        const direction = degMap[s.gradientDirection || 'to-r'] || '90deg';
        return {
            background: `linear-gradient(${direction}, ${s.gradientFrom}, ${s.gradientTo})`,
            minHeight: minHeight.value,
        };
    }
    
    return {
        backgroundColor: s?.backgroundColor || '#1e40af',
        minHeight: minHeight.value,
    };
});

const hasAnyOffset = computed(() => 
    props.hasElementOffset(props.section, 'title') ||
    props.hasElementOffset(props.section, 'subtitle') ||
    props.hasElementOffset(props.section, 'buttons')
);

// Check if using video background
const hasVideoBackground = computed(() =>
    content.value.backgroundType === 'video' && content.value.videoBackground
);

// Check if using image background
const hasImageBackground = computed(() =>
    (!content.value.backgroundType || content.value.backgroundType === 'image') && content.value.backgroundImage
);

// Compute overlay style
const overlayStyle = computed(() => {
    const color = content.value.overlayColor || 'black';
    const opacity = (content.value.overlayOpacity ?? 50) / 100;
    
    if (color === 'gradient') {
        const from = content.value.overlayGradientFrom || '#2563eb';
        const to = content.value.overlayGradientTo || '#7c3aed';
        const midpoint = content.value.overlayGradientMidpoint || 50;
        const opacityHex = Math.round(opacity * 255).toString(16).padStart(2, '0');
        return {
            background: `linear-gradient(135deg, ${from}${opacityHex} 0%, ${from}${opacityHex} ${Math.max(0, midpoint - 20)}%, ${to}${opacityHex} ${Math.min(100, midpoint + 20)}%, ${to}${opacityHex} 100%)`,
        };
    } else if (color === 'white') {
        return {
            backgroundColor: `rgba(255, 255, 255, ${opacity})`,
        };
    } else {
        return {
            backgroundColor: `rgba(0, 0, 0, ${opacity})`,
        };
    }
});

// Slideshow functions
const slides = computed(() => content.value.slides || []);
const nextSlide = () => {
    if (slides.value.length > 0) {
        currentSlide.value = (currentSlide.value + 1) % slides.value.length;
    }
};
const prevSlide = () => {
    if (slides.value.length > 0) {
        currentSlide.value = (currentSlide.value - 1 + slides.value.length) % slides.value.length;
    }
};
const goToSlide = (idx: number) => {
    currentSlide.value = idx;
};

onMounted(() => {
    if (layout.value === 'slideshow' && content.value.autoPlay && slides.value.length > 1) {
        const interval = content.value.slideInterval || 5000;
        slideInterval = setInterval(nextSlide, interval);
    }
});

onUnmounted(() => {
    if (slideInterval) clearInterval(slideInterval);
});
</script>

<template>
    <div
        class="relative overflow-hidden"
        :style="backgroundStyle"
    >
        <!-- Slideshow Layout -->
        <template v-if="layout === 'slideshow' && slides.length > 0">
            <div class="relative h-full" style="min-height: inherit;">
                <!-- Slides -->
                <div 
                    v-for="(slide, idx) in slides" 
                    :key="idx"
                    class="absolute inset-0 transition-opacity duration-500"
                    :class="idx === currentSlide ? 'opacity-100 z-10' : 'opacity-0 z-0'"
                >
                    <img v-if="slide.backgroundImage" :src="slide.backgroundImage" class="w-full h-full object-cover" :alt="slide.title" />
                    <div class="absolute inset-0 bg-black/40"></div>
                    <div class="absolute inset-0 flex flex-col justify-center items-center text-center px-4">
                        <h1 :class="[textSize.h1, 'font-bold mb-4 text-white']">{{ slide.title || 'Slide Title' }}</h1>
                        <p v-if="slide.subtitle" :class="[textSize.p, 'mb-6 text-white/90 max-w-2xl']">{{ slide.subtitle }}</p>
                        <button v-if="slide.buttonText" class="px-6 py-3 bg-white text-blue-600 font-semibold rounded-lg">
                            {{ slide.buttonText }}
                        </button>
                    </div>
                </div>
                <!-- Navigation Dots -->
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex gap-2">
                    <button 
                        v-for="(_, idx) in slides" 
                        :key="idx"
                        @click="goToSlide(idx)"
                        class="w-2.5 h-2.5 rounded-full transition-colors"
                        :class="idx === currentSlide ? 'bg-white' : 'bg-white/50 hover:bg-white/75'"
                    ></button>
                </div>
                <!-- Arrows -->
                <button @click="prevSlide" class="absolute left-2 top-1/2 -translate-y-1/2 z-20 w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center text-white">
                    <ChevronLeftIcon class="w-5 h-5" />
                </button>
                <button @click="nextSlide" class="absolute right-2 top-1/2 -translate-y-1/2 z-20 w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center text-white">
                    <ChevronRightIcon class="w-5 h-5" />
                </button>
            </div>
        </template>

        <!-- Split Layout (Image Left or Right) -->
        <template v-else-if="layout === 'split-left' || layout === 'split-right'">
            <div class="h-full flex items-center" :class="spacing.section">
                <div class="w-full max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                    <!-- Text Content -->
                    <div :class="layout === 'split-left' ? 'lg:order-2' : 'lg:order-1'">
                        <h1 :class="[textSize.h1, 'font-bold mb-4']" :style="textColorStyle">
                            {{ content.title || 'Hero Title' }}
                        </h1>
                        <p :class="[textSize.p, 'mb-6']" :style="subtitleColorStyle">
                            {{ content.subtitle || 'Subtitle text' }}
                        </p>
                        <div class="flex flex-wrap gap-3">
                            <button class="px-6 py-2 bg-white text-blue-600 font-semibold rounded-lg">
                                {{ content.buttonText || 'Get Started' }}
                            </button>
                            <button v-if="content.secondaryButtonText" class="px-6 py-2 border-2 font-semibold rounded-lg" :style="{ borderColor: textColor, color: textColor }">
                                {{ content.secondaryButtonText }}
                            </button>
                        </div>
                    </div>
                    <!-- Image -->
                    <div :class="layout === 'split-left' ? 'lg:order-1' : 'lg:order-2'">
                        <img v-if="content.image" :src="content.image" class="rounded-xl shadow-2xl w-full" :alt="content.title" />
                        <div v-else class="aspect-video bg-white/10 rounded-xl flex items-center justify-center">
                            <span class="text-white/50 text-sm">Add hero image</span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Default Centered Layout -->
        <template v-else>
            <!-- Background Image -->
            <div v-if="hasImageBackground" class="absolute inset-0">
                <img :src="content.backgroundImage" class="w-full h-full object-cover" />
                <div class="absolute inset-0" :style="overlayStyle"></div>
            </div>

            <!-- Video Background -->
            <div v-else-if="hasVideoBackground" class="absolute inset-0">
                <iframe
                    :src="content.videoBackground"
                    class="absolute w-full h-full object-cover"
                    style="transform: scale(1.5); pointer-events: none;"
                    frameborder="0"
                    allow="autoplay; fullscreen"
                    allowfullscreen
                ></iframe>
                <div class="absolute inset-0" :style="overlayStyle"></div>
            </div>

            <!-- Reset Button -->
            <div v-if="isSelected && hasAnyOffset" class="absolute top-2 left-2 z-20">
                <button
                    @click.stop="resetAllElementOffsets(section.id)"
                    class="px-2 py-1 text-xs font-medium rounded bg-white/90 backdrop-blur shadow-lg border border-gray-200 text-gray-600 hover:bg-gray-100 transition-colors"
                >
                    Reset Positions
                </button>
            </div>

            <!-- Content -->
            <div
                class="flex flex-col justify-center relative z-10"
                :class="[
                    spacing.section,
                    {
                        'text-left items-start': content.textPosition === 'left',
                        'text-center items-center': !content.textPosition || content.textPosition === 'center',
                        'text-right items-end': content.textPosition === 'right'
                    }
                ]"
                :style="{ minHeight: minHeight }"
            >
                <div
                    class="w-full max-w-3xl"
                    :class="{
                        'mx-auto': !content.textPosition || content.textPosition === 'center',
                        'ml-auto': content.textPosition === 'right'
                    }"
                >
                    <!-- Draggable Title -->
                    <div
                        class="relative group/drag"
                        :class="draggingElement?.sectionId === section.id && draggingElement?.elementKey === 'title' ? 'z-50' : ''"
                        :style="{ transform: getElementTransform(section, 'title') }"
                    >
                        <span
                            class="absolute -left-8 top-1/2 -translate-y-1/2 opacity-0 group-hover/drag:opacity-100 transition-opacity cursor-ns-resize bg-blue-500 text-white p-1 rounded shadow-lg z-10"
                            @click.stop
                            @mousedown.stop.prevent="startElementDrag($event, section.id, 'title')"
                        >
                            <ArrowsUpDownIcon class="w-3 h-3" />
                        </span>
                        <h1 v-if="isEditing(section.id, 'title')">
                            <input
                                :value="editingValue"
                                @input="emit('update:editingValue', ($event.target as HTMLInputElement).value)"
                                @blur="saveInlineEdit"
                                @keydown="handleInlineKeydown"
                                class="inline-edit-input font-bold bg-white/20 border border-white/40 rounded px-3 py-2 w-full"
                                :class="textSize.h1"
                                :style="textColorStyle"
                            />
                        </h1>
                        <h1
                            v-else
                            @click.stop="startInlineEdit(section.id, 'title', content.title)"
                            class="font-bold mb-4 cursor-pointer hover:bg-white/10 rounded px-2 py-1 transition-colors"
                            :class="[textSize.h1, hasElementOffset(section, 'title') ? 'ring-1 ring-blue-400/50' : '']"
                            :style="textColorStyle"
                        >
                            {{ content.title || 'Hero Title' }}
                            <PencilIcon class="w-4 h-4 inline opacity-0 hover:opacity-70 ml-2" aria-hidden="true" />
                        </h1>
                    </div>

                    <!-- Draggable Subtitle -->
                    <div
                        class="relative group/drag"
                        :class="draggingElement?.sectionId === section.id && draggingElement?.elementKey === 'subtitle' ? 'z-50' : ''"
                        :style="{ transform: getElementTransform(section, 'subtitle') }"
                    >
                        <span
                            class="absolute -left-8 top-1/2 -translate-y-1/2 opacity-0 group-hover/drag:opacity-100 transition-opacity cursor-ns-resize bg-blue-500 text-white p-1 rounded shadow-lg z-10"
                            @click.stop
                            @mousedown.stop.prevent="startElementDrag($event, section.id, 'subtitle')"
                        >
                            <ArrowsUpDownIcon class="w-3 h-3" />
                        </span>
                        <p v-if="isEditing(section.id, 'subtitle')">
                            <input
                                :value="editingValue"
                                @input="emit('update:editingValue', ($event.target as HTMLInputElement).value)"
                                @blur="saveInlineEdit"
                                @keydown="handleInlineKeydown"
                                class="inline-edit-input bg-white/20 border border-white/40 rounded px-3 py-2 w-full"
                                :class="textSize.p"
                                :style="subtitleColorStyle"
                            />
                        </p>
                        <p
                            v-else
                            @click.stop="startInlineEdit(section.id, 'subtitle', content.subtitle)"
                            class="mb-6 cursor-pointer hover:bg-white/10 rounded px-2 py-1 transition-colors"
                            :class="[textSize.p, hasElementOffset(section, 'subtitle') ? 'ring-1 ring-blue-400/50' : '']"
                            :style="subtitleColorStyle"
                        >
                            {{ content.subtitle || 'Subtitle text' }}
                            <PencilIcon class="w-3 h-3 inline opacity-0 hover:opacity-70 ml-2" aria-hidden="true" />
                        </p>
                    </div>

                    <!-- Draggable Buttons -->
                    <div
                        class="relative group/drag"
                        :class="draggingElement?.sectionId === section.id && draggingElement?.elementKey === 'buttons' ? 'z-50' : ''"
                        :style="{ transform: getElementTransform(section, 'buttons') }"
                    >
                        <span
                            class="absolute -left-8 top-1/2 -translate-y-1/2 opacity-0 group-hover/drag:opacity-100 transition-opacity cursor-ns-resize bg-blue-500 text-white p-1 rounded shadow-lg z-10"
                            @click.stop
                            @mousedown.stop.prevent="startElementDrag($event, section.id, 'buttons')"
                        >
                            <ArrowsUpDownIcon class="w-3 h-3" />
                        </span>
                        <div
                            :class="[
                                isMobile ? 'flex flex-col gap-2' : 'flex flex-wrap gap-3',
                                {
                                    'justify-start': content.textPosition === 'left',
                                    'justify-center': !content.textPosition || content.textPosition === 'center',
                                    'justify-end': content.textPosition === 'right'
                                }
                            ]"
                        >
                            <button
                                class="px-6 py-2 bg-white text-blue-600 font-semibold rounded-lg hover:bg-gray-100 transition-colors"
                                :class="[textSize.p, hasElementOffset(section, 'buttons') ? 'ring-1 ring-blue-400/50' : '']"
                            >
                                {{ content.buttonText || 'Get Started' }}
                            </button>
                            <button
                                v-if="content.secondaryButtonText"
                                class="px-6 py-2 border-2 font-semibold rounded-lg hover:bg-white/10 transition-colors"
                                :class="textSize.p"
                                :style="{ borderColor: textColor, color: textColor }"
                            >
                                {{ content.secondaryButtonText }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
