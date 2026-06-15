<template>
    <aside :class="[
        'flex flex-col transition-all duration-300 flex-shrink-0 border-r z-30',
        'fixed md:relative inset-y-0 left-0',
        leftSidebarOpen ? 'w-full md:w-72' : 'w-0',
        darkMode ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-200',
        'top-14 md:top-0'
    ]">
        <div v-if="leftSidebarOpen" class="flex flex-col h-full overflow-hidden">
            <!-- Mobile: Close button -->
            <div class="md:hidden flex items-center justify-between p-3 border-b" :class="darkMode ? 'border-gray-700' : 'border-gray-200'">
                <h3 :class="['font-semibold', darkMode ? 'text-white' : 'text-gray-900']">Editor Tools</h3>
                <button
                    @click="$emit('toggle')"
                    class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                    aria-label="Close sidebar"
                >
                    <XMarkIcon class="w-5 h-5" :class="darkMode ? 'text-gray-400' : 'text-gray-600'" aria-hidden="true" />
                </button>
            </div>

            <!-- Sidebar Tabs -->
            <div :class="['flex border-b', darkMode ? 'border-gray-700' : 'border-gray-200']">
                <button
                    @click="activeLeftTab = 'widgets'"
                    data-tour="add-tab"
                    :class="[
                        'flex-1 flex items-center justify-center gap-1.5 py-2.5 text-xs font-medium transition-colors border-b-2',
                        activeLeftTab === 'widgets'
                            ? (darkMode ? 'text-blue-400 border-blue-400 bg-blue-900/20' : 'text-blue-600 border-blue-600 bg-blue-50/50')
                            : (darkMode ? 'text-gray-400 border-transparent hover:text-gray-200 hover:bg-gray-700' : 'text-gray-500 border-transparent hover:text-gray-700 hover:bg-gray-50')
                    ]"
                >
                    <Squares2X2Icon class="w-4 h-4" aria-hidden="true" />
                    <span>Add</span>
                </button>
                <button
                    @click="activeLeftTab = 'pages'"
                    data-tour="pages-tab"
                    :class="[
                        'flex-1 flex items-center justify-center gap-1.5 py-2.5 text-xs font-medium transition-colors border-b-2',
                        activeLeftTab === 'pages'
                            ? (darkMode ? 'text-blue-400 border-blue-400 bg-blue-900/20' : 'text-blue-600 border-blue-600 bg-blue-50/50')
                            : (darkMode ? 'text-gray-400 border-transparent hover:text-gray-200 hover:bg-gray-700' : 'text-gray-500 border-transparent hover:text-gray-700 hover:bg-gray-50')
                    ]"
                >
                    <DocumentIcon class="w-4 h-4" aria-hidden="true" />
                    <span>Pages</span>
                </button>
                <button
                    @click="activeLeftTab = 'inspector'"
                    data-tour="edit-tab"
                    :class="[
                        'flex-1 flex items-center justify-center gap-1.5 py-2.5 text-xs font-medium transition-colors border-b-2 relative',
                        activeLeftTab === 'inspector'
                            ? (darkMode ? 'text-blue-400 border-blue-400 bg-blue-900/20' : 'text-blue-600 border-blue-600 bg-blue-50/50')
                            : (darkMode ? 'text-gray-400 border-transparent hover:text-gray-200 hover:bg-gray-700' : 'text-gray-500 border-transparent hover:text-gray-700 hover:bg-gray-50')
                    ]"
                >
                    <Cog6ToothIcon class="w-4 h-4" aria-hidden="true" />
                    <span>Edit</span>
                    <span
                        v-if="selectedSectionId || showNavSettings || showFooterSettings"
                        class="absolute top-1 right-1 w-2 h-2 bg-blue-500 rounded-full"
                    ></span>
                </button>
            </div>

            <!-- Widget Palette Tab -->
            <WidgetPalette
                v-if="activeLeftTab === 'widgets'"
                :siteName="siteName"
                :pages="pages"
                :darkMode="darkMode"
                @dragStart="$emit('dragStart')"
                @dragEnd="$emit('dragEnd')"
            />

            <!-- Pages Tab -->
            <PagesList
                v-if="activeLeftTab === 'pages'"
                :pages="pages"
                :activePage="activePage"
                :sections="sections"
                :selectedSectionId="selectedSectionId"
                :darkMode="darkMode"
                @switchPage="$emit('switchPage', $event)"
                @createPage="$emit('createPage')"
                @editPage="$emit('editPage', $event)"
                @deletePage="$emit('deletePage', $event)"
                @applyTemplate="$emit('applyTemplate')"
                @selectSection="(id) => editorStore.selectSection(id)"
                @deleteSection="(id) => $emit('deleteSection', id)"
                @dragStart="$emit('dragStart')"
                @dragEnd="$emit('dragEnd')"
                @update:sections="$emit('update:sections', $event)"
            />

            <!-- Inspector Tab -->
            <div v-if="activeLeftTab === 'inspector'" class="flex flex-col h-full overflow-hidden">
                <!-- Inspector Header -->
                <div :class="['p-3 border-b', darkMode ? 'border-gray-700' : 'border-gray-200']">
                    <div v-if="showNavSettings" class="flex items-center gap-2">
                        <div :class="['w-8 h-8 rounded-lg flex items-center justify-center', darkMode ? 'bg-indigo-900/50' : 'bg-indigo-100']">
                            <Bars3BottomLeftIcon :class="['w-4 h-4', darkMode ? 'text-indigo-400' : 'text-indigo-600']" aria-hidden="true" />
                        </div>
                        <div>
                            <h3 :class="['text-sm font-semibold', darkMode ? 'text-white' : 'text-gray-900']">Navigation</h3>
                            <p :class="['text-xs', darkMode ? 'text-gray-400' : 'text-gray-500']">Site-wide settings</p>
                        </div>
                    </div>
                    <div v-else-if="showFooterSettings" class="flex items-center gap-2">
                        <div :class="['w-8 h-8 rounded-lg flex items-center justify-center', darkMode ? 'bg-gray-700' : 'bg-gray-100']">
                            <Bars3BottomLeftIcon :class="['w-4 h-4', darkMode ? 'text-gray-300' : 'text-gray-600']" aria-hidden="true" />
                        </div>
                        <div>
                            <h3 :class="['text-sm font-semibold', darkMode ? 'text-white' : 'text-gray-900']">Footer</h3>
                            <p :class="['text-xs', darkMode ? 'text-gray-400' : 'text-gray-500']">Site-wide settings</p>
                        </div>
                    </div>
                    <div v-else-if="selectedSection" class="flex items-center gap-2">
                        <div :class="['w-8 h-8 rounded-lg flex items-center justify-center', darkMode ? 'bg-blue-900/50' : 'bg-blue-100']">
                            <component :is="selectedSectionType?.icon || Squares2X2Icon" :class="['w-4 h-4', darkMode ? 'text-blue-400' : 'text-blue-600']" aria-hidden="true" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 :class="['text-sm font-semibold capitalize truncate', darkMode ? 'text-white' : 'text-gray-900']">{{ selectedSection.type }}</h3>
                            <p :class="['text-xs', darkMode ? 'text-gray-400' : 'text-gray-500']">Edit section</p>
                        </div>
                        <button
                            @click="selectedSectionId = null"
                            :class="['p-1.5 rounded-lg transition-colors', darkMode ? 'hover:bg-gray-700 text-gray-400' : 'hover:bg-gray-100 text-gray-500']"
                            aria-label="Deselect section"
                        >
                            <XMarkIcon class="w-4 h-4" aria-hidden="true" />
                        </button>
                    </div>
                    <div v-else class="text-center py-4">
                        <div :class="['w-12 h-12 mx-auto rounded-full flex items-center justify-center mb-2', darkMode ? 'bg-gray-700' : 'bg-gray-100']">
                            <Squares2X2Icon :class="['w-6 h-6', darkMode ? 'text-gray-500' : 'text-gray-400']" aria-hidden="true" />
                        </div>
                        <p :class="['text-sm font-medium', darkMode ? 'text-gray-300' : 'text-gray-600']">No selection</p>
                        <p :class="['text-xs mt-1', darkMode ? 'text-gray-500' : 'text-gray-400']">Click a section to edit</p>
                    </div>
                </div>

                <!-- Navigation Settings Panel -->
                <NavigationInspector
                    v-if="showNavSettings"
                    :navigation="siteNavigation"
                    :pages="pages"
                    :darkMode="darkMode"
                    class="flex-1 min-h-0 overflow-hidden"
                    @openMediaLibrary="(target, field) => $emit('openMediaLibrary', target, field)"
                />

                <!-- Footer Settings Panel -->
                <FooterInspector
                    v-else-if="showFooterSettings"
                    :footer="siteFooter"
                    :pages="pages"
                    :darkMode="darkMode"
                    class="flex-1 min-h-0 overflow-hidden"
                    @openMediaLibrary="(target, field) => $emit('openMediaLibrary', target, field)"
                />

                <!-- Section Inspector -->
                <SectionInspector
                    v-else-if="selectedSection"
                    :section="selectedSection"
                    :sectionType="selectedSectionType"
                    :activeTab="activeInspectorTab"
                    :darkMode="darkMode"
                    :subdomain="siteSubdomain"
                    class="flex-1 min-h-0 overflow-hidden"
                    @update:activeTab="activeInspectorTab = $event"
                    @updateContent="(key, val) => $emit('updateContent', key, val)"
                    @updateStyle="(key, val) => $emit('updateStyle', key, val)"
                    @openMediaLibrary="(sectionId, field) => $emit('openMediaLibrary', 'section', sectionId, field)"
                />
            </div>
        </div>
    </aside>
