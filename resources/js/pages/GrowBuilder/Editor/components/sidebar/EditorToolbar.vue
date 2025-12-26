<script setup lang="ts">
import { computed } from 'vue';
import {
    ChevronLeftIcon,
    ComputerDesktopIcon,
    DevicePhoneMobileIcon,
    DeviceTabletIcon,
    EyeIcon,
    ArrowUturnLeftIcon,
    ArrowUturnRightIcon,
    CloudArrowUpIcon,
    CheckCircleIcon,
    QuestionMarkCircleIcon,
    MagnifyingGlassMinusIcon,
    MagnifyingGlassPlusIcon,
    SunIcon,
    MoonIcon,
} from '@heroicons/vue/24/outline';
import type { PreviewMode } from '../../types';

const props = defineProps<{
    siteName: string;
    siteLogo?: string;
    pageTitle: string;
    previewMode: PreviewMode;
    saving: boolean;
    lastSaved?: Date | null;
    canUndo?: boolean;
    canRedo?: boolean;
    zoom?: number;
    darkMode?: boolean;
}>();

const emit = defineEmits<{
    (e: 'back'): void;
    (e: 'update:previewMode', mode: PreviewMode): void;
    (e: 'preview'): void;
    (e: 'save'): void;
    (e: 'undo'): void;
    (e: 'redo'): void;
    (e: 'showShortcuts'): void;
    (e: 'update:zoom', zoom: number): void;
    (e: 'update:darkMode', value: boolean): void;
}>();

// Format last saved time
const lastSavedText = computed(() => {
    if (!props.lastSaved) return null;
    const now = new Date();
    const diff = Math.floor((now.getTime() - props.lastSaved.getTime()) / 1000);
    if (diff < 60) return 'Just now';
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
    return props.lastSaved.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
});

// Zoom levels
const currentZoom = computed(() => props.zoom || 100);
</script>

