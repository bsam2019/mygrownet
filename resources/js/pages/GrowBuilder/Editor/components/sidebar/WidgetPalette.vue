<script setup lang="ts">
import { ref, computed } from 'vue';
import draggable from 'vuedraggable';
import { ChevronDownIcon, ArrowsUpDownIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import { sectionBlocks, sectionCategories } from '../../config/sectionBlocks';
import type { SectionBlock, Section } from '../../types';
import { getDefaultContent } from '../../config/sectionDefaults';

const props = defineProps<{
    siteName: string;
    pages: Array<{ id: number; title: string; slug: string; isHomepage: boolean; showInNav: boolean }>;
    darkMode?: boolean;
}>();

const emit = defineEmits<{
    (e: 'dragStart'): void;
    (e: 'dragEnd'): void;
}>();

// Search
const searchQuery = ref('');

// Category expansion state
const widgetCategoryExpanded = ref<Record<string, boolean>>({
    'Layout': true,
    'Content': true,
    'Media': false,
    'Social Proof': false,
    'Commerce': false,
    'Forms': false,
});

const toggleWidgetCategory = (category: string) => {
    widgetCategoryExpanded.value[category] = !widgetCategoryExpanded.value[category];
};

// Filtered blocks based on search
const filteredBlocks = computed(() => {
    if (!searchQuery.value.trim()) return sectionBlocks;
    const query = searchQuery.value.toLowerCase();
    return sectionBlocks.filter(block => 
        block.name.toLowerCase().includes(query) ||
        block.description?.toLowerCase().includes(query) ||
        block.category.toLowerCase().includes(query)
    );
});

// Get blocks for a category
const getBlocksForCategory = (category: string) => {
    return filteredBlocks.value.filter(b => b.category === category);
};

// Check if category has matching blocks
const categoryHasBlocks = (category: string) => {
    return getBlocksForCategory(category).length > 0;
};

const cloneSection = (type: string): Section => {
    return {
        id: `section-${Date.now()}`,
        type: type as any,
        content: getDefaultContent(type as any, props.siteName, props.pages),
        style: { backgroundColor: '#ffffff', textColor: '#111827' },
    };
};

const onDragStart = () => emit('dragStart');
const onDragEnd = () => emit('dragEnd');
</script>

<template>
    <div :class="['flex-1 overflow-y-auto', darkMode ? 'custom-scrollbar-dark' : 'custom-scrollbar']">
        <!-- Header with search -->
        <div :class="['sticky top-0 z-10 border-b', darkMode ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-100']">
            <div :class="['px-4 py-3', darkMode ? 'bg-blue-900/30' : 'bg-gradient-to-r from-blue-50 to-indigo-50']">
                <p :class="['text-xs font-medium flex items-center gap-1.5', darkMode ? 'text-blue-300' : 'text-blue-700']">
                    <ArrowsUpDownIcon class="w-3.5 h-3.5" aria-hidden="true" />
                    Drag widgets to the canvas
                </p>
            </div>
            
            <!-- Search Input -->
            <div class="px-3 py-2">
                <div class="relative">
                    <MagnifyingGlassIcon :class="['absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4', darkMode ? 'text-gray-500' : 'text-gray-400']" aria-hidden="true" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search widgets..."
                        :class="[
                            'w-full pl-9 pr-3 py-2 text-sm rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all',
                            darkMode 
                                ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' 
                                : 'bg-gray-50 border border-gray-200 text-gray-900 placeholder-gray-500'
                        ]"
                    />
                </div>
            </div>
        </div>

        <!-- Widget Categories -->
        <div class="p-3 space-y-1">
            <template v-for="category in sectionCategories" :key="category">
                <div v-if="categoryHasBlocks(category)" class="mb-3">
                    <!-- Category Header -->
                    <button
                        @click="toggleWidgetCategory(category)"
                        :class="[
                            'w-full flex items-center justify-between px-2 py-2 text-xs font-semibold uppercase tracking-wider rounded-lg transition-colors',
                            darkMode 
                                ? 'text-gray-400 hover:bg-gray-700' 
                                : 'text-gray-600 hover:bg-gray-50'
                        ]"
                    >
                        <span class="flex items-center gap-2">
                            <span :class="['w-1.5 h-1.5 rounded-full', darkMode ? 'bg-blue-400' : 'bg-blue-500']"></span>
                            {{ category }}
                            <span :class="['font-normal normal-case', darkMode ? 'text-gray-500' : 'text-gray-400']">
                                ({{ getBlocksForCategory(category).length }})
                            </span>
                        </span>
                        <ChevronDownIcon 
                            :class="['w-4 h-4 transition-transform duration-200', darkMode ? 'text-gray-500' : 'text-gray-400', widgetCategoryExpanded[category] ? '' : '-rotate-90']" 
                            aria-hidden="true" 
                        />
                    </button>

                    <!-- Category Widgets -->
                    <div 
                        v-show="widgetCategoryExpanded[category]" 
                        class="mt-2 overflow-hidden"
                    >
                        <draggable
                            :list="getBlocksForCategory(category)"
                            :group="{ name: 'sections', pull: 'clone', put: false }"
                            :clone="(item: SectionBlock) => cloneSection(item.type)"
                            item-key="type"
                            :sort="false"
                            ghost-class="opacity-0"
                            @start="onDragStart"
                            @end="onDragEnd"
                            class="grid grid-cols-2 gap-2"
                        >
                            <template #item="{ element }">
                                <div 
                                    :class="[
                                        'group relative flex flex-col items-center p-3 rounded-xl cursor-grab transition-all duration-200 active:cursor-grabbing active:scale-95',
                                        darkMode 
                                            ? 'bg-gray-700 hover:bg-gradient-to-br hover:from-blue-900/50 hover:to-indigo-900/50 hover:ring-2 hover:ring-blue-500/50' 
                                            : 'bg-gray-50 hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 hover:ring-2 hover:ring-blue-200'
                                    ]"
                                >
                                    <!-- Widget Icon -->
                                    <div :class="[
                                        'w-10 h-10 rounded-xl flex items-center justify-center mb-2 shadow-sm group-hover:shadow-md transition-all duration-200',
                                        darkMode 
                                            ? 'bg-gray-600 group-hover:bg-gradient-to-br group-hover:from-blue-800 group-hover:to-indigo-800' 
                                            : 'bg-white group-hover:bg-gradient-to-br group-hover:from-blue-100 group-hover:to-indigo-100'
                                    ]">
                                        <component 
                                            :is="element.icon" 
                                            :class="[
                                                'w-5 h-5 transition-colors',
                                                darkMode 
                                                    ? 'text-gray-300 group-hover:text-blue-300' 
                                                    : 'text-gray-500 group-hover:text-blue-600'
                                            ]" 
                                            aria-hidden="true" 
                                        />
                                    </div>
                                    
                                    <!-- Widget Name -->
                                    <span :class="[
                                        'text-xs font-medium text-center leading-tight',
                                        darkMode 
                                            ? 'text-gray-300 group-hover:text-blue-300' 
                                            : 'text-gray-700 group-hover:text-blue-700'
                                    ]">
                                        {{ element.name }}
                                    </span>
                                    
                                    <!-- Tooltip on hover -->
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-20">
                                        {{ element.description || element.name }}
                                        <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                            </template>
                        </draggable>
                    </div>
                </div>
            </template>

            <!-- No results -->
            <div v-if="filteredBlocks.length === 0" class="text-center py-8">
                <p :class="['text-sm', darkMode ? 'text-gray-400' : 'text-gray-500']">
                    No widgets found for "{{ searchQuery }}"
                </p>
            </div>
        </div>
    </div>
</template>
