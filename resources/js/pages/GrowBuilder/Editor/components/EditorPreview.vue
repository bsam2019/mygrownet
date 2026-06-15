<script setup lang="ts">
import { ref } from 'vue';
import {
    DevicePhoneMobileIcon,
    DeviceTabletIcon,
    ComputerDesktopIcon,
    XMarkIcon,
    PencilSquareIcon,
} from '@heroicons/vue/24/outline';
import NavigationRenderer from './NavigationRenderer.vue';
import SectionRenderer from './SectionRenderer.vue';
import FooterRenderer from './FooterRenderer.vue';
import type { Section, NavigationSettings, FooterSettings, PreviewMode } from '../types';

const props = defineProps<{
    isFullPreview: boolean;
    isIframePreview: boolean;
    previewWidth: number;
    iframeKey: number;
    siteName: string;
    siteUrl: string;
    siteNavigation: NavigationSettings;
    siteFooter: FooterSettings;
    sections: Section[];
    isMobile: boolean;
    editingValue: string;
    draggingElement: { sectionId: string; elementKey: string } | null;
}>();
const gridCols2 = 'grid grid-cols-1 md:grid-cols-2 gap-6';
const gridCols3 = 'grid grid-cols-1 md:grid-cols-3 gap-6';
const gridCols4 = 'grid grid-cols-1 md:grid-cols-4 gap-6';

const emit = defineEmits<{
    close: [];
    edit: [];
    setPreviewBreakpoint: [width: number];
    setIframePreview: [value: boolean];
    refreshIframe: [];
    switchPage: [slug: string];
}>();

const breakpoints = [
    { name: 'Mobile', width: 375, icon: 'phone' },
    { name: 'Tablet', width: 768, icon: 'tablet' },
    { name: 'Laptop', width: 1024, icon: 'laptop' },
    { name: 'Desktop', width: 1440, icon: 'desktop' },
];

const textSize = { h1: 'text-3xl md:text-5xl font-bold', h2: 'text-2xl md:text-4xl font-bold', h3: 'text-xl md:text-2xl font-semibold', p: 'text-base md:text-lg' };
const spacing = { section: 'py-12 md:py-20', gap: 'gap-6' };

const previewWidthPx = ref(props.previewWidth);

const isMobilePreview = ref(false);

function setPreviewBreakpoint(width: number) {
    previewWidthPx.value = width;
    isMobilePreview.value = width <= 768;
    emit('setPreviewBreakpoint', width);
}
</script>

<template>
    <div v-if="isFullPreview" class="fixed inset-0 z-50 bg-gray-900 overflow-hidden">
        <div class="w-full h-full overflow-hidden preview-frame flex items-center justify-center">
            <div
                :style="{ width: previewWidthPx + 'px', maxWidth: '100%' }"
                class="h-full bg-white transition-all duration-300"
            >
                <iframe
                    v-if="isIframePreview"
                    :key="iframeKey"
                    :src="siteUrl"
                    class="w-full h-full border-0"
                    title="Site Preview"
                ></iframe>

                <div
                    v-else
                    class="w-full h-full overflow-y-auto bg-white"
                >
                    <NavigationRenderer
                        :navigation="siteNavigation"
                        :site-name="siteName"
                        :is-mobile="isMobilePreview"
                        :is-editing="false"
                        @switch-page="emit('switchPage', $event)"
                    />
                    <div v-for="section in sections" :key="section.id">
                        <SectionRenderer
                            :section="section"
                            :is-mobile="isMobilePreview"
                            :text-size="textSize"
                            :spacing="spacing"
                            :gridCols2="gridCols2"
                            :gridCols3="gridCols3"
                            :gridCols4="gridCols4"
                            :getSectionContentTransform="() => ''"
                            :get-element-transform="() => ''"
                            :has-element-offset="() => false"
                            :is-editing="() => false"
                            :editing-value="''"
                            :start-inline-edit="() => {}"
                            :save-inline-edit="() => {}"
                            :handle-inline-keydown="() => {}"
                            :start-element-drag="() => {}"
                            :reset-all-element-offsets="() => {}"
                            :selected-section-id="null"
                            :dragging-element="null"
                        />
                    </div>
                    <FooterRenderer
                        :footer="siteFooter"
                        :site-name="siteName"
                        :logo-text="siteNavigation.logoText"
                        :is-editing="false"
                    />
                </div>
            </div>
        </div>

        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 bg-white/95 backdrop-blur-sm rounded-full shadow-2xl px-3 py-2 flex items-center gap-1 z-50">
            <div class="px-3 py-1 border-r border-gray-200">
                <p class="text-sm font-medium text-gray-900">{{ siteName }}</p>
            </div>

            <div class="flex items-center border-r border-gray-200 px-2">
                <button
                    @click="emit('setIframePreview', true); emit('refreshIframe')"
                    :class="['px-3 py-1.5 text-sm rounded-full transition-colors', isIframePreview ? 'bg-blue-100 text-blue-700 font-medium' : 'text-gray-500 hover:text-gray-700']"
                    title="Interactive preview"
                >
                    Live
                </button>
                <button
                    @click="emit('setIframePreview', false)"
                    :class="['px-3 py-1.5 text-sm rounded-full transition-colors', !isIframePreview ? 'bg-blue-100 text-blue-700 font-medium' : 'text-gray-500 hover:text-gray-700']"
                    title="Static preview"
                >
                    Static
                </button>
            </div>

            <div class="flex items-center gap-1 border-r border-gray-200 px-2">
                <button
                    v-for="bp in breakpoints"
                    :key="bp.width"
                    @click="setPreviewBreakpoint(bp.width)"
                    :class="['p-2 rounded-full transition-colors', previewWidthPx === bp.width ? 'bg-blue-100 text-blue-600' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-100']"
                    :title="`${bp.name} (${bp.width}px)`"
                >
                    <DevicePhoneMobileIcon v-if="bp.icon === 'phone'" class="w-5 h-5" />
                    <DeviceTabletIcon v-else-if="bp.icon === 'tablet'" class="w-5 h-5" />
                    <ComputerDesktopIcon v-else class="w-5 h-5" />
                </button>
            </div>

            <a :href="siteUrl" target="_blank" class="p-2 text-gray-600 hover:bg-gray-100 rounded-full transition" title="Open in new tab">
                <ComputerDesktopIcon class="h-5 w-5" />
            </a>

            <button @click="emit('close')" class="px-4 py-2 text-gray-700 font-medium rounded-full hover:bg-gray-100 transition">
                Close
            </button>

            <button @click="emit('edit')" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-full hover:bg-blue-700 transition inline-flex items-center gap-1.5">
                <PencilSquareIcon class="h-4 w-4" />
                Edit
            </button>
        </div>

        <button @click="emit('close')" class="absolute top-4 right-4 p-2.5 bg-white/90 backdrop-blur-sm text-gray-700 rounded-full shadow-lg hover:bg-white transition z-50">
            <XMarkIcon class="h-5 w-5" />
        </button>

        <div class="absolute top-4 left-4 px-3 py-1.5 bg-black/50 backdrop-blur-sm text-white/80 text-xs rounded-full z-50">
            Press <kbd class="px-1.5 py-0.5 bg-white/20 rounded text-xs mx-1">Esc</kbd> to exit
        </div>
    </div>
</template>