<template>
    <header 
        :class="[
            'h-14 border-b flex items-center justify-between px-4 flex-shrink-0 shadow-sm transition-colors duration-200',
            darkMode 
                ? 'bg-gray-900 border-gray-700' 
                : 'bg-gradient-to-r from-white to-gray-50 border-gray-200'
        ]"
    >
        <!-- Left Section: Back + Site Info -->
        <div class="flex items-center gap-3">
            <button
                @click="emit('back')"
                :class="[
                    'p-2 rounded-lg transition-colors group',
                    darkMode ? 'hover:bg-gray-800' : 'hover:bg-gray-100'
                ]"
                aria-label="Back to dashboard"
            >
                <ChevronLeftIcon 
                    :class="[
                        'w-5 h-5 transition-colors',
                        darkMode ? 'text-gray-400 group-hover:text-gray-200' : 'text-gray-500 group-hover:text-gray-700'
                    ]" 
                    aria-hidden="true" 
                />
            </button>
            
            <div :class="['h-6 w-px', darkMode ? 'bg-gray-700' : 'bg-gray-200']"></div>
            
            <!-- Site Logo + Name -->
            <div class="flex items-center gap-3">
                <div 
                    :class="[
                        'w-9 h-9 rounded-lg flex items-center justify-center shadow-sm overflow-hidden',
                        !siteLogo && 'bg-gradient-to-br from-blue-500 to-indigo-600'
                    ]"
                >
                    <img 
                        v-if="siteLogo" 
                        :src="siteLogo" 
                        :alt="siteName" 
                        class="w-full h-full object-cover"
                    />
                    <span v-else class="text-white text-sm font-bold">
                        {{ siteName.charAt(0).toUpperCase() }}
                    </span>
                </div>
                <div>
                    <h1 :class="['text-sm font-semibold leading-tight', darkMode ? 'text-white' : 'text-gray-900']">
                        {{ siteName }}
                    </h1>
                    <p :class="['text-xs leading-tight', darkMode ? 'text-gray-400' : 'text-gray-500']">
                        {{ pageTitle || 'No page selected' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Center Section: Preview Mode + Zoom -->
        <div class="flex items-center gap-4">
            <!-- Undo/Redo -->
            <div class="flex items-center gap-1">
                <button
                    @click="emit('undo')"
                    :disabled="!canUndo"
                    :class="[
                        'p-2 rounded-lg transition-colors disabled:opacity-30 disabled:cursor-not-allowed',
                        darkMode ? 'hover:bg-gray-800' : 'hover:bg-gray-100'
                    ]"
                    aria-label="Undo"
                    title="Undo (Ctrl+Z)"
                >
                    <ArrowUturnLeftIcon :class="['w-4 h-4', darkMode ? 'text-gray-400' : 'text-gray-600']" aria-hidden="true" />
                </button>
                <button
                    @click="emit('redo')"
                    :disabled="!canRedo"
                    :class="[
                        'p-2 rounded-lg transition-colors disabled:opacity-30 disabled:cursor-not-allowed',
                        darkMode ? 'hover:bg-gray-800' : 'hover:bg-gray-100'
                    ]"
                    aria-label="Redo"
                    title="Redo (Ctrl+Shift+Z)"
                >
                    <ArrowUturnRightIcon :class="['w-4 h-4', darkMode ? 'text-gray-400' : 'text-gray-600']" aria-hidden="true" />
                </button>
            </div>

            <div :class="['h-6 w-px', darkMode ? 'bg-gray-700' : 'bg-gray-200']"></div>

            <!-- Preview Mode Toggle -->
            <div :class="['flex items-center rounded-lg p-1', darkMode ? 'bg-gray-800' : 'bg-gray-100']">
                <button
                    @click="emit('update:previewMode', 'desktop')"
                    :class="[
                        'p-1.5 rounded-md transition-all',
                        previewMode === 'desktop' 
                            ? (darkMode ? 'bg-gray-700 shadow-sm text-blue-400' : 'bg-white shadow-sm text-blue-600')
                            : (darkMode ? 'text-gray-400 hover:text-gray-200' : 'text-gray-500 hover:text-gray-700')
                    ]"
                    aria-label="Desktop preview"
                    title="Desktop view"
                >
                    <ComputerDesktopIcon class="w-4 h-4" aria-hidden="true" />
                </button>
                <button
                    @click="emit('update:previewMode', 'tablet')"
                    :class="[
                        'p-1.5 rounded-md transition-all',
                        previewMode === 'tablet' 
                            ? (darkMode ? 'bg-gray-700 shadow-sm text-blue-400' : 'bg-white shadow-sm text-blue-600')
                            : (darkMode ? 'text-gray-400 hover:text-gray-200' : 'text-gray-500 hover:text-gray-700')
                    ]"
                    aria-label="Tablet preview"
                    title="Tablet view"
                >
                    <DeviceTabletIcon class="w-4 h-4" aria-hidden="true" />
                </button>
                <button
                    @click="emit('update:previewMode', 'mobile')"
                    :class="[
                        'p-1.5 rounded-md transition-all',
                        previewMode === 'mobile' 
                            ? (darkMode ? 'bg-gray-700 shadow-sm text-blue-400' : 'bg-white shadow-sm text-blue-600')
                            : (darkMode ? 'text-gray-400 hover:text-gray-200' : 'text-gray-500 hover:text-gray-700')
                    ]"
                    aria-label="Mobile preview"
                    title="Mobile view"
                >
                    <DevicePhoneMobileIcon class="w-4 h-4" aria-hidden="true" />
                </button>
            </div>

            <!-- Zoom Controls -->
            <div :class="['flex items-center gap-1 rounded-lg p-1', darkMode ? 'bg-gray-800' : 'bg-gray-100']">
                <button
                    @click="emit('update:zoom', Math.max(50, currentZoom - 25))"
                    :disabled="currentZoom <= 50"
                    :class="[
                        'p-1.5 rounded-md transition-all disabled:opacity-30',
                        darkMode 
                            ? 'text-gray-400 hover:text-gray-200 hover:bg-gray-700' 
                            : 'text-gray-500 hover:text-gray-700 hover:bg-white'
                    ]"
                    aria-label="Zoom out"
                >
                    <MagnifyingGlassMinusIcon class="w-4 h-4" aria-hidden="true" />
                </button>
                <span :class="['px-2 text-xs font-medium min-w-[3rem] text-center', darkMode ? 'text-gray-300' : 'text-gray-600']">
                    {{ currentZoom }}%
                </span>
                <button
                    @click="emit('update:zoom', Math.min(125, currentZoom + 25))"
                    :disabled="currentZoom >= 125"
                    :class="[
                        'p-1.5 rounded-md transition-all disabled:opacity-30',
                        darkMode 
                            ? 'text-gray-400 hover:text-gray-200 hover:bg-gray-700' 
                            : 'text-gray-500 hover:text-gray-700 hover:bg-white'
                    ]"
                    aria-label="Zoom in"
                >
                    <MagnifyingGlassPlusIcon class="w-4 h-4" aria-hidden="true" />
                </button>
            </div>
        </div>

        <!-- Right Section: Actions -->
        <div class="flex items-center gap-2">
            <!-- Auto-save indicator -->
            <div v-if="lastSavedText" :class="['flex items-center gap-1.5 text-xs mr-2', darkMode ? 'text-gray-400' : 'text-gray-500']">
                <CheckCircleIcon class="w-4 h-4 text-green-500" aria-hidden="true" />
                <span>Saved {{ lastSavedText }}</span>
            </div>

            <!-- Dark Mode Toggle -->
            <button
                @click="emit('update:darkMode', !darkMode)"
                :class="[
                    'p-2 rounded-lg transition-colors',
                    darkMode 
                        ? 'text-yellow-400 hover:bg-gray-800' 
                        : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
                ]"
                :title="darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
                :aria-label="darkMode ? 'Switch to light mode' : 'Switch to dark mode'"
            >
                <SunIcon v-if="darkMode" class="w-5 h-5" aria-hidden="true" />
                <MoonIcon v-else class="w-5 h-5" aria-hidden="true" />
            </button>

            <!-- Keyboard Shortcuts -->
            <button
                @click="emit('showShortcuts')"
                :class="[
                    'p-2 rounded-lg transition-colors',
                    darkMode 
                        ? 'text-gray-400 hover:text-gray-200 hover:bg-gray-800' 
                        : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
                ]"
                title="Keyboard Shortcuts (Ctrl+/)"
                aria-label="Show keyboard shortcuts"
            >
                <QuestionMarkCircleIcon class="w-5 h-5" aria-hidden="true" />
            </button>

            <div :class="['h-6 w-px', darkMode ? 'bg-gray-700' : 'bg-gray-200']"></div>

            <button
                @click="emit('preview')"
                :class="[
                    'flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-lg transition-colors',
                    darkMode 
                        ? 'text-gray-300 hover:text-white hover:bg-gray-800' 
                        : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
                ]"
                title="Preview (Ctrl+P)"
            >
                <EyeIcon class="w-4 h-4" aria-hidden="true" />
                <span>Preview</span>
            </button>

            <button
                @click="emit('save')"
                :disabled="saving"
                class="flex items-center gap-1.5 px-4 py-1.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 disabled:opacity-50 transition-all shadow-sm hover:shadow"
                title="Save (Ctrl+S)"
            >
                <CloudArrowUpIcon v-if="!saving" class="w-4 h-4" aria-hidden="true" />
                <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ saving ? 'Saving...' : 'Save' }}</span>
            </button>
        </div>
    </header>
</template>
