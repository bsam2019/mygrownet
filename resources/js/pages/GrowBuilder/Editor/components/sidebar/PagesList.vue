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
        <!-- Compact Add Page Button -->
        <div class="p-2 border-b border-gray-100">
            <button
                @click="emit('createPage')"
                class="w-full flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors"
            >
                <PlusIcon class="w-3.5 h-3.5" aria-hidden="true" />
                New Page
            </button>
        </div>

        <!-- Compact Pages List -->
        <div class="p-2">
            <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5 px-1">Pages</p>
            <div class="space-y-0.5">
                <div
                    v-for="page in pages"
                    :key="page.id"
                    :class="['group flex items-center gap-1.5 px-2 py-1.5 rounded-md cursor-pointer transition-all', activePage?.id === page.id ? 'bg-blue-50 text-blue-700' : 'hover:bg-gray-50 text-gray-600']"
                    @click="emit('switchPage', page)"
                >
                    <DocumentIcon :class="['w-3.5 h-3.5 flex-shrink-0', activePage?.id === page.id ? 'text-blue-500' : 'text-gray-400']" aria-hidden="true" />
                    <span :class="['flex-1 text-xs truncate', activePage?.id === page.id && 'font-medium']">
                        {{ page.title }}
                    </span>
                    <span v-if="page.isHomepage" class="text-[9px] text-gray-400 bg-gray-100 px-1 py-0.5 rounded">H</span>
                    <div class="opacity-0 group-hover:opacity-100 flex items-center">
                        <button @click.stop="emit('editPage', page)" class="p-0.5 hover:bg-gray-200 rounded" aria-label="Edit">
                            <PencilIcon class="w-3 h-3 text-gray-400" aria-hidden="true" />
                        </button>
                        <button v-if="!page.isHomepage" @click.stop="emit('deletePage', page.id)" class="p-0.5 hover:bg-red-100 rounded" aria-label="Delete">
                            <TrashIcon class="w-3 h-3 text-red-400" aria-hidden="true" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compact Template Button -->
        <div class="px-2 pb-2">
            <button
                @click="emit('applyTemplate')"
                class="w-full flex items-center justify-center gap-1.5 px-2 py-1.5 text-[10px] text-gray-500 border border-gray-200 rounded-md hover:bg-gray-50 transition-colors"
            >
                <SparklesIcon class="w-3 h-3" aria-hidden="true" />
                Apply Template
            </button>
        </div>

        <!-- Compact Sections List -->
        <div class="p-2 border-t border-gray-100">
            <div class="flex items-center justify-between mb-1.5 px-1">
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Sections</p>
                <span class="text-[10px] text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded-full">{{ sections.length }}</span>
            </div>
            <draggable
                :model-value="sections"
                @update:model-value="emit('update:sections', $event)"
                item-key="id"
                handle=".drag-handle"
                ghost-class="opacity-50"
                @start="emit('dragStart')"
                @end="emit('dragEnd')"
                class="space-y-0.5"
            >
                <template #item="{ element }">
                    <div
                        :class="['group flex items-center gap-1.5 px-2 py-1.5 rounded-md cursor-pointer transition-all', selectedSectionId === element.id ? 'bg-blue-50 text-blue-700' : 'hover:bg-gray-50 text-gray-600']"
                        @click="emit('selectSection', element.id)"
                    >
                        <ArrowsUpDownIcon class="drag-handle w-3 h-3 text-gray-300 cursor-grab hover:text-gray-500 flex-shrink-0" aria-hidden="true" />
                        <component :is="getSectionIcon(element.type)" class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" aria-hidden="true" />
                        <span class="flex-1 text-xs truncate capitalize">{{ element.type }}</span>
                        <button @click.stop="emit('deleteSection', element.id)" class="opacity-0 group-hover:opacity-100 p-0.5 hover:bg-red-100 rounded" aria-label="Delete">
                            <TrashIcon class="w-3 h-3 text-red-400" aria-hidden="true" />
                        </button>
                    </div>
                </template>
            </draggable>
            <div v-if="sections.length === 0" class="text-center py-4 text-gray-400">
                <Squares2X2Icon class="w-6 h-6 mx-auto mb-1 opacity-50" aria-hidden="true" />
                <p class="text-[10px]">No sections</p>
            </div>
        </div>
    </div>
</template>
