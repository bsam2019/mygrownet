<script setup lang="ts">
import { ref, computed } from 'vue';
import draggable from 'vuedraggable';
import { ChevronDownIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
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

const searchQuery = ref('');
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

const filteredBlocks = computed(() => {
    if (!searchQuery.value.trim()) return sectionBlocks;
    const query = searchQuery.value.toLowerCase();
    return sectionBlocks.filter(block => 
        block.name.toLowerCase().includes(query) ||
        block.description?.toLowerCase().includes(query) ||
        block.category.toLowerCase().includes(query)
    );
});

const getBlocksForCategory = (category: string) => {
    return filteredBlocks.value.filter(b => b.category === category);
};

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
        <!-- Compact Search -->
        <div :class="['sticky top-0 z-10 px-2 py-2 border-b', darkMode ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-100']">
            <div class="relative">
                <MagnifyingGlassIcon :class="['absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5', darkMode ? 'text-gray-500' : 'text-gray-400']" aria-hidden="true" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search..."
                    :class="[
                        'w-full pl-8 pr-2 py-1.5 text-xs rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 transition-all',
                        darkMode 
                            ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-400' 
                            : 'bg-gray-50 border border-gray-200 text-gray-900 placeholder-gray-400'
                    ]"
                />
            </div>
        </div>

        <!-- Compact Widget Categories -->
        <div class="p-2 space-y-0.5">
            <template v-for="category in sectionCategories" :key="category">
                <div v-if="categoryHasBlocks(category)">
                    <!-- Compact Category Header -->
                    <button
                        @click="toggleWidgetCategory(category)"
                        :class="[
                            'w-full flex items-center justify-between px-2 py-1.5 text-[10px] font-semibold uppercase tracking-wider rounded transition-colors',
                            darkMode 
                                ? 'text-gray-400 hover:bg-gray-700/50' 
                                : 'text-gray-500 hover:bg-gray-50'
                        ]"
                    >
                        <span class="flex items-center gap-1.5">
                            {{ category }}
                            <span :class="['font-normal', darkMode ? 'text-gray-600' : 'text-gray-400']">
                                {{ getBlocksForCategory(category).length }}
                            </span>
                        </span>
                        <ChevronDownIcon 
                            :class="['w-3 h-3 transition-transform', darkMode ? 'text-gray-500' : 'text-gray-400', widgetCategoryExpanded[category] ? '' : '-rotate-90']" 
                            aria-hidden="true" 
                        />
                    </button>

                    <!-- Compact Widget Grid -->
                    <div v-show="widgetCategoryExpanded[category]" class="mt-1 mb-2">
                        <draggable
                            :list="getBlocksForCategory(category)"
                            :group="{ name: 'sections', pull: 'clone', put: false }"
                            :clone="(item: SectionBlock) => cloneSection(item.type)"
                            item-key="type"
                            :sort="false"
                            ghost-class="opacity-0"
                            @start="onDragStart"
                            @end="onDragEnd"
                            class="grid grid-cols-3 gap-1"
                        >
                            <template #item="{ element }">
                                <div 
                                    :class="[
                                        'group relative flex flex-col items-center p-2 rounded-lg cursor-grab transition-all active:cursor-grabbing active:scale-95',
                                        darkMode 
                                            ? 'hover:bg-gray-700/70' 
                                            : 'hover:bg-blue-50/70'
                                    ]"
                                    :title="element.description || element.name"
                                >
                                    <div :class="[
                                        'w-7 h-7 rounded-md flex items-center justify-center mb-1 transition-all',
                                        darkMode 
                                            ? 'bg-gray-700 group-hover:bg-blue-900/50' 
                                            : 'bg-gray-100 group-hover:bg-blue-100'
                                    ]">
                                        <component 
                                            :is="element.icon" 
                                            :class="[
                                                'w-3.5 h-3.5 transition-colors',
                                                darkMode 
                                                    ? 'text-gray-400 group-hover:text-blue-400' 
                                                    : 'text-gray-500 group-hover:text-blue-600'
                                            ]" 
                                            aria-hidden="true" 
                                        />
                                    </div>
                                    <span :class="[
                                        'text-[10px] font-medium text-center leading-tight truncate w-full',
                                        darkMode 
                                            ? 'text-gray-400 group-hover:text-blue-300' 
                                            : 'text-gray-600 group-hover:text-blue-700'
                                    ]">
                                        {{ element.name }}
                                    </span>
                                </div>
                            </template>
                        </draggable>
                    </div>
                </div>
            </template>

            <div v-if="filteredBlocks.length === 0" class="text-center py-4">
                <p :class="['text-xs', darkMode ? 'text-gray-500' : 'text-gray-400']">
                    No widgets found
                </p>
            </div>
        </div>
    </div>
</template>