</template>

<script setup lang="ts">
import { storeToRefs } from 'pinia';
import { useEditorStore } from '../stores/editorStore';
import type { Page, SectionBlock } from '../types';

import {
    XMarkIcon,
    Squares2X2Icon,
    DocumentIcon,
    Cog6ToothIcon,
    Bars3BottomLeftIcon,
} from '@heroicons/vue/24/outline';

import WidgetPalette from './sidebar/WidgetPalette.vue';
import PagesList from './sidebar/PagesList.vue';
import { NavigationInspector, FooterInspector, SectionInspector } from './inspectors';

const props = defineProps<{
    siteName: string;
    siteSubdomain: string;
    pages: Page[];
    selectedSectionType: SectionBlock | null;
}>();

defineEmits<{
    toggle: [];
    dragStart: [];
    dragEnd: [];
    switchPage: [pageOrId: Page | number];
    createPage: [];
    editPage: [page: Page];
    deletePage: [pageId: number];
    applyTemplate: [];
    deleteSection: [id: string];
    'update:sections': [sections: any[]];
    openMediaLibrary: [target: string, ...args: any[]];
    updateContent: [key: string, value: any];
    updateStyle: [key: string, value: any];
}>();

const editorStore = useEditorStore();
const {
    leftSidebarOpen,
    darkMode,
    activeLeftTab,
    selectedSectionId,
    selectedSection,
    showNavSettings,
    showFooterSettings,
    activeInspectorTab,
    siteNavigation,
    siteFooter,
    activePage,
    sections,
} = storeToRefs(editorStore);
</script>
