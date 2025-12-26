<script setup lang="ts">
import draggable from 'vuedraggable';
import {
    PlusIcon,
    DocumentIcon,
    PencilIcon,
    TrashIcon,
    SparklesIcon,
    Squares2X2Icon,
    ArrowsUpDownIcon,
} from '@heroicons/vue/24/outline';
import type { Page, Section } from '../../types';
import { sectionBlocks } from '../../config/sectionBlocks';

const props = defineProps<{
    pages: Page[];
    activePage: Page | null;
    sections: Section[];
    selectedSectionId: string | null;
    darkMode?: boolean;
}>();

const emit = defineEmits<{
    (e: 'switchPage', page: Page): void;
    (e: 'createPage'): void;
    (e: 'editPage', page: Page): void;
    (e: 'deletePage', pageId: number): void;
    (e: 'applyTemplate'): void;
    (e: 'selectSection', sectionId: string): void;
    (e: 'deleteSection', sectionId: string): void;
    (e: 'dragStart'): void;
    (e: 'dragEnd'): void;
    (e: 'update:sections', sections: Section[]): void;
}>();

const getSectionIcon = (type: string) => {
    return sectionBlocks.find(b => b.type === type)?.icon || Squares2X2Icon;
};
</script>

<template>
    <div :class="['flex-1 overflow-y-auto', darkMode ? 'custom-scrollbar-dark' : 'custom-scrollbar']">
        <!-- Add Page Button -->
        <div class="p-3 border-b border-gray-100">
            <button
                @click="emit('createPage')"
                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
            >
                <PlusIcon class="w-4 h-4" aria-hidden="true" />
                Add New Page
            </button>
        </div>

        <!-- Pages List -->
        <div class="p-3 space-y-1">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Site Pages</p>
            <div
                v-for="page in pages"
                :key="page.id"
                :class="['group flex items-center gap-2 px-3 py-2.5 rounded-lg cursor-pointer transition-all', activePage?.id === page.id ? 'bg-blue-50 ring-1 ring-blue-200' : 'hover:bg-gray-50']"
                @click="emit('switchPage', page)"
            >
                <DocumentIcon :class="['w-4 h-4', activePage?.id === page.id ? 'text-blue-600' : 'text-gray-400']" aria-hidden="true" />
                <span :class="['flex-1 text-sm truncate', activePage?.id === page.id ? 'text-blue-700 font-medium' : 'text-gray-700']">
                    {{ page.title }}
                </span>
                <span v-if="page.isHomepage" class="text-xs text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">Home</span>
                <div class="opacity-0 group-hover:opacity-100 flex items-center gap-1">
                    <button @click.stop="emit('editPage', page)" class="p-1 hover:bg-gray-200 rounded" aria-label="Edit page">
                        <PencilIcon class="w-3.5 h-3.5 text-gray-500" aria-hidden="true" />
                    </button>
                    <button v-if="!page.isHomepage" @click.stop="emit('deletePage', page.id)" class="p-1 hover:bg-red-100 rounded" aria-label="Delete page">
                        <TrashIcon class="w-3.5 h-3.5 text-red-500" aria-hidden="true" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Apply Template Button -->
        <div class="px-3 pb-3">
            <button
                @click="emit('applyTemplate')"
                class="w-full flex items-center justify-center gap-2 px-3 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-colors"
            >
                <SparklesIcon class="w-4 h-4" aria-hidden="true" />
                <span>Apply Template to Page</span>
            </button>
        </div>

        <!-- Page Sections List -->
        <div class="p-3 border-t border-gray-100">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Page Sections</p>
                <span class="text-xs text-gray-400">{{ sections.length }}</span>
            </div>
            <draggable
                :model-value="sections"
                @update:model-value="emit('update:sections', $event)"
                item-key="id"
                handle=".drag-handle"
                ghost-class="opacity-50"
                @start="emit('dragStart')"
                @end="emit('dragEnd')"
                class="space-y-1"
            >
                <template #item="{ element }">
                    <div
                        :class="['group flex items-center gap-2 px-3 py-2 rounded-lg cursor-pointer transition-all', selectedSectionId === element.id ? 'bg-blue-50 ring-1 ring-blue-200' : 'hover:bg-gray-50']"
                        @click="emit('selectSection', element.id)"
                    >
                        <ArrowsUpDownIcon class="drag-handle w-4 h-4 text-gray-300 cursor-grab hover:text-gray-500" aria-hidden="true" />
                        <component :is="getSectionIcon(element.type)" class="w-4 h-4 text-gray-400" aria-hidden="true" />
                        <span class="flex-1 text-sm text-gray-700 truncate capitalize">{{ element.type }}</span>
                        <button @click.stop="emit('deleteSection', element.id)" class="opacity-0 group-hover:opacity-100 p-1 hover:bg-red-100 rounded transition-all" aria-label="Delete section">
                            <TrashIcon class="w-3.5 h-3.5 text-red-500" aria-hidden="true" />
                        </button>
                    </div>
                </template>
            </draggable>
            <div v-if="sections.length === 0" class="text-center py-6 text-gray-400">
                <Squares2X2Icon class="w-8 h-8 mx-auto mb-2" aria-hidden="true" />
                <p class="text-xs">No sections yet</p>
            </div>
        </div>
    </div>
</template>
